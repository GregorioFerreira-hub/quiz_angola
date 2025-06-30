<?php
// Incluir o ficheiro de conexão com a base de dados
require_once 'db_connect.php';

session_start(); // Iniciar a sessão para gerir o estado de login

$mensagem = ''; // Variável para armazenar mensagens de sucesso ou erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Validação básica
    if (empty($email) || empty($senha)) {
        $mensagem = "<p class='error'>Por favor, preencha todos os campos.</p>";
    } else {
        // Preparar e executar a consulta para buscar o usuário pelo email
        $stmt = $conn->prepare("SELECT id_usuario, nome_usuario, senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id_usuario, $nome_usuario, $senha_hash); // Bind para buscar o hash da senha

        if ($stmt->num_rows == 1) {
            $stmt->fetch();
            // Verificar a senha usando password_verify
            if (password_verify($senha, $senha_hash)) {
                /*
                // Senha correta, iniciar sessão
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['nome_usuario'] = $nome_usuario;
                // Redirecionar para a página principal do quiz (que vamos desenvolver mais tarde)
                header("Location: dashboard.php"); // Redirecionar para uma dashboard ou quiz_game.php
                exit();
                */
                // Senha correta, iniciar sessão
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['nome_usuario'] = $nome_usuario;
                
                // --- NOVO: Redirecionar para painel admin se for o utilizador com ID 1 ---
                if ($id_usuario == 1) { // Supondo que o usuário com ID 1 é o administrador
                    $_SESSION['is_admin'] = true;
                    header("Location: admin_dashboard.php"); 
                } else {
                    $_SESSION['is_admin'] = false; // Define como não-admin para usuários comuns
                    header("Location: dashboard.php");
                }
                exit();
                // --- FIM NOVO ---
            } else {
                $mensagem = "<p class='error'>E-mail ou senha incorretos.</p>";
            }
        } else {
            $mensagem = "<p class='error'>E-mail ou senha incorretos.</p>";
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
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #EF0000 50%, #000000 50%); /* Cores da bandeira */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 90%;
            color: #333;
            text-align: left;
        }
        h1 {
            font-size: 2.2em;
            margin-bottom: 25px;
            color: #EF0000;
            text-align: center;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: calc(100% - 20px);
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-primary {
            background-color: #FFCD00; /* Amarelo */
            color: #000000; /* Preto */
            border: 2px solid #CCA300;
        }
        .btn-primary:hover {
            background-color: #CCA300;
            transform: translateY(-2px);
        }
        .link-text {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.95em;
            color: #555;
        }
        .link-text a {
            color: #EF0000; /* Vermelho */
            text-decoration: none;
            font-weight: bold;
        }
        .link-text a:hover {
            text-decoration: underline;
        }
        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php echo $mensagem; // Exibe a mensagem de sucesso ou erro ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
        <p class="link-text">Não tem uma conta? <a href="cadastro.php">Registar agora</a></p>
    </div>
</body>
</html>