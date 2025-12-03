<?php
class Connect {
    static private $pdo;
    static public function connect(){
        self::$pdo = new PDO("mysql:host=localhost;dbname=Techfit;", "root", "senaisp");
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$pdo;
    }} 