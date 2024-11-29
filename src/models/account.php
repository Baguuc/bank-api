<?php
namespace BankAPI;

// załączamy biblioteke mysql
use msqli;

class Account {
    private int $id;
    // wartość amount jest przechowywana w groszach
    // stąd typ zmiennej integer a nie float
    private int $amount;
    private string $name;


    public function __construct(int $id, int $amount, string $name) {
        $this->id = $id;
        $this->amount = $amount;
        $this->name = $name;
    }

    public static function select(mysqli $dbconn, int $id): Account {
        $query = $dbconn->prepare("SELECT * FROM account WHERE accountNo = ?;");
        $query->bind_param("i", $id);
        $query->execute();
        $rows = mysqli_stmt_get_result($query);

        if($rows->num_rows == 0) {
            throw new \Exception("User not found");
        }

        $data = $rows->fetch_array();

        return new Account($data['accountNo'], $data['amount'], $data['name']);
    }

    public function getID(): int {
        return $this->id;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getName(): string {
        return $this->name;
    }

    public function asArray(): array {
        return [
            "id" => $this->id,
            "amount" => $this->amount,
            "name" => $this->name
        ];
    }
}