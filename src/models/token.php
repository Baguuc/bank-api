<?php
namespace BankAPI;

// załączamy biblioteke mysql
use mysqli;

class Token {
    private string $ip;
    private int $userID;
    private string $token;

    public function __construct(string $ip, int $userID, string $token) {
        $this->ip = $ip;
        $this->userID = $userID;
        $this->token = $token;
    }

    public static function create(mysqli $db, string $ip, int $userID): Token {
        $timeCreated = time();
        // generacja token na podstawie:
        // + ip użytkownika
        // + ID użytkownika
        // + czas stworzenia
        $dataHashed = hash('sha256', $ip . $userID . $timeCreated);

        $query = $db->prepare("INSERT INTO token (token, ip, user_id) VALUES (?, ?, ?)");
        $query->bind_param('ssi', $dataHashed, $ip, $userID);

        if(!$query->execute()) {
            throw new \Exception('Something went wrong during token creation');
        }
        
        return new Token($ip, $userID, $dataHashed);
    }

    static function searchUserId(mysqli $dbconn, string $token) : int {
        // szukamy jedneid użytkownika przypisane do danego tokenu
        $query = $dbconn->prepare("SELECT user_id FROM token WHERE token = ? LIMIT 1;");
        $query->bind_param('s', $token);
        $query->execute();

        $result = $query->get_result();
        //jeśli nie ma tokenu to wyrzuć wyjątek
        if($result->num_rows == 0) {
            throw new \Exception('Invalid token');
        }
        $row = $result->fetch_assoc();

        return $row['user_id'];
    }
    
    public static function verify(mysqli $db, string $token, string $ip) : bool {
        $query = $db->prepare("SELECT * FROM token WHERE token = ? AND ip = ?");
        $query->bind_param('ss', $token, $ip);
        $query->execute();

        $result = $query->get_result();

        // zwrócenie więcej niż 0 wierszy
        // bedzie oznaczać, że taki token istnieje
        return $result->num_rows > 0;
    }

    public function getToken(): string {
        return $this->token;
    }

    public function getUserId(): int {
        return $this->userID;
    }

    public function getIP(): string {
        return $this->ip;
    }
}

?>