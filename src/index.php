<?php

namespace BankAPI;
// załączamy zależności
require_once("../lib/phprouter.php");
use Steampixel\Route;

// załączamy modele
require_once("../src/models/account.php");
require_once("../src/models/user.php");
require_once("../src/models/token.php");
require_once("../src/models/transfer.php");

// załączamy poszczegolne sciezki
require_once("../src/routes/index.php");
require_once("../src/routes/login.php");
require_once("../src/routes/account/details.php");
require_once("../src/routes/account/transfers.php");
require_once("../src/routes/transfer/new.php");

// polacz z baza danych i skonfiguruj polaczenie
$dbconn = new \mysqli("localhost", "root", "", "bankAPI");
$dbconn->set_charset('utf8');

// dołączamy ścieżki
Route::add("/login", function() use($dbconn) { 
    (new LoginPage($dbconn))->page();
}, "post");

Route::add("/account/details", function() use($dbconn) {
    (new AccountDetailsPage($dbconn))->page();
}, "post");

Route::add("/account/transfers", function() use($dbconn) {
    (new AccountTransfersPage($dbconn))->page();
}, "post");

Route::add("/transfer/new", function() use($dbconn) {
    (new TransferNewPage($dbconn))->page();
}, "post");

Route::run("/bankapi");