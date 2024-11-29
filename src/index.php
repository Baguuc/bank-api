<?php

namespace BankAPI;
// załączamy zależności
require_once("../lib/phprouter.php");
use Steampixel\Route;

require_once("../src/routes/models/account.php");
require_once("../src/routes/models/user.php");
require_once("../src/routes/models/token.php");

// załączamy poszczegolne sciezki
require_once("../src/routes/index.php");
require_once("../src/routes/login.php");

// polacz z baza danych i skonfiguruj polaczenie
$dbconn = new \mysqli("localhost", "root", "", "bankAPI");
$dbconn->set_charset('utf8');

// dołączamy ścieżki
const INDEX_ROUTE = new IndexPage();
Route::add("/", function() { return INDEX_ROUTE->page(); });

const LOGIN_ROUTE = new LoginPage($dbconn);
Route::add("/login", function() { return INDEX_ROUTE->page(); }, "post");

Route::run("/bankAPI");