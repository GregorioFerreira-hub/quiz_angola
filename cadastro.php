<?php
// Incluir o ficheiro de conexão com a base de dados
require_once 'db_connect.php';

$mensagem = ''; // Variável para armazenar mensagens de sucesso ou erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_usuario = $_POST['nome_usuario'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validação básica
    if (empty($nome_usuario) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $mensagem = "<p class='error'>Todos os campos são obrigatórios!</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "<p class='error'>Formato de e-mail inválido!</p>";
    } elseif ($senha !== $confirmar_senha) {
        $mensagem = "<p class='error'>As senhas não coincidem!</p>";
    } else {
        // Verificar se o email já existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensagem = "<p class='error'>Este e-mail já está registado!</p>";
        } else {
            // Hash da senha antes de guardar no banco de dados
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir novo usuário
            $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome_usuario, $email, $senha_hash);

            if ($stmt->execute()) {
                $mensagem = "<p class='success'>Registo efetuado com sucesso! <a href='login.php'>Fazer Login</a></p>";
            } else {
                $mensagem = "<p class='error'>Erro ao registar: " . $stmt->error . "</p>";
            }
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
    <title>Registar - Quiz Conheça Angola</title>
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
        .form-group input[type="text"],
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
        <h1>Registar Nova Conta</h1>
        <?php echo $mensagem; // Exibe a mensagem de sucesso ou erro ?>
        <form action="cadastro.php" method="POST">
            <div class="form-group">
                <label for="nome_usuario">Nome de Usuário:</label>
                <input type="text" id="nome_usuario" name="nome_usuario" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <button type="submit" class="btn btn-primary">Registar</button>
        </form>
        <p class="link-text">Já tem uma conta? <a href="login.php">Fazer Login</a></p>
    </div>
</body>
</html>