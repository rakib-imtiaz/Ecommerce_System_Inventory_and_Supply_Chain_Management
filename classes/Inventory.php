<?php
class Inventory {
    private $conn;
    private $table_name = "product";
    private $history_table = "inventory_history";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllInventory($search = '', $category = '', $stock_status = '', $sort = '', $limit = 10, $page = 1) {
        $query = "SELECT p.*, c.category_name, s.supplier_name,
                 (SELECT COUNT(*) FROM " . $this->history_table . " WHERE product_id = p.product_id) as movement_count
                 FROM " . $this->table_name . " p
                 LEFT JOIN category c ON p.category_id = c.category_id
                 LEFT JOIN supplier s ON p.supplier_id = s.supplier_id
                 WHERE 1=1";
        
        $params = [];

        if (!empty($search)) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($category)) {
            $query .= " AND p.category_id = ?";
            $params[] = $category;
        }

        if (!empty($stock_status)) {
            switch ($stock_status) {
                case 'low':
                    $query .= " AND p.stock_level <= p.reorder_level";
                    break;
                case 'out':
                    $query .= " AND p.stock_level = 0";
                    break;
                case 'in':
                    $query .= " AND p.stock_level > p.reorder_level";
                    break;
            }
        }

        switch ($sort) {
            case 'stock_asc':
                $query .= " ORDER BY p.stock_level ASC";
                break;
            case 'stock_desc':
                $query .= " ORDER BY p.stock_level DESC";
                break;
            case 'name_asc':
                $query .= " ORDER BY p.name ASC";
                break;
            default:
                $query .= " ORDER BY p.product_id DESC";
        }

        $offset = ($page - 1) * $limit;
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function updateStock($product_id, $quantity_change, $type, $notes = '') {
        try {
            $this->conn->beginTransaction();

            // Get current stock level
            $query = "SELECT stock_level FROM " . $this->table_name . " WHERE product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$product_id]);
            $current_stock = $stmt->fetch(PDO::FETCH_ASSOC)['stock_level'];

            // Calculate new stock level
            $new_stock = $current_stock + $quantity_change;
            if ($new_stock < 0) {
                throw new Exception("Stock level cannot be negative");
            }

            // Update stock level
            $query = "UPDATE " . $this->table_name . " SET stock_level = ? WHERE product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$new_stock, $product_id]);

            // Record history
            $query = "INSERT INTO " . $this->history_table . " 
                     (product_id, change_type, quantity_change, previous_stock, new_stock, notes, change_date) 
                     VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$product_id, $type, $quantity_change, $current_stock, $new_stock, $notes]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getStockHistory($product_id) {
        $query = "SELECT h.*, p.name as product_name 
                 FROM " . $this->history_table . " h
                 LEFT JOIN " . $this->table_name . " p ON h.product_id = p.product_id
                 WHERE h.product_id = ?
                 ORDER BY h.change_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt;
    }

    public function getLowStockProducts($limit = 10) {
        $query = "SELECT p.*, c.category_name
                 FROM " . $this->table_name . " p
                 LEFT JOIN category c ON p.category_id = c.category_id
                 WHERE p.stock_level <= p.reorder_level
                 ORDER BY (p.stock_level / p.reorder_level) ASC
                 LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt;
    }
} 