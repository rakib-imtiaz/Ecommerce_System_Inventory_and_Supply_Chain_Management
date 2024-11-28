<?php
class Inventory {
    private $conn;
    private $table_name = "product";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllInventory($search = '', $category = '', $stock_status = '', $sort = '', $limit = 10, $page = 1) {
        try {
            $query = "SELECT p.*, c.category_name, s.supplier_name 
                     FROM " . $this->table_name . " p 
                     LEFT JOIN category c ON p.category_id = c.category_id 
                     LEFT JOIN supplier s ON p.supplier_id = s.supplier_id 
                     WHERE 1=1";

            // Add search condition
            if (!empty($search)) {
                $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            }

            // Add category filter
            if (!empty($category)) {
                $query .= " AND p.category_id = :category";
            }

            // Add stock status filter
            if (!empty($stock_status)) {
                switch ($stock_status) {
                    case 'out':
                        $query .= " AND p.stock_level = 0";
                        break;
                    case 'low':
                        $query .= " AND p.stock_level > 0 AND p.stock_level <= 10";
                        break;
                    case 'in':
                        $query .= " AND p.stock_level > 10";
                        break;
                }
            }

            // Add sorting
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

            // Add pagination
            $offset = ($page - 1) * $limit;
            $query .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);

            // Bind parameters
            if (!empty($search)) {
                $searchParam = "%{$search}%";
                $stmt->bindParam(':search', $searchParam);
            }
            if (!empty($category)) {
                $stmt->bindParam(':category', $category);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getAllInventory: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLowStockProducts($threshold = 10) {
        try {
            $query = "SELECT p.*, c.category_name 
                     FROM " . $this->table_name . " p 
                     LEFT JOIN category c ON p.category_id = c.category_id 
                     WHERE p.stock_level <= :threshold 
                     ORDER BY p.stock_level ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error in getLowStockProducts: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateStock($product_id, $quantity, $type, $notes) {
        try {
            $this->conn->beginTransaction();

            // Get current stock level
            $query = "SELECT stock_level FROM product WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $current_stock = $stmt->fetch(PDO::FETCH_ASSOC)['stock_level'];

            // Calculate new stock level
            $new_stock = $current_stock + $quantity;

            // Prevent negative stock
            if ($new_stock < 0) {
                throw new Exception("Cannot reduce stock below 0");
            }

            // Update product stock
            $query = "UPDATE product 
                     SET stock_level = :new_stock 
                     WHERE product_id = :product_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':new_stock', $new_stock);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function addProduct($data) {
        try {
            $query = "INSERT INTO product (name, description, price, stock_level, category_id, supplier_id) 
                     VALUES (:name, :description, :price, :stock_level, :category_id, :supplier_id)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':stock_level', $data['stock_level']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':supplier_id', $data['supplier_id']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in addProduct: " . $e->getMessage());
            throw $e;
        }
    }
} 