<?php

class OrderGateway extends Model
{
    // Récupérer toutes les commandes
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM orders");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une commande spécifique par son ID
    public function get(string $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer une nouvelle commande
    public function create(array $data): string
    {
        // Vérifier que l'utilisateur existe
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $data['user_id']]);
        if ($stmt->fetchColumn() == 0) {
            throw new InvalidArgumentException("User not found.");
        }

        // Calculer le prix total
        $totalPrice = 0;
        foreach ($data['order_items'] as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Insérer la commande
        $sql = "INSERT INTO orders (user_id, total_price, status) VALUES (:user_id, :total_price, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user_id", $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(":total_price", $totalPrice, PDO::PARAM_STR);
        $stmt->bindValue(":status", 'pending', PDO::PARAM_STR);
        $stmt->execute();

        // Récupérer l'ID de la commande insérée
        $orderId = $this->conn->lastInsertId();

        // Insérer les éléments de la commande
        foreach ($data['order_items'] as $item) {
            $sql = "INSERT INTO order_items (order_id, service_id, sub_service_id, quantity, price) 
                    VALUES (:order_id, :service_id, :sub_service_id, :quantity, :price)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(":order_id", $orderId, PDO::PARAM_INT);
            $stmt->bindValue(":service_id", $item['service_id'], PDO::PARAM_INT);
            $stmt->bindValue(":sub_service_id", $item['sub_service_id'], PDO::PARAM_INT);
            $stmt->bindValue(":quantity", $item['quantity'], PDO::PARAM_INT);
            $stmt->bindValue(":price", $item['price'], PDO::PARAM_STR);
            $stmt->execute();
        }

        return $orderId;
    }

    // Mettre à jour le statut de la commande
    public function updateStatus(string $orderId, string $status): int
    {
        $stmt = $this->conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute(['id' => $orderId, 'status' => $status]);
        return $stmt->rowCount();
    }
}
