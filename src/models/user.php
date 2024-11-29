<?php
namespace BankAPI;

// załączamy biblioteke mysql
use msqli;

class User {
    private int $id;
    private string $email;
    private string $passwordHash;

    public function __construct(int $id, string $email, string $passwordHash) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    public static function select(mysqli $dbconn, string $email): Account {
        $query = $dbconn->prepare("SELECT * FROM user WHERE email = ?;");
        $query->bind_param("s", $email);
        $query->execute();
        $rows = mysqli_stmt_get_result($query);

        if($rows->num_rows == 0) {
            throw new \Exception("User not found");
        }

        $data = $rows->fetch_array();

        return new Account($data['id'], $data['email'], $data['passwordHash ']);
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->passwordHash);
    }

    public function getID(): int {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPasswordHash(): string {
        return $this->passwordHash;
    }
}