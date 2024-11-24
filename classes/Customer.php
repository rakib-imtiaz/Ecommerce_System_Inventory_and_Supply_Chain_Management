<?php
class Customer {
    private $conn;
    private $table_name = "customer";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllCustomers($search = '', $sort = '', $limit = 10, $page = 1) {
        $query = "SELECT c.*, 
                 COUNT(DISTINCT o.order_id) as total_orders,
                 COALESCE(SUM(oi.quantity * oi.unit_price), 0) as total_spent
                 FROM " . $this->table_name . " c
                 LEFT JOIN ordertable o ON c.customer_id = o.customer_id
                 LEFT JOIN orderitem oi ON o.order_id = oi.order_id
                 WHERE 1=1";
        
        $params = [];

        if (!empty($search)) {
            $query .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " GROUP BY c.customer_id";

        switch ($sort) {
            case 'name_asc':
                $query .= " ORDER BY c.name ASC";
                break;
            case 'name_desc':
                $query .= " ORDER BY c.name DESC";
                break;
            case 'orders_desc':
                $query .= " ORDER BY total_orders DESC";
                break;
            case 'spent_desc':
                $query .= " ORDER BY total_spent DESC";
                break;
            default:
                $query .= " ORDER BY c.customer_id DESC";
        }

        $offset = ($page - 1) * $limit;
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function getCustomer($id) {
        $query = "SELECT c.*, 
                 COUNT(DISTINCT o.order_id) as total_orders,
                 COALESCE(SUM(oi.quantity * oi.unit_price), 0) as total_spent
                 FROM " . $this->table_name . " c
                 LEFT JOIN ordertable o ON c.customer_id = o.customer_id
                 LEFT JOIN orderitem oi ON o.order_id = oi.order_id
                 WHERE c.customer_id = ?
                 GROUP BY c.customer_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerOrders($id) {
        $query = "SELECT o.*, 
                 COUNT(oi.order_item_id) as item_count,
                 SUM(oi.quantity * oi.unit_price) as total_amount
                 FROM ordertable o
                 LEFT JOIN orderitem oi ON o.order_id = oi.order_id
                 WHERE o.customer_id = ?
                 GROUP BY o.order_id
                 ORDER BY o.order_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt;
    }
} 