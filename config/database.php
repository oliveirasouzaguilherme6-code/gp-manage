<?php

class Database
{
    private string $host = "localhost";
    private string $db = "gp-manager";
    private string $user = "root";
    private string $pass = "";

    public PDO $conn;

    public function connect(): PDO
    {
        try {

            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->pass
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->conn;

        } catch(PDOException $e){

            die($e->getMessage());

        }
    }
}