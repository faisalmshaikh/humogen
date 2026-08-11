<?php
try {
    $dsn = "mysql:host=dic.crystalregistry.com;port=3306;dbname=khandesh21at_humogen;charset=utf8mb4";
    $pdo = new PDO($dsn, "khandesh21at_adm", "vc!qL7OHy5bs");
    echo "Connected successfully to the remote MariaDB database!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>