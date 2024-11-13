
<?php

class ProductController
{
    public function __construct(private ProductGateway $gateway) {}
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
        // Récupération du produit
        $product = $this->gateway->get($id);

        // Si le produit n'existe pas, retourner une erreur 404
        if (!$product) {
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
            return;
        }

        // Traitement selon la méthode HTTP
        switch ($method) {
            case "GET":
                // Retourne le produit si la méthode est GET
                echo json_encode($product);
                break;

            case "PATCH":
                // Récupération des données de la requête
                $data = (array) json_decode(file_get_contents("php://input"), true);

                // Validation des données envoyées
                $errors = $this->getValidationErrors($data, false);
                if (!empty($errors)) {
                    http_response_code(422); // Erreur de validation
                    echo json_encode(["errors" => $errors]);
                    return; // Ne pas poursuivre si les données sont invalides
                }

                // Mise à jour du produit
                try {
                    $rows = $this->gateway->update($product, $data);
                    echo json_encode([
                        "message" => "Product $id updated",
                        "rows" => $rows
                    ]);
                } catch (InvalidArgumentException $e) {
                    http_response_code(400);  // Erreur 400 pour une requête mal formée
                    echo json_encode([
                        "error" => $e->getMessage()
                    ]);
                }
                break;

            case "DELETE":
                if (!$id) {
                    http_response_code(400);
                    echo json_encode([
                        "error" => "Product ID is required for deletion"
                    ]);
                    return;
                }

                // Suppression du produit avec l'ID fourni
                $rows = $this->gateway->delete($id);

                if ($rows > 0) {
                    echo json_encode([
                        "message" => "Product $id deleted",
                        "rows" => $rows
                    ]);
                } else {
                    http_response_code(404);  // Not Found
                    echo json_encode([
                        "error" => "Product not found"
                    ]);
                }
                break;


            default:
                // Méthode HTTP non supportée
                http_response_code(405);
                header("Allow: GET, POST, DELETE");
                break;
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

                // Validation des données
                $errors = $this->getValidationErrors($data);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    return;  // Empêche la création si les données sont invalides
                }

                // Si pas d'erreurs, procéder à la création
                try {
                    $id = $this->gateway->create($data);
                    http_response_code(201);
                    echo json_encode([
                        "message" => "Product created",
                        "id" => $id
                    ]);
                } catch (InvalidArgumentException $e) {
                    http_response_code(400);  // Bad Request
                    echo json_encode([
                        "error" => $e->getMessage()
                    ]);
                }
                break;

            default:
                http_response_code(405);  // Method Not Allowed
                header("Allow: GET, POST, DELETE");
                echo json_encode([
                    "error" => "Method not allowed"
                ]);
        }
    }


    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data["name"])) {
            $errors[] = "name is required";
        }

        if (array_key_exists("size", $data)) {
            if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "size must be an integer";
            }
        }

        return $errors;
    }
}
