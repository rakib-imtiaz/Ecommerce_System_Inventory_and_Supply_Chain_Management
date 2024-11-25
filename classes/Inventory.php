<?php
class Inventory {
    private $conn;
    private $table_name = "product";
    private $history_table = "inventory_history";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllInventory($search = '', $category = '', $stock_status = '', $sort = '', $limit = 10, $page = 1) {
        try {
            // Debug: Base query
            $query = "SELECT p.*, c.category_name, s.supplier_name
                     FROM product p
                     LEFT JOIN category c ON p.category_id = c.category_id
                     LEFT JOIN supplier s ON p.supplier_id = s.supplier_id
                     WHERE 1=1";
            
            $params = [];
            error_log("Debug: Building inventory query");

            if (!empty($search)) {
                $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                error_log("Debug: Added search condition: $searchTerm");
            }

            if (!empty($category)) {
                $query .= " AND p.category_id = ?";
                $params[] = $category;
                error_log("Debug: Added category condition: $category");
            }

            if (!empty($stock_status)) {
                switch ($stock_status) {
                    case 'low':
                        $query .= " AND p.stock_level <= COALESCE(p.reorder_level, 10)";
                        break;
                    case 'out':
                        $query .= " AND p.stock_level = 0";
                        break;
                    case 'in':
                        $query .= " AND p.stock_level > COALESCE(p.reorder_level, 10)";
                        break;
                }
                error_log("Debug: Added stock status condition: $stock_status");
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
            error_log("Debug: Added sort condition: $sort");

            $offset = ($page - 1) * $limit;
            $query .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;

            // Debug: Final query and parameters
            error_log("Debug: Final SQL Query: " . $query);
            error_log("Debug: Parameters: " . print_r($params, true));

            $stmt = $this->conn->prepare($query);
            
            if ($stmt === false) {
                error_log("Error: Failed to prepare statement");
                return false;
            }

            $stmt->execute($params);
            error_log("Debug: Query executed successfully. Row count: " . $stmt->rowCount());
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getAllInventory: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            throw $e;
        }
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
        try {
            $query = "SELECT p.*, c.category_name
                     FROM product p
                     LEFT JOIN category c ON p.category_id = c.category_id
                     WHERE p.stock_level <= COALESCE(p.reorder_level, 10)
                     ORDER BY (p.stock_level / NULLIF(p.reorder_level, 0)) ASC
                     LIMIT ?";

            error_log("Debug: Low stock query: " . $query);
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$limit]);
            
            error_log("Debug: Low stock products found: " . $stmt->rowCount());
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getLowStockProducts: " . $e->getMessage());
            throw $e;
        }
    }
} 