<?php

class CategoryController
{
    public function __construct(private CategoryGateway $gateway) {}

    // Traiter les requêtes selon le type (GET, POST, PATCH, DELETE)
    public function processRequests(string $method, ?string $id): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        $category = $this->gateway->get($id);

        if (!$category) {
            http_response_code(404);
            echo json_encode(["message" => "Category not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($category);
                break;

            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->gateway->update($id, $data);
                echo json_encode(["message" => "Category $id updated", "rows" => $rows]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH");
        }
    }


    // Traiter une requête pour la collection de catégories (GET, POST)
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $id = $this->gateway->create($data);
                http_response_code(201);
                echo json_encode(["message" => "Category created", "id" => $id]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
}
