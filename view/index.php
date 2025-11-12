<?php
require_once "../model/Connect.php";
$pdo = Connect::connect();

require_once "../helper/viewHelper.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH );


switch ($uri){
    case "/":
        require __DIR__ . '/../controller/HomeController.php';
        homeController();
        break;
    case "/techfit":
        require __DIR__ . "/../controller/TechfitController.php";
        techfitController();
        break;
    default:
        echo "Erro 404 - Página não encontrada";
        break;
}
?>