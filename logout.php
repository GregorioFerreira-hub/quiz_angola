<?php
// Inicia a sessão (necessário para acessar as variáveis de sessão)
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se estiver usando cookies de sessão, também destrói o cookie.
// Nota: Isso irá destruir a sessão e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

// Redireciona o usuário para a página de login ou página inicial
header("location: login.php"); // Mude para a sua página de login ou página inicial
exit; // Garante que o script pare de executar após o redirecionamento
?>