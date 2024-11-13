<?php

class ServiceGateway extends Model
{
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM services");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(string $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        // Obtenir la date et l'heure actuelles
        $currentDate = (new DateTime())->format('Y-m-d H:i:s');

        // Vérifier si l'ID de l'utilisateur est fourni, sinon utiliser une valeur par défaut (ex: 1 pour l'admin)
        $createdById = $data['created_by_id'] ?? 1; // Vous pouvez récupérer l'ID de l'utilisateur authentifié ici
        $updatedById = $data['updated_by_id'] ?? 1; // Idem pour l'ID de mise à jour, utiliser la même valeur ou différente

        // Préparer la requête d'insertion
        $stmt = $this->conn->prepare("INSERT INTO services (title, description, category, image, rating, reviews, created_by_id, updated_by_id, created_at, updated_at) 
                                 VALUES (:title, :description, :category, :image, :rating, :reviews, :created_by_id, :updated_by_id, :created_at, :updated_at)");

        // Exécuter la requête en passant les données, y compris les dates
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'image' => $data['image'],
            'rating' => $data['rating'] ?? 0.0,
            'reviews' => $data['reviews'] ?? 0,
            'created_by_id' => $createdById,
            'updated_by_id' => $updatedById,
            'created_at' => $currentDate,
            'updated_at' => $currentDate
        ]);

        return $this->conn->lastInsertId();
    }

    public function update(string $id, array $data): int
    {
        $stmt = $this->conn->prepare("UPDATE services SET title = :title, description = :description, category = :category, image = :image, rating = :rating, reviews = :reviews, updated_by_id = :updated_by_id WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'image' => $data['image'],
            'rating' => $data['rating'],
            'reviews' => $data['reviews'],
            'updated_by_id' => $data['updated_by_id']
        ]);
        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $stmt = $this->conn->prepare("DELETE FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
