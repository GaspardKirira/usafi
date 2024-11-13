<?php

class OrderController
{
    public function __construct(private OrderGateway $gateway) {}

    public function processRequests(string $method, ?string $id = null): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        $order = $this->gateway->get($id);

        if (!$order) {
            http_response_code(404);
            echo json_encode(["message" => "Order not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($order);
                break;

            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->gateway->updateStatus($id, $data['status']);
                echo json_encode(["message" => "Order $id updated", "rows" => $rows]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH");
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
                echo json_encode(["message" => "Order created", "id" => $id]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }
}
