<?php

class Model
{
    public PDO $conn;

    public function __construct(Database $conn)
    {
        $this->conn = $conn->getConnection();
    }
}
