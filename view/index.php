<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH );
require __DIR__ . '/../controller/HomeController.php';

switch ($uri){
    case "/":
        homeControl();
        break;
    default:
        echo "Erro 404 - Página não encontrada";
        break;
        
}