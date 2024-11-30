<?php

namespace BankAPI;

class Transfer {
    public static function new(\mysqli $dbconn, int $sourceAccountNo, int $targetAccountNo, int $amount) {
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
