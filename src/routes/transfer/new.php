<?php
namespace BankAPI;

// załączamy biblioteke mysql
use mysqli;

class TransferNewPageResponse {
    private string|null $error;

    public function __construct(string|null $error) {
        $this->error = $error;
    }

    public function send() {
        header('Content-Type: application/json');

        if($this->error) {
            echo json_encode($this->error);
        } else {
            echo json_encode(['status' => "OK"]);
        }
    }
}

class TransferNewPage {
    private mysqli $dbconn;

    public function __construct(mysqli $dbconn) {
        $this->dbconn = $dbconn;
    }

    public function page() {
        // odczytujemy dane przeslane JSONem w Requescie
        $data = file_get_contents('php://input');
        $dataArray = json_decode($data, true);

        $token = $dataArray['token'];

        // sprawdz poprawność tokena
        if(!Token::verify($this->dbconn, $token, $_SERVER['REMOTE_ADDR'])) {
            $response = new TransferNewPageResponse("Invalid token");
            $response->send();

            return;
        }
        
        // sprawdz czy kwota przelewu jest ujemna, jesli tak wyrzuc error
        $amount = $dataArray['amount'];

        if($amount < 0) {
            $response = new TransferNewPageResponse("Cannot create a transfer with negative amount.");
            $response->send();

            return;
        }
        
        $userId = Token::searchUserId($this->dbconn, $token);
        $sourceAccount = Account::selectByUserID($this->dbconn, $userId);

        if($sourceAccount->getAmount() < $amount) {
            $response = new TransferNewPageResponse("Your balance is too low to do this transfer.");
            $response->send();

            return;
        }

        // "target" w tej tablicy oznacza numer konta odbiorcy
        $target = $dataArray['target'];
        try {
            Transfer::new($this->dbconn, $sourceAccount->getAccountNo(), $target, $amount);
        } catch(\Exception $ex) {
            $response = new TransferNewPageResponse("The target account do not exist.");
            $response->send();

            return;
        }

        $response = new TransferNewPageResponse(null);
        $response->send();
    }
}