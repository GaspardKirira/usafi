<?php

class ReviewGateway extends Model
{
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM reviews");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(string $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByServiceId(string $serviceId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE service_id = :service_id");
        $stmt->execute(['service_id' => $serviceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserId(string $userId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        $stmt = $this->conn->prepare("INSERT INTO reviews (user_id, service_id, rating, review) VALUES (:user_id, :service_id, :rating, :review)");
        $stmt->execute([
            'user_id' => $data['user_id'],
            'service_id' => $data['service_id'],
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    public function update(string $id, array $data): int
    {
        $stmt = $this->conn->prepare("UPDATE reviews SET rating = :rating, review = :review WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null
        ]);
        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $stmt = $this->conn->prepare("DELETE FROM reviews WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
