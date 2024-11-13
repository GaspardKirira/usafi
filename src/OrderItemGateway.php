<?php

class OrderItemGateway extends Model
{
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM order_items");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByOrderId(string $orderId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        // Vérifier si le service existe dans la table services
        if ($data['service_id']) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM services WHERE id = :service_id");
            $stmt->execute(['service_id' => $data['service_id']]);
            if ($stmt->fetchColumn() == 0) {
                throw new InvalidArgumentException("Service not found.");
            }
        }

        // Vérifier si le sous-service existe dans la table sub_services
        if ($data['sub_service_id']) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM sub_services WHERE id = :sub_service_id");
            $stmt->execute(['sub_service_id' => $data['sub_service_id']]);
            if ($stmt->fetchColumn() == 0) {
                throw new InvalidArgumentException("Sub-service not found.");
            }
        }

        // Insérer l'élément de commande
        $stmt = $this->conn->prepare("INSERT INTO order_items (order_id, service_id, sub_service_id, quantity, price) 
        VALUES (:order_id, :service_id, :sub_service_id, :quantity, :price)");
        $stmt->execute([
            'order_id' => $data['order_id'],
            'service_id' => $data['service_id'] ?? null,
            'sub_service_id' => $data['sub_service_id'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'price' => $data['price']
        ]);
        return $this->conn->lastInsertId();
    }


    public function deleteByOrderId(string $orderId): int
    {
        $stmt = $this->conn->prepare("DELETE FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->rowCount();
    }
}
