<?php

namespace App\Database;
use PDO;

class Database{
    private static $conexion = null;
    private static $host = 'localhost';
    private static $usuario = 'root';
    private static $password = '123456';
    private static $port = '33061';
    private static $dbname = 'inventario_db';

    public static function conectar() {
        if (self::$conexion === null) {
            try {
                self::$conexion = new \PDO(
                    "mysql:host=" . self::$host .";port=" . self::$port .";dbname=" . self::$dbname .";charset=utf8mb4", 
                    self::$usuario, 
                    self::$password,
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (\PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }

    public static function desconectar() {
        self::$conexion = null;
    }
}