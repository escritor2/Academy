<?php
class Connect {
    static private $pdo;
    static public function connect(){
        self::$pdo = new PDO("mysql:host=localhost;dbname=Techfit;", "root", "senaisp");
        return self::$pdo;
    }} 