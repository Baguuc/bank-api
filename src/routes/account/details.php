<?php
namespace BankAPI;

// załączamy biblioteke mysql
use mysqli;

class AccountDetailsPageResponse {
    private Account|null $account;
    private string|null $error;

    public function __construct(Account|null $account, string|null $error) {
        $this->account = $account;
        $this->error = $error;
    }

    public function send() {
        header('Content-Type: application/json');

        if($this->account) {
            echo json_encode($this->account->asArray());
        } else if($this->error) {
            echo json_encode(['error' => $this->error]);
        }
    }
}

class AccountDetailsPage {
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
            // jeżeli token jest błędny zwracamy błąd
            header('HTTP/1.1 401 Unauthorized');

            $response = new AccountDetailsPageResponse(null, "Nieprawidłowy token");
            $response->send();

            return;
        }

        try {
            $userId = Token::searchUserId($this->dbconn, $token);
            $account = Account::selectByUserID($this->dbconn, $userId);
        } catch(\Exception $e) {
            $response = new AccountDetailsPageResponse(null, "This user do not exist.");
            $response->send();

            return;
        }

        $response = new AccountDetailsPageResponse($account, null);
        $response->send();
    }
}