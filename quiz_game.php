<?php
session_start(); // Inicia a sessão

// --- Lógica de Proteção da Página (garante que só utilizadores logados acedem) ---
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php"); // Redireciona para a página de login se não estiver logado
    exit();
}
// --- Fim da Lógica de Proteção ---

require_once 'db_connect.php'; // Inclui o ficheiro de conexão com a base de dados

// Inicializa a pontuação e o controlo de perguntas na sessão, se ainda não existirem
if (!isset($_SESSION['pontuacao'])) {
    $_SESSION['pontuacao'] = 0;
    $_SESSION['perguntas_respondidas'] = []; // Armazena os IDs das perguntas já mostradas nesta sessão de jogo
}

$mensagem_feedback = ''; // Para mensagens de "Correto!" ou "Incorreto!"

// --- Lógica para Processar a Resposta do Utilizador (quando o formulário é submetido) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pergunta']) && isset($_POST['resposta'])) {
    $id_pergunta_respondida = (int)$_POST['id_pergunta'];
    $id_resposta_selecionada = (int)$_POST['resposta'];

    // Buscar a resposta correta para a pergunta submetida
    $stmt = $conn->prepare("SELECT id_resposta FROM respostas WHERE id_pergunta = ? AND correta = 1 LIMIT 1");
    $stmt->bind_param("i", $id_pergunta_respondida);
    $stmt->execute();
    $result = $stmt->get_result();
    $resposta_correta_db = $result->fetch_assoc();
/*
    if ($resposta_correta_db) {
        if ($id_resposta_selecionada == $resposta_correta_db['id_resposta']) {
            $_SESSION['pontuacao'] += 10; // Adiciona 10 pontos por resposta correta
            $mensagem_feedback = "<p class='feedback correta'>Correto! Pontuação: " . $_SESSION['pontuacao'] . "</p>";
        } else {
            // Se incorreto, podemos também mostrar qual era a resposta certa
            $stmt_texto_correto = $conn->prepare("SELECT texto_resposta FROM respostas WHERE id_resposta = ? LIMIT 1");
            $stmt_texto_correto->bind_param("i", $resposta_correta_db['id_resposta']);
            $stmt_texto_correto->execute();
            $result_texto_correto = $stmt_texto_correto->get_result();
            $texto_resposta_correta = $result_texto_correto->fetch_assoc()['texto_resposta'];
            $stmt_texto_correto->close();

            $mensagem_feedback = "<p class='feedback incorreta'>Incorreto. A resposta correta era: **" . htmlspecialchars($texto_resposta_correta) . "**.</p>";
            $mensagem_feedback .= "<p class='feedback incorreta'>Pontuação: " . $_SESSION['pontuacao'] . "</p>";
        }
    } else {
        $mensagem_feedback = "<p class='feedback incorreta'>Erro ao verificar a resposta. Pontuação: " . $_SESSION['pontuacao'] . "</p>";
    }
    $stmt->close();
    */
    if ($resposta_correta_db) {
    if ($id_resposta_selecionada == $resposta_correta_db['id_resposta']) {
        $_SESSION['pontuacao'] += 10; // Adiciona 10 pontos na sessão

        // --- NOVO: Atualizar a pontuação no banco de dados ---
        $id_usuario_atual = $_SESSION['id_usuario'];
        $nova_pontuacao_total = $_SESSION['pontuacao']; // Assumimos que pontuação na sessão já é a total acumulada

        $stmt_update_score = $conn->prepare("UPDATE usuarios SET pontuacao_total = ? WHERE id_usuario = ?");
        $stmt_update_score->bind_param("ii", $nova_pontuacao_total, $id_usuario_atual);
        $stmt_update_score->execute();
        $stmt_update_score->close();
        // --- FIM NOVO ---

        $mensagem_feedback = "<p class='feedback correta'>Correto! Pontuação: " . $_SESSION['pontuacao'] . "</p>";
    } else {
        // Se incorreto, podemos também mostrar qual era a resposta certa
        $stmt_texto_correto = $conn->prepare("SELECT texto_resposta FROM respostas WHERE id_resposta = ? LIMIT 1");
        $stmt_texto_correto->bind_param("i", $resposta_correta_db['id_resposta']);
        $stmt_texto_correto->execute();
        $result_texto_correto = $stmt_texto_correto->get_result();
        $texto_resposta_correta = $result_texto_correto->fetch_assoc()['texto_resposta'];
        $stmt_texto_correto->close();

        $mensagem_feedback = "<p class='feedback incorreta'>Incorreto. A resposta correta era: **" . htmlspecialchars($texto_resposta_correta) . "**.</p>";
        $mensagem_feedback .= "<p class='feedback incorreta'>Pontuação: " . $_SESSION['pontuacao'] . "</p>";
    }
} else {
    $mensagem_feedback = "<p class='feedback incorreta'>Erro ao verificar a resposta. Pontuação: " . $_SESSION['pontuacao'] . "</p>";
}
$stmt->close();
    // Adiciona a pergunta respondida à lista para não a repetir nesta sessão de jogo
    $_SESSION['perguntas_respondidas'][] = $id_pergunta_respondida;
}

