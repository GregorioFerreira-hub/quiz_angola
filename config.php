<?php
// config.php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Geralmente 'root' para XAMPP
define('DB_PASSWORD', '');     // Senha vazia para XAMPP, a menos que você tenha configurado uma
define('DB_NAME', 'quiz_angola3');

// Tentar conectar ao banco de dados MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar a conexão
if($conn->connect_error){
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Opcional: Definir o charset para UTF-8 para evitar problemas com acentuação
$conn->set_charset("utf8mb4");

// Esta conexão ($conn) será usada em outros scripts PHP
?>