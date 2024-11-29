<?php

namespace BankAPI;
// załączamy zależności
require_once("../lib/phprouter.php");
use Steampixel\Route;

// załączamy poszczegolne sciezki
require_once("../src/routes/index.php");

// dołączamy ścieżki
const INDEX_ROUTE = new IndexPage();
Route::add(INDEX_ROUTE->ROUTE_PATH, function() { return INDEX_ROUTE->page(); });

Route::run("/bankAPI");