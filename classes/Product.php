<?php
class Product {
    private $conn;
    private $table_name = "product";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllProducts($search = '', $category = '', $sort = '', $limit = 10, $page = 1) {
        $query = "SELECT p.*, c.category_name, s.supplier_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN category c ON p.category_id = c.category_id
                 LEFT JOIN supplier s ON p.supplier_id = s.supplier_id
                 WHERE 1=1";
        
        $params = [];

        // Add search condition
        if (!empty($search)) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add category filter
        if (!empty($category)) {
            $query .= " AND p.category_id = ?";
            $params[] = $category;
        }

        // Add sorting
        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price DESC";
                break;
            case 'name_asc':
                $query .= " ORDER BY p.name ASC";
                break;
            case 'name_desc':
                $query .= " ORDER BY p.name DESC";
                break;
            case 'stock_asc':
                $query .= " ORDER BY p.stock_level ASC";
                break;
            case 'stock_desc':
                $query .= " ORDER BY p.stock_level DESC";
                break;
            default:
                $query .= " ORDER BY p.product_id DESC";
        }

        // Add pagination
        $offset = ($page - 1) * $limit;
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function getTotalProducts($search = '', $category = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($category)) {
            $query .= " AND p.category_id = ?";
            $params[] = $category;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getProduct($id) {
        $query = "SELECT p.*, c.category_name, s.supplier_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN category c ON p.category_id = c.category_id
                 LEFT JOIN supplier s ON p.supplier_id = s.supplier_id
                 WHERE p.product_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $description, $price, $stock_level, $category_id, $supplier_id) {
        $query = "INSERT INTO " . $this->table_name . "
                 (name, description, price, stock_level, category_id, supplier_id)
                 VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$name, $description, $price, $stock_level, $category_id, $supplier_id]);
    }

    public function update($id, $name, $description, $price, $stock_level, $category_id, $supplier_id) {
        $query = "UPDATE " . $this->table_name . "
                 SET name = ?, description = ?, price = ?, 
                     stock_level = ?, category_id = ?, supplier_id = ?
                 WHERE product_id = ?";

        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$name, $description, $price, $stock_level, $category_id, $supplier_id, $id]);
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE product_id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            // If there's a foreign key constraint or other database error
            throw new Exception("Cannot delete product: It may be referenced in orders or inventory.");
        }
    }
} 