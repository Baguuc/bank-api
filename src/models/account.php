<?php
namespace BankAPI;

// załączamy biblioteke mysql
use msqli;

class Account {
    private int $accountNo;
    // wartość amount jest przechowywana w groszach
    // stąd typ zmiennej integer a nie float
    private int $amount;
    private string $name;


    public function __construct(int $accountNo, int $amount, string $name) {
        $this->accountNo = $accountNo;
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

    public static function selectByUserID(mysqli $dbconn, int $userID): Account {
        $query = $dbconn->prepare("SELECT * FROM account WHERE user_id = ?;");
        $query->bind_param("i", $userID);
        $query->execute();
        $rows = mysqli_stmt_get_result($query);

        if($rows->num_rows == 0) {
            throw new \Exception("User not found");
        }

        $data = $rows->fetch_array();

        return new Account($data['accountNo'], $data['amount'], $data['name']);
    }

    public function getAccountNo(): int {
        return $this->accountNo;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getName(): string {
        return $this->name;
    }

    public function asArray(): array {
        return [
            "id" => $this->accountNo,
            "amount" => $this->amount,
            "name" => $this->name
        ];
    }
}