// --- Lógica para Obter a Próxima Pergunta ---
$proxima_pergunta = null;
$ids_respondidos_string = ''; // String de IDs para a cláusula NOT IN do SQL

// Prepara a string de IDs de perguntas já respondidas
if (!empty($_SESSION['perguntas_respondidas'])) {
    // Garante que todos os IDs são inteiros para segurança no SQL
    $safe_ids = array_map('intval', $_SESSION['perguntas_respondidas']);
    $ids_respondidos_string = implode(',', $safe_ids);
}

// Primeiro, tenta obter uma pergunta que AINDA NÃO FOI RESPONDIDA
$sql_pergunta = "SELECT id_pergunta, texto_pergunta FROM perguntas";
if (!empty($ids_respondidos_string)) {
    $sql_pergunta .= " WHERE id_pergunta NOT IN ($ids_respondidos_string)";
}
$sql_pergunta .= " ORDER BY RAND() LIMIT 1"; // Ordena aleatoriamente para variar as perguntas

$result_pergunta = $conn->query($sql_pergunta);

if ($result_pergunta && $result_pergunta->num_rows > 0) {
    $proxima_pergunta = $result_pergunta->fetch_assoc();
    $id_pergunta_atual = $proxima_pergunta['id_pergunta'];

    // Obter as respostas associadas à pergunta atual
    $stmt_respostas = $conn->prepare("SELECT id_resposta, texto_resposta FROM respostas WHERE id_pergunta = ?");
    $stmt_respostas->bind_param("i", $id_pergunta_atual);
    $stmt_respostas->execute();
    $result_respostas = $stmt_respostas->get_result();
    
    $respostas_para_exibir = [];
    while($row_resposta = $result_respostas->fetch_assoc()) {
        $respostas_para_exibir[] = $row_resposta;
    }
    shuffle($respostas_para_exibir); // Mistura a ordem das opções de resposta
    $stmt_respostas->close();

} else {
    // Se não há mais perguntas NÃO RESPONDIDAS no DB
    $total_perguntas_db = $conn->query("SELECT COUNT(*) FROM perguntas")->fetch_row()[0];

    if ($total_perguntas_db > 0) { // Se há perguntas no DB mas todas foram respondidas
        $mensagem_feedback .= "<p class='feedback'>Você respondeu a todas as perguntas disponíveis! O quiz será reiniciado com novas perguntas.</p>";
        $_SESSION['perguntas_respondidas'] = []; // Reinicia a lista de perguntas respondidas
        // Tenta buscar uma pergunta novamente (agora que a lista foi resetada)
        header("Location: quiz_game.php"); // Redireciona para recarregar com uma nova pergunta
        exit();
    } else { // Se não há perguntas cadastradas no DB
        $proxima_pergunta = null;
        $mensagem_feedback = "<p class='feedback'>Não há perguntas disponíveis no quiz. Pontuação final: **" . $_SESSION['pontuacao'] . "**.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogar Quiz - Quiz Conheça Angola</title>
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
        .quiz-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
            color: #333;
            text-align: left;
            position: relative; /* Para a pontuação */
        }
        h1 {
            font-size: 2.2em;
            margin-bottom: 25px;
            color: #EF0000; /* Vermelho */
            text-align: center;
        }
        .pergunta-texto {
            font-size: 1.5em;
            margin-bottom: 30px;
            color: #555;
            text-align: center;
        }
        .respostas-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .resposta-btn {
            background-color: #FFCD00; /* Amarelo */
            color: #000000; /* Preto */
            padding: 15px;
            border: 2px solid #CCA300;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .resposta-btn:hover {
            background-color: #CCA300;
            transform: translateY(-2px);
        }
        .resposta-btn.selected {
            background-color: #EF0000; /* Vermelho quando selecionado */
            color: white;
            border-color: #A00000;
        }
        .feedback {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .feedback.correta {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .feedback.incorreta {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .submit-btn {
            background-color: #000000; /* Preto */
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #333333;
        }
        .current-score {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #FFCD00;
            color: #000000;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .nav-links {
            text-align: center;
            margin-top: 25px;
        }
        .nav-links a {
            color: #EF0000; /* Vermelho */
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: #A00000;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="current-score">Pontuação: <?php echo $_SESSION['pontuacao']; ?></div>
        <h1>Quiz Conheça Angola</h1>

        <?php echo $mensagem_feedback; // Exibe feedback da resposta anterior ?>

        <?php if ($proxima_pergunta): ?>
            <p class="pergunta-texto"><?php echo htmlspecialchars($proxima_pergunta['texto_pergunta']); ?></p>

            <form action="quiz_game.php" method="POST" id="quizForm">
                <input type="hidden" name="id_pergunta" value="<?php echo $proxima_pergunta['id_pergunta']; ?>">
                <div class="respostas-form" id="respostas-container">
                    <?php foreach ($respostas_para_exibir as $resposta): ?>
                        <button type="button" class="resposta-btn" data-id-resposta="<?php echo $resposta['id_resposta']; ?>">
                            <?php echo htmlspecialchars($resposta['texto_resposta']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="resposta" id="selected-answer">
                <button type="submit" class="submit-btn" id="submit-btn" disabled>Confirmar Resposta</button>
            </form>
        <?php else: ?>
            <p class="pergunta-texto">O quiz terminou! Não há mais perguntas disponíveis. Pontuação final: **<?php echo $_SESSION['pontuacao']; ?>**.</p>
            <div class="nav-links">
                <a href="dashboard.php">Voltar ao Painel</a> |
                <a href="logout.php">Sair</a>
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <?php if ($proxima_pergunta): ?>
                <a href="dashboard.php">Voltar ao Painel</a> |
            <?php endif; ?>
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <script>
        const respostaBotoes = document.querySelectorAll('.resposta-btn');
        const submitBtn = document.getElementById('submit-btn');
        const selectedAnswerInput = document.getElementById('selected-answer');

        // Ativa o botão de submeter e marca a resposta selecionada
        respostaBotoes.forEach(botao => {
            botao.addEventListener('click', function() {
                // Remove a seleção de todos os botões
                respostaBotoes.forEach(btn => btn.classList.remove('selected'));
                // Adiciona a seleção ao botão clicado
                this.classList.add('selected');
                // Atribui o ID da resposta ao campo hidden que será submetido
                selectedAnswerInput.value = this.dataset.idResposta;
                // Ativa o botão de submeter
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>