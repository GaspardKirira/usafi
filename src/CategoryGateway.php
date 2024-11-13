<?php

class CategoryGateway extends Model
{
    // Récupérer toutes les catégories
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer une catégorie par son ID
    public function get(string $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer une nouvelle catégorie
    public function create(array $data): string
    {
        $stmt = $this->conn->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        return $this->conn->lastInsertId();
    }

    // Mettre à jour une catégorie existante
    public function update(string $id, array $data): int
    {
        $stmt = $this->conn->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        return $stmt->rowCount();
    }

    // Supprimer une catégorie
    public function delete(string $id): int
    {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
