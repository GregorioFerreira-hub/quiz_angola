<?php
session_start(); // Inicia a sessão PHP existente

// 1. Destrói todas as variáveis de sessão
// Isso remove todos os dados armazenados na sessão (como 'id_usuario', 'nome_usuario', 'pontuacao', etc.)
$_SESSION = array();

// 2. Se for preciso destruir os cookies de sessão, apaga também o cookie de sessão.
// Isso é importante para garantir que o identificador da sessão no navegador do utilizador seja removido.
// Nota: Isto irá destruir a sessão, e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, // Define o tempo de expiração no passado
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destrói a sessão no servidor.
session_destroy();

// 4. Redireciona o utilizador para a página de login ou para a landing page após o logout
header("Location: landing.php"); // Podes mudar para 'login.php' se preferires que vá diretamente para o ecrã de login
exit(); // Garante que nenhum outro código PHP seja executado após o redirecionamento
?>