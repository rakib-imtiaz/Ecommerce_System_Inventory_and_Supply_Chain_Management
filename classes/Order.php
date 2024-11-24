<?php
class Order {
    private $conn;
    private $table_name = "ordertable";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllOrders($search = '', $status = '', $sort = '', $limit = 10, $page = 1) {
        $query = "SELECT o.*, c.name as customer_name, 
                 COUNT(oi.order_item_id) as item_count,
                 SUM(oi.quantity * oi.unit_price) as total_amount
                 FROM " . $this->table_name . " o
                 LEFT JOIN customer c ON o.customer_id = c.customer_id
                 LEFT JOIN orderitem oi ON o.order_id = oi.order_id
                 WHERE 1=1";
        
        $params = [];

        if (!empty($search)) {
            $query .= " AND (c.name LIKE ? OR o.order_id LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($status)) {
            $query .= " AND o.order_status = ?";
            $params[] = $status;
        }

        $query .= " GROUP BY o.order_id";

        switch ($sort) {
            case 'date_asc':
                $query .= " ORDER BY o.order_date ASC";
                break;
            case 'date_desc':
                $query .= " ORDER BY o.order_date DESC";
                break;
            case 'amount_asc':
                $query .= " ORDER BY total_amount ASC";
                break;
            case 'amount_desc':
                $query .= " ORDER BY total_amount DESC";
                break;
            default:
                $query .= " ORDER BY o.order_id DESC";
        }

        $offset = ($page - 1) * $limit;
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function getOrder($id) {
        $query = "SELECT o.*, c.name as customer_name, c.shipping_address
                 FROM " . $this->table_name . " o
                 LEFT JOIN customer c ON o.customer_id = c.customer_id
                 WHERE o.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($order_id) {
        $query = "SELECT oi.*, p.name as product_name
                 FROM orderitem oi
                 LEFT JOIN product p ON oi.product_id = p.product_id
                 WHERE oi.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->execute();

        return $stmt;
    }

    public function create($customer_id, $items) {
        try {
            $this->conn->beginTransaction();

            // Create order
            $query = "INSERT INTO " . $this->table_name . " (customer_id, order_date, order_status) 
                     VALUES (?, CURRENT_DATE, 'Pending')";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$customer_id]);
            $order_id = $this->conn->lastInsertId();

            // Add order items
            $query = "INSERT INTO orderitem (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            foreach ($items as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price']
                ]);

                // Update product stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }

            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    private function updateProductStock($product_id, $quantity) {
        $query = "UPDATE product 
                 SET stock_level = stock_level - ? 
                 WHERE product_id = ? AND stock_level >= ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$quantity, $product_id, $quantity]);
    }

    public function updateStatus($order_id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                 SET order_status = ? 
                 WHERE order_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $order_id]);
    }
} 