<?php

class SubServiceController
{
    public function __construct(private SubServiceGateway $gateway) {}

    public function processRequests(string $method, ?string $serviceId = null, ?string $subServiceId = null): void
    {
        if ($serviceId && $subServiceId) {
            $this->linkSubServiceToService($serviceId, $subServiceId);
        } else {
            if ($subServiceId) {
                $this->processResourceRequest($method, $subServiceId);
            } else {
                $this->processCollectionRequest($method);
            }
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        $subService = $this->gateway->get($id);

        if (!$subService) {
            http_response_code(404);
            echo json_encode(["message" => "SubService not found"]);
            return;
        }

        switch ($method) {
            case "GET":
                echo json_encode($subService);
                break;

            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->gateway->update($id, $data);
                echo json_encode(["message" => "SubService $id updated", "rows" => $rows]);
                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);
                echo json_encode(["message" => "SubService $id deleted", "rows" => $rows]);
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
                echo json_encode(["message" => "SubService created", "id" => $id]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    /**
     * Lier un sous-service à un service
     */
    private function linkSubServiceToService(string $serviceId, string $subServiceId): void
    {
        // Vérifier si le service existe
        $stmt = $this->gateway->conn->prepare("SELECT COUNT(*) FROM services WHERE id = :service_id");
        $stmt->execute(['service_id' => $serviceId]);
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(["message" => "Service not found"]);
            return;
        }

        // Vérifier si le sous-service existe
        $stmt = $this->gateway->conn->prepare("SELECT COUNT(*) FROM sub_services WHERE id = :sub_service_id");
        $stmt->execute(['sub_service_id' => $subServiceId]);
        if ($stmt->fetchColumn() == 0) {
            http_response_code(404);
            echo json_encode(["message" => "Sub-service not found"]);
            return;
        }

        // Lier le sous-service au service
        $sql = "INSERT INTO service_sub_service (service_id, sub_service_id) 
                VALUES (:service_id, :sub_service_id)";
        $stmt = $this->gateway->conn->prepare($sql);
        $stmt->bindValue(":service_id", $serviceId, PDO::PARAM_INT);
        $stmt->bindValue(":sub_service_id", $subServiceId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["message" => "Sub-service linked to service"]);
    }
}
