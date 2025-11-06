<?php

require_once "../model/Connect.php";
$pdo = Connect::connect();
Connect::sefuder($pdo);

require_once "../helper/viewHelper.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH );


switch ($uri){
    case "/":
        require __DIR__ . '/../controller/HomeController.php';
        homeController();
        break;
    case "/cu":
        require __DIR__ . "/../controller/cuController.php";
        cuController();
        break;
        
    default:
        echo "Erro 404 - Página não encontrada";
        break;
        
}
?>