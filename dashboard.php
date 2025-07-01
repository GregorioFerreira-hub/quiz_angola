<?php
// Inicia a sessão no início do script. ESSENCIAL!
session_start();

// Inclui o arquivo de configuração do banco de dados APENAS UMA VEZ.
// Usar 'require_once' é crucial para evitar o erro "Constant already defined".
require_once 'config.php';

// Redirecionar se o usuário não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("location: login.php"); // Certifique-se de que 'login.php' é o caminho correto para sua página de login
    exit;
}

// Conecta ao banco de dados usando as constantes definidas em config.php
// A conexão $conn é criada aqui e será reutilizada.
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica a conexão. Se houver erro, para a execução do script.
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Obter a pontuação total do usuário da tabela 'pontuacoes'
$pontuacao_total = 0;
// Verifica se a sessão 'usuario_id' está definida antes de tentar buscar no DB
if (isset($_SESSION['usuario_id'])) {
    // Prepara a consulta para evitar SQL Injection
    $stmt = $conn->prepare("SELECT pontuacao_total FROM pontuacoes WHERE usuario_id = ?");
    // Verifica se a preparação da query foi bem-sucedida
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['usuario_id']); // 'i' indica que o parâmetro é um inteiro
        $stmt->execute();
        $stmt->bind_result($pontuacao_total_db); // Associa o resultado a uma variável
        if ($stmt->fetch()) { // Obtém o resultado
            $pontuacao_total = $pontuacao_total_db;
        }
        $stmt->close(); // Fecha o statement
    } else {
        // Erro na preparação da query, pode logar ou exibir uma mensagem
        error_log("Erro na preparação da query para pontuação total: " . $conn->error);
    }
}

// O nome do usuário já deve estar na sessão após o login
$nome_usuario = isset($_SESSION['nome_usuario']) ? htmlspecialchars($_SESSION['nome_usuario']) : 'Convidado';

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz Conheça Angola</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="container">
            <h1>Bem-vindo, <?php echo $nome_usuario; ?>!</h1>
            <p>Sua pontuação total: <span class="score-display"><?php echo $pontuacao_total; ?></span></p>
            <nav>
                <a href="quiz.php" class="btn primary">Começar Quiz</a>
                <a href="ranking.php" class="btn secondary">Ranking Global</a>
                <a href="logout.php" class="btn ghost">Sair</a>
            </nav>
        </div>
    </header>

    <main class="container quiz-selection">
        <h2>Escolha uma Categoria para Jogar</h2>
        <div class="categories-grid">
            <?php
            // REUTILIZA a conexão $conn já aberta no início do script.
            // NÃO inclua 'config.php' novamente aqui, nem reabra a conexão.
            $sql = "SELECT id, nome_categoria FROM categorias ORDER BY nome_categoria";
            $result = $conn->query($sql); // Executa a query usando a conexão existente

            if ($result) { // Verifica se a query foi bem-sucedida
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="category-card">';
                        echo '<h3>' . htmlspecialchars($row['nome_categoria']) . '</h3>';
                        // Garante que o ID da categoria é seguro para URL
                        echo '<a href="quiz.php?categoria_id=' . htmlspecialchars($row['id']) . '" class="btn primary">Jogar</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Nenhuma categoria encontrada.</p>';
                }
            } else {
                // Erro na query SQL, pode logar ou exibir uma mensagem
                echo '<p>Erro ao carregar categorias: ' . $conn->error . '</p>';
                error_log("Erro ao carregar categorias: " . $conn->error);
            }
            // A conexão $conn será fechada automaticamente no final do script
            // ou você pode fechá-la explicitamente aqui se não for mais usá-la:
            // $conn->close(); // Se você fechar aqui, remova o $conn->close() anterior.
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Quiz Conheça Angola. Todos os direitos reservados.</p>
    </footer>

    <style>
        /* Mantenha o seu CSS existente aqui */
        .app-header {
            background-color: var(--color-black);
            color: var(--color-white);
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .app-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            color: var(--color-yellow);
        }
        .app-header p {
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        .score-display {
            font-weight: 700;
            color: var(--color-yellow);
        }
        .app-header nav .btn {
            margin: 0 10px;
        }

        .quiz-selection {
            padding: 50px 20px;
            text-align: center;
        }
        .quiz-selection h2 {
            color: var(--color-red);
            margin-bottom: 40px;
            font-size: 2em;
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            justify-content: center;
        }
        .category-card {
            background-color: var(--color-white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .category-card h3 {
            color: var(--color-dark-gray);
            margin-bottom: 20px;
            font-size: 1.5em;
        }
    </style>
</body>
</html>