<?php
/**
 * PostgreSQL Database Connection Handler (PDO Singleton)
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Handle if database donasi_mandeh is not created yet
                try {
                    $rootDsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=postgres";
                    $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS);
                    $rootPdo->exec("CREATE DATABASE " . DB_NAME);
                    
                    // Reconnect
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                } catch (PDOException $ex) {
                    error_log("Database Connection Error: " . $e->getMessage());
                    throw new Exception("Koneksi database gagal: " . $e->getMessage());
                }
            }
        }
        return self::$instance;
    }
}
