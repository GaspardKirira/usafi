<?php

class ReviewController
{
    public function __construct(private ReviewGateway $gateway) {}

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
        $review = $this->gateway->getById($id);

        if (!$review) {
            http_response_code(404);
            echo json_encode(["message" => "Review not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($review);
                break;

            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->gateway->update($id, $data);
                echo json_encode(["message" => "Review $id updated", "rows" => $rows]);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);
                echo json_encode(["message" => "Review $id deleted", "rows" => $rows]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
        }
    }

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
                echo json_encode(["message" => "Review created", "id" => $id]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
}
