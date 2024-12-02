<?php
namespace BankAPI;

// załączamy biblioteke mysql
use mysqli;

class AccountTransfersPageResponse {
    private array|null $transfers;
    private string|null $error;

    public function __construct(array|null $transfers, string|null $error) {
        $this->transfers = $transfers;
        $this->error = $error;
    }

    public function send() {
        if($this->transfers && !$this->error) {
            header("HTTP/1.1 200 OK");
            header('Content-Type: application/json');

            $data = [
                "transfers" => $this->transfers,
                "error" => null
            ];

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        } else if(!$this->transfers && $this->error) {
            header("HTTP/1.1 401 Unauthorized");
            header('Content-Type: application/json');

            $data = [
                "transfers" => null,
                "error" => $this->error
            ];

            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
    }
}

class AccountTransfersPage {
    private mysqli $dbconn;

    public function __construct(mysqli $dbconn) {
        $this->dbconn = $dbconn;
    }

    public function page() {
        // odczytujemy json wyslany w zapytaniu do serwera
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $token = $data['token'];

        if(!Token::verify($this->dbconn, $token, $_SERVER['REMOTE_ADDR'])) {
            // jezeli token jest bledny zwracamy error
            $response = new AccountTransfersPageResponse(null, "Nieprawidlowy token");
            $response->send();

            return;
        }

        try {
            $userId = Token::searchUserId($this->dbconn, $token);
            $transfers = Transfer::selectAll($this->dbconn, $userId);

            $response = new AccountTransfersPageResponse($transfers, null);
            $response->send();
        } catch(\Exception $e) {
            $response = new AccountTransfersPageResponse(null, "This user do not exist.");
            $response->send();

            return;
        }
    }
}