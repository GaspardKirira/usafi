<?php

class UserGateway extends Model
{

    public function getAll(): array
    {
        $sql = "SELECT * FROM users";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): string
    {
        $this->validateUserData($data);

        $otp = $this->generateOtp();
        $otpExpiresAt = date("Y-m-d H:i:s", strtotime("+1 hour"));  // OTP expire dans 1 heure

        $sql = "INSERT INTO users (firstname, lastname, username, email, password, otp, otp_expires_at, is_active) 
                VALUES (:firstname, :lastname, :username, :email, :password, :otp, :otp_expires_at, :is_active)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":firstname", $data['firstname'], PDO::PARAM_STR);
        $stmt->bindValue(":lastname", $data['lastname'], PDO::PARAM_STR);
        $stmt->bindValue(":username", $data['username'], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(":password", password_hash($data['password'], PASSWORD_BCRYPT));
        $stmt->bindValue(":otp", $otp, PDO::PARAM_INT);
        $stmt->bindValue(":otp_expires_at", $otpExpiresAt, PDO::PARAM_STR);
        $stmt->bindValue(":is_active", true, PDO::PARAM_BOOL);

        $stmt->execute();

        $this->sendOtpByEmail($data['email'], $otp);

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array|false
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(array $user, array $data): int
    {
        $fields = [];
        $params = [];

        if (isset($data['firstname'])) {
            $fields[] = "firstname = :firstname";
            $params[':firstname'] = $data['firstname'];
        }
        if (isset($data['lastname'])) {
            $fields[] = "lastname = :lastname";
            $params[':lastname'] = $data['lastname'];
        }
        // Continue for other fields...

        $params[':id'] = $user['id'];
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    private function generateOtp(): int
    {
        return random_int(10000, 99999);
    }

    private function sendOtpByEmail(string $email, int $otp): void
    {
        // Envoi de l'email avec l'OTP (à adapter selon le service d'email utilisé)
        mail($email, "Votre code OTP", "Votre code de vérification est : $otp");
    }

    private function validateUserData(array $data, bool $isUpdate = false): void
    {
        if (!$isUpdate || isset($data['firstname'])) {
            if (empty($data['firstname'])) {
                throw new InvalidArgumentException("First name is required.");
            }
        }
        if (!$isUpdate || isset($data['lastname'])) {
            if (empty($data['lastname'])) {
                throw new InvalidArgumentException("Last name is required.");
            }
        }
        if (!$isUpdate || isset($data['username'])) {
            if (empty($data['username'])) {
                throw new InvalidArgumentException("Username is required.");
            }
        }
        if (!$isUpdate || isset($data['email'])) {
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Valid email is required.");
            }

            // Vérifier si l'email existe déjà
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $data['email']]);
            if ($stmt->fetchColumn() > 0) {
                throw new InvalidArgumentException("Email is already in use.");
            }
        }

        // Validation du mot de passe
        if (empty($data['password'])) {
            throw new InvalidArgumentException("Password is required.");
        } elseif (strlen($data['password']) < 8) {
            throw new InvalidArgumentException("Password must be at least 8 characters long.");
        }
    }
}
