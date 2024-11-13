<?php

class UserController
{
    public function __construct(private UserGateway $gateway) {}

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
        try {
            $user = $this->gateway->get($id);

            if (!$user) {
                http_response_code(404);
                echo json_encode(["message" => "User not found"]);
                return;
            }

            switch ($method) {
                case "GET":
                    echo json_encode($user);
                    break;

                case "PATCH":
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    $errors = $this->getValidationErrors($data, false);

                    if (!empty($errors)) {
                        http_response_code(422);
                        echo json_encode(["errors" => $errors]);
                        return;
                    }

                    $rows = $this->gateway->update($user, $data);
                    echo json_encode(["message" => "User $id updated", "rows" => $rows]);
                    break;

                case "DELETE":
                    $rows = $this->gateway->delete($id);
                    echo json_encode(["message" => "User $id deleted", "rows" => $rows]);
                    break;

                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH, DELETE");
                    break;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Internal server error", "error" => $e->getMessage()]);
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
                $errors = $this->getValidationErrors($data);

                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    return;
                }

                $id = $this->gateway->create($data);
                http_response_code(201);
                echo json_encode(["message" => "User created", "id" => $id]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
                break;
        }
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data["firstname"])) {
            $errors[] = "Firstname is required";
        }

        if ($is_new && empty($data["email"])) {
            $errors[] = "Email is required";
        }

        return $errors;
    }
}
