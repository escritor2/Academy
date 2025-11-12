<?php
class Connect {
    static private $pdo;
    static public function connect(){
        self::$pdo = new PDO("mysql:host=localhost;dbname=academia_techfit;", "root", "senaisp");
        return self::$pdo;
    }}