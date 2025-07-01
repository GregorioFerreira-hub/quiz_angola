<?php
require_once 'config.php';
session_start();

// Redireciona se o usuário já estiver logado
if (isset($_SESSION['usuario_id'])) {
    header("location: dashboard.php"); // Redireciona para o dashboard após login
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_usuario = trim($_POST['nome_usuario']);
    $senha = $_POST['senha'];

    if (empty($nome_usuario) || empty($senha)) {
        $message = '<div class="message error">Por favor, preencha todos os campos.</div>';
    } else {
        $stmt = $conn->prepare("SELECT id, nome_usuario, senha, tipo_usuario FROM usuarios WHERE nome_usuario = ?");
        $stmt->bind_param("s", $nome_usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $nome_usuario_db, $senha_hashed, $tipo_usuario);
            $stmt->fetch();

            if (password_verify($senha, $senha_hashed)) {
                // Senha correta, iniciar sessão
                $_SESSION['usuario_id'] = $id;
                $_SESSION['nome_usuario'] = $nome_usuario_db;
                $_SESSION['tipo_usuario'] = $tipo_usuario;

                if ($tipo_usuario == 'admin') {
                    header("location: admin/dashboard_admin.php"); // Redireciona para o painel do admin
                } else {
                    header("location: dashboard.php"); // Redireciona para o dashboard do usuário
                }
                exit;
            } else {
                $message = '<div class="message error">Nome de usuário ou senha inválidos.</div>';
            }
        } else {
            $message = '<div class="message error">Nome de usuário ou senha inválidos.</div>';
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quiz Conheça Angola</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="form-container">
        <div class="form-box">
            <h2>Login</h2>
            <?php echo $message; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="nome_usuario">Nome de Usuário:</label>
                    <input type="text" id="nome_usuario" name="nome_usuario" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="Entrar">
                </div>
            </form>
            <a href="cadastro.php" class="link">Não tem uma conta? Cadastre-se</a>
            <a href="index.php" class="link">Voltar para a Página Inicial</a>
        </div>
    </div>
</body>
</html>