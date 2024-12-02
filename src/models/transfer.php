<?php

namespace BankAPI;

use mysqli;

class Transfer {
    public static function selectAll(mysqli $dbconn, int $userID): array {
        $sql = "
            SELECT 
                t.timestamp,
                t.amount,
                a2.name as targetName
            FROM 
                account a1
            INNER JOIN 
                transfer t
            ON
                a1.accountNo = t.source
            INNER JOIN
                account a2
            ON
                a2.accountNo = t.target
            WHERE
	            a1.user_id = ?;
        ";

        try {
            $query = $dbconn->prepare($sql);
            $query->bind_param('i', $userID);
            $query->execute();

            $result = mysqli_stmt_get_result($query);
            $transfers = [];

            while($row = $result->fetch_assoc()) {
                array_push($transfers, $row);
            }

            return $transfers;
        } catch(\Exception $_) {
            $dbconn->rollback();

            throw new \Exception("Cannot find the account to tranfer the money from.");
        }
    }

    public static function new(mysqli $dbconn, int $sourceAccountNo, int $targetAccountNo, int $amount) {
        //rozpocznij transakcje
        $dbconn->begin_transaction();

        // dla czytelności podzieliłem wszystko na osobne scope'y
        // (nie wiem jak to przetłumaczyć na polski, poprostu chodzi o oddzielne bloki try/catch)
        try {
            // blok odejmujący kwote przelewu od konta nadawcy
            $query = $dbconn->prepare("UPDATE account SET amount = amount - ? WHERE accountNo = ?");
            $query->bind_param('ii', $amount, $sourceAccountNo);
            $query->execute();
        } catch(\Exception $_) {
            $dbconn->rollback();

            throw new \Exception("Cannot find the account to tranfer the money from.");
        }

        try {
             // blok dodajacy kwote przelewu do konta odbiorcy
            $query = $dbconn->prepare("UPDATE account SET amount = amount + ? WHERE accountNo = ?");
            $query->bind_param('ii', $amount, $targetAccountNo);
            $query->execute();
        } catch(\Exception $_) {
            $dbconn->rollback();
            
            throw new \Exception("Cannot find the account to tranfer the money to.");
        }

        try { 
            // blok zapisujący transfer do listy transferów
            $sql = "INSERT INTO transfer (source, target, amount) VALUES (?, ?, ?)";
            $query = $dbconn->prepare($sql);
            $query->bind_param('iii', $sourceAccountNo, $targetAccountNo, $amount);
            $query->execute();
            $dbconn->commit();
        } catch(\Exception $_) {
            $dbconn->rollback();
            
            throw new \Exception("Cannot save the transfer.");
        }

        $dbconn->commit();
    }
}
