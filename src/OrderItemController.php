<?php

class OrderItemController
{
    public function __construct(private OrderItemGateway $gateway) {}

    public function processRequests(string $method, ?string $orderId): void
    {
        if ($orderId) {
            $this->processOrderItemsRequest($method, $orderId);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processOrderItemsRequest(string $method, string $orderId): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getByOrderId($orderId));
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $data['order_id'] = $orderId;
                $id = $this->gateway->create($data);
                http_response_code(201);
                echo json_encode(["message" => "Order item created", "id" => $id]);
                break;

            case "DELETE":
                $rows = $this->gateway->deleteByOrderId($orderId);
                echo json_encode(["message" => "Order items for order $orderId deleted", "rows" => $rows]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST, DELETE");
        }
    }

    private function processCollectionRequest(string $method): void
    {
        if ($method === "GET") {
            echo json_encode($this->gateway->getAll());
        } else {
            http_response_code(405);
            header("Allow: GET");
        }
    }
}
