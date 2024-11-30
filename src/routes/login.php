<?php
namespace BankAPI;

// załączamy biblioteke mysql
use mysqli;

class LoginPageResponse {
    private string|null $token;
    private string|null $error;

    public function __construct(string|null $token, string|null $error) {
        $this->token = $token;
        $this->error = $error;
    }

    public function send() {
        header('Content-Type: application/json');

        if($this->token) {
            echo json_encode(['token' => $this->token]);
        } else if($this->error) {
            echo json_encode(['error' => $this->error]);
        }
    }
}

class LoginPage {
    private mysqli $dbconn;

    public function __construct(mysqli $dbconn) {
        $this->dbconn = $dbconn;
    }

    public function page() {
        // odczytujemy json wyslany w zapytaniu do serwera
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        $ip = $_SERVER['REMOTE_ADDR'];

        try {
            // Zmienna "login" w tym przypadku oznacza email uzytkownika
            $user = User::select($this->dbconn, $data['login']);
        } catch(\Exception $_) {
            $response = new LoginPageResponse(null, "This user does not exist.");
            $response->send();

            return;
        }

        $passwordOk = $user->verifyPassword($data['password']);

        if(!$passwordOk) {
            $response = new LoginPageResponse(null, "Invalid password");
            $response->send();

            return;
        }

        $token = Token::create($this->dbconn, $ip, $user->getID());
        
        $response = new LoginPageResponse($token->getToken(), null);
        $response->send();

        return;
    }
}