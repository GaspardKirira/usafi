<?php

class SubServiceGateway extends Model
{
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM sub_services");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(string $id): array|false
    {
        $stmt = $this->conn->prepare("SELECT * FROM sub_services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        // Valider les données d'entrée
        if (empty($data['title']) || empty($data['description']) || empty($data['price']) || empty($data['image'])) {
            throw new InvalidArgumentException("All fields are required.");
        }

        $sql = "INSERT INTO sub_services (title, description, price, image) 
            VALUES (:title, :description, :price, :image)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":title", $data['title'], PDO::PARAM_STR);
        $stmt->bindValue(":description", $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(":price", $data['price'], PDO::PARAM_STR);
        $stmt->bindValue(":image", $data['image'], PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();  // Retourne l'ID du sous-service créé
    }

    public function update(string $id, array $data): int
    {
        $stmt = $this->conn->prepare("UPDATE sub_services SET title = :title, description = :description, price = :price, image = :image WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'image' => $data['image']
        ]);
        return $stmt->rowCount();
    }

    public function delete(string $id): int
    {
        $stmt = $this->conn->prepare("DELETE FROM sub_services WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }
}
