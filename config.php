<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hw14', 'root', '');
    $pdo->exec("SET NAMES utf8;");
} catch (PDOException $e) {
    die("Error!: " . $e->getMessage());
}
 ?>
