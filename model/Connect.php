<?php
class Connect {
    private static $pdo;
    
    public static function connect() {
        if (self::$pdo == null) {
            try {
                self::$pdo = new PDO("mysql:host=localhost;dbname=Techfit;charset=utf8", "root", "senaisp");
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Erro de conexão: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>