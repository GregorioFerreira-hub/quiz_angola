<?php

$servername = "localhost";
$username = "root"; // Nome de usuário padrão do XAMPP/WAMP
$password = "";     // Senha padrão do XAMPP/WAMP (geralmente vazia)
$dbname = "quiz_angola";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
// else {
//     echo "Conexão bem-sucedida!"; // Pode remover esta linha após o teste inicial
// }

?>