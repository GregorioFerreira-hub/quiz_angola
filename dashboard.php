<?php
session_start(); // Inicia a sessão PHP

// Verifica se o utilizador está logado (se o ID de utilizador está definido na sessão)
if (!isset($_SESSION['id_usuario'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login.php");
    exit(); // Termina a execução do script para garantir o redirecionamento
}

// Obtém o nome do utilizador da sessão para exibir uma saudação personalizada
$nome_usuario = $_SESSION['nome_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz Conheça Angola</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #EF0000 50%, #000000 50%); /* Fundo com as cores da bandeira de Angola */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95); /* Fundo branco semi-transparente */
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3); /* Sombra para profundidade */
            max-width: 700px;
            width: 90%;
            color: #333; /* Cor do texto principal */
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #EF0000; /* Vermelho vibrante para títulos */
        }
        p {
            font-size: 1.2em;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #555; /* Cor do texto secundário */
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px; /* Espaço entre os botões */
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra nos botões */
        }
        .btn-primary {
            background-color: #FFCD00; /* Amarelo da bandeira */
            color: #000000; /* Texto preto para contraste */
            border: 2px solid #CCA300; /* Borda mais escura do amarelo */
        }
        .btn-primary:hover {
            background-color: #CCA300;
            transform: translateY(-2px); /* Efeito de elevação ao passar o rato */
        }
        .btn-secondary {
            background-color: #000000; /* Preto da bandeira */
            color: white;
            border: 2px solid #333333; /* Borda mais escura do preto */
        }
        .btn-secondary:hover {
            background-color: #333333;
            transform: translateY(-2px); /* Efeito de elevação ao passar o rato */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</h1>
        <p>Está pronto para testar os seus conhecimentos sobre Angola?</p>
        
        <div class="buttons-container">
            <a href="quiz_game.php" class="btn btn-primary">Iniciar Quiz</a>
            <a href="logout.php" class="btn btn-secondary">Sair</a>
        </div>
    </div>
</body>
</html>