<?php
namespace BankAPI;

// załączamy biblioteke mysql
use msqli;

class Token {
    private string $ip;
    private int $userID;
    private string $token;

    public function __construct(string $ip, int $userID, string $token) {
        $this->ip = $ip;
        $this->userID = $userID;
        $this->token = $token;
    }

    static function create(mysqli $db, string $ip, int $userID): Token {
        $timeCreated = time();
        // generacja token na podstawie:
        // + ip użytkownika
        // + ID użytkownika
        // + czas stworzenia
        $dataHashed = hash('sha256', $ip . $userID . $timeCreated);

        $query = $db->prepare("INSERT INTO token (token, ip, userId) VALUES (?, ?, ?)");
        $query->bind_param('ssi', $dataHashed, $ip, $userID);

        if(!$query->execute()) {
            throw new Exception('Something went wrong during token creation');
        }
        
        return new Token($ip, $userID, $dataHashed);
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
}

?>