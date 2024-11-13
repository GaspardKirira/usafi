-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- Identifiant unique pour chaque utilisateur
    firstname VARCHAR(255) NOT NULL,             -- Prénom de l'utilisateur
    lastname VARCHAR(255) NOT NULL,              -- Nom de l'utilisateur
    username VARCHAR(255) NOT NULL UNIQUE,       -- Nom d'utilisateur unique
    email VARCHAR(255) NOT NULL UNIQUE,          -- Email unique
    password VARCHAR(255) NOT NULL,              -- Mot de passe
    is_active BOOLEAN DEFAULT TRUE,              -- Si l'utilisateur est actif
    blocked BOOLEAN DEFAULT FALSE,               -- Si l'utilisateur est bloqué
    prefered_language VARCHAR(10) DEFAULT 'en',  -- Langue préférée de l'utilisateur
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Date de création
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Date de mise à jour
);


-- Table des services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- Identifiant unique pour chaque service
    title VARCHAR(255) NOT NULL,                 -- Titre du service
    description TEXT NOT NULL,                   -- Description du service
    category VARCHAR(255) NOT NULL,              -- Catégorie du service
    image VARCHAR(255) NOT NULL,                 -- Image du service
    rating DECIMAL(3, 2) DEFAULT 0.0,            -- Note du service (ex. 4.5)
    reviews INT DEFAULT 0,                       -- Nombre de reviews (avis)
    created_by_id INT NOT NULL,                  -- L'ID de l'utilisateur ayant créé ce service
    updated_by_id INT NOT NULL,                  -- L'ID de l'utilisateur ayant mis à jour ce service
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Date de création
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- Date de mise à jour
    FOREIGN KEY (created_by_id) REFERENCES users(id),  -- Clé étrangère vers la table des utilisateurs
    FOREIGN KEY (updated_by_id) REFERENCES users(id)   -- Clé étrangère vers la table des utilisateurs
);


-- Table des sous-services
CREATE TABLE IF NOT EXISTS sub_services (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- Identifiant unique pour chaque sous-service
    title VARCHAR(255) NOT NULL,                 -- Titre du sous-service
    description TEXT NOT NULL,                   -- Description du sous-service
    price DECIMAL(10, 2) NOT NULL,               -- Prix du sous-service
    image VARCHAR(255) NOT NULL,                 -- Image du sous-service
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Date de création
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP   -- Date de mise à jour
);


-- Table de relation entre services et sous-services
CREATE TABLE IF NOT EXISTS service_sub_service (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Identifiant unique
    service_id INT NOT NULL,                    -- Clé étrangère vers la table services
    sub_service_id INT NOT NULL,                -- Clé étrangère vers la table sub_services
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Date de création
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,  -- Clé étrangère vers services
    FOREIGN KEY (sub_service_id) REFERENCES sub_services(id) ON DELETE CASCADE  -- Clé étrangère vers sub_services
);


-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- Identifiant unique pour chaque commande
    user_id INT NOT NULL,                        -- L'ID de l'utilisateur ayant passé la commande
    total_price DECIMAL(10, 2) NOT NULL,         -- Prix total de la commande
    status VARCHAR(50) DEFAULT 'pending',        -- Statut de la commande (en attente, terminée, etc.)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Date de création
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- Date de mise à jour
    FOREIGN KEY (user_id) REFERENCES users(id)   -- Clé étrangère vers la table des utilisateurs
);


-- Table des éléments de la commande (services ou sous-services dans une commande)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- Identifiant unique pour chaque élément de commande
    order_id INT NOT NULL,                       -- Clé étrangère vers la table des commandes
    service_id INT,                              -- Clé étrangère vers un service (peut être NULL si c'est un sous-service)
    sub_service_id INT,                          -- Clé étrangère vers un sous-service (peut être NULL si c'est un service)
    quantity INT DEFAULT 1,                      -- Quantité de l'élément commandé
    price DECIMAL(10, 2) NOT NULL,               -- Prix unitaire de l'élément
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,  -- Clé étrangère vers orders
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,  -- Clé étrangère vers services
    FOREIGN KEY (sub_service_id) REFERENCES sub_services(id) ON DELETE CASCADE  -- Clé étrangère vers sub_services
);


-- Table des avis des utilisateurs sur les services
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,           -- Identifiant unique pour chaque avis
    user_id INT NOT NULL,                        -- L'ID de l'utilisateur ayant laissé l'avis
    service_id INT NOT NULL,                     -- L'ID du service évalué
    rating DECIMAL(3, 2) NOT NULL,               -- Note de l'avis (ex. 4.5)
    review TEXT,                                 -- Contenu de l'avis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Date de création
    FOREIGN KEY (user_id) REFERENCES users(id),  -- Clé étrangère vers la table des utilisateurs
    FOREIGN KEY (service_id) REFERENCES services(id)  -- Clé étrangère vers la table des services
);
