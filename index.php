<?php

// declare(strict_types=1);

// spl_autoload_register(function ($class) {
//     require __DIR__ . "/src/$class.php";
// });

// set_error_handler("ErrorHandler::handleError");
// set_exception_handler("ErrorHandler::handleException");

// header("Content-Type: application/json; charset=UTF-8");

// $parts = explode("/", $_SERVER['REQUEST_URI']);

// if ($parts[1] != "products") {
//     http_response_code(404);
//     exit;
// }

// $id = $parts[2] ?? null;

// $database = new Database("127.0.0.1", "product_db", "root", "");
// $gateway = new ProductGateway($database);

// $controller = new ProductController($gateway);
// $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);

declare(strict_types=1);

// Auto-chargement des classes
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

// Gestion des erreurs et exceptions
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

// Définir l'en-tête HTTP
header("Content-Type: application/json; charset=UTF-8");

// Initialisation de la connexion à la base de données
$database = new Database("127.0.0.1", "product_db", "root", "");

// Récupérer l'URL de la requête
$parts = explode("/", $_SERVER['REQUEST_URI']);
$resource = $parts[1] ?? null;
$id = $parts[2] ?? null;

// Routers pour les différentes ressources (exemple : services, sous-services, commandes, avis)
switch ($resource) {
    case "services":
        // Gestion des services
        $gateway = new ServiceGateway($database);
        $controller = new ServiceController($gateway);
        $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);
        break;

    case "sub_services":
        // Gestion des sous-services
        $gateway = new SubServiceGateway($database);
        $controller = new SubServiceController($gateway);
        $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);
        break;

    case "orders":
        // Gestion des commandes
        $gateway = new OrderGateway($database);
        $controller = new OrderController($gateway);
        $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);
        break;

    case "reviews":
        // Gestion des avis
        $gateway = new ReviewGateway($database);
        $controller = new ReviewController($gateway);
        $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);
        break;

    case "users":
        // Gestion des utilisateurs
        $gateway = new UserGateway($database);
        $controller = new UserController($gateway);
        $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);
        break;

    case "categories":
        // Gestion des catégories
        $gateway = new CategoryGateway($database);
        $controller = new CategoryController($gateway);
        $controller->processRequests($_SERVER["REQUEST_METHOD"], $id);
        break;

    default:
        // Si la ressource n'est pas reconnue
        http_response_code(404);
        echo json_encode(["message" => "Resource not found"]);
        break;
}
