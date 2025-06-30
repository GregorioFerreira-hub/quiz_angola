<?php
require_once 'db_connect.php'; // Inclui o ficheiro de conexão

if ($conn) {
    echo "Conexão com a base de dados 'quiz_angola' estabelecida com sucesso!";
    $conn->close(); // Fechar a conexão
} else {
    echo "Erro na conexão com a base de dados.";
}
?>