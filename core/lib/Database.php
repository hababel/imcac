<?php
namespace Core\Lib; use PDO;
class Database {
  private static ?PDO $pdo = null;
  public static function pdo(): PDO {
    if (self::$pdo === null) {
      $host = getenv('DB_HOST') ?: '127.0.0.1';
      $db   = getenv('DB_NAME') ?: 'database_imcac';
      $user = getenv('DB_USER') ?: 'root';
      $pass = getenv('DB_PASS') ?: '';
      $port = getenv('DB_PORT') ?: '3306';
      $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
      $opt = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false];
      self::$pdo = new PDO($dsn, $user, $pass, $opt);
      self::$pdo->exec("SET time_zone = '+00:00'");
    }
    return self::$pdo;
  }
}
