<?php
session_start();
require_once 'config.php';

// Redirecionar se o usuário não estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("location: login.php");
    exit;
}

$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;
$categoria_nome = "Todas as Categorias";

if ($categoria_id > 0) {
    $stmt_cat = $conn->prepare("SELECT nome_categoria FROM categorias WHERE id = ?");
    $stmt_cat->bind_param("i", $categoria_id);
    $stmt_cat->execute();
    $stmt_cat->bind_result($cat_name);
    if ($stmt_cat->fetch()) {
        $categoria_nome = htmlspecialchars($cat_name);
    }
    $stmt_cat->close();
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?php echo $categoria_nome; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <header class="app-header">
        <div class="container">
            <h1>Quiz: <?php echo $categoria_nome; ?></h1>
            <p>Pontuação: <span id="current-score">0</span></p>
            <nav>
                <a href="dashboard.php" class="btn ghost">Voltar ao Dashboard</a>
                <a href="logout.php" class="btn ghost">Sair</a>
            </nav>
        </div>
    </header>

    <main class="container quiz-container">
        <div id="quiz-area">
            <div class="question-area">
                <h2 id="question-text">Carregando pergunta...</h2>
                <img id="question-image" src="" alt="Imagem da pergunta" style="max-width:100%; height:auto; margin-top:15px; display:none;">
                <audio id="question-audio" controls style="width:100%; margin-top:15px; display:none;">
                    <source src="" type="audio/mpeg">
                    Seu navegador não suporta o elemento de áudio.
                </audio>
            </div>
            <ul class="options-list" id="options-list">
                <!-- Opções serão carregadas via JavaScript -->
            </ul>
            <div class="quiz-navigation">
                <button id="next-question-btn" disabled>Próxima Pergunta</button>
            </div>
            <div id="feedback-area" style="margin-top: 20px; font-weight: bold;"></div>
        </div>
        <div id="quiz-results" style="display:none; text-align:center;">
            <h2>Quiz Finalizado!</h2>
            <p>Sua pontuação nesta rodada: <span id="final-score">0</span></p>
            <p>Sua pontuação total acumulada: <span id="total-accumulated-score">0</span></p>
            <a href="quiz.php?categoria_id=<?php echo $categoria_id; ?>" class="btn primary">Jogar Novamente</a>
            <a href="dashboard.php" class="btn secondary">Voltar ao Dashboard</a>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Quiz Conheça Angola. Todos os direitos reservados.</p>
    </footer>

    <script>
        let currentQuestionIndex = 0;
        let questions = [];
        let score = 0;
        let answeredQuestionsCount = 0;
        const maxQuestions = 10; // Número de perguntas por rodada

        function loadQuestion() {
            if (answeredQuestionsCount >= maxQuestions) {
                showResults();
                return;
            }

            const categoryId = <?php echo $categoria_id; ?>;
            $.ajax({
                url: 'get_question.php',
                type: 'GET',
                data: { current_question_index: currentQuestionIndex, category_id: categoryId },
                dataType: 'json',
                success: function(response) {
                    if (response.question) {
                        questions.push(response.question); // Armazenar a pergunta atual
                        displayQuestion(response.question);
                        $('#next-question-btn').prop('disabled', true); // Desabilitar até responder
                        $('#feedback-area').text('');
                        $('.option-item').removeClass('correct incorrect'); // Remover classes de feedback
                    } else {
                        // Não há mais perguntas ou erro
                        showResults();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erro ao carregar pergunta:", status, error);
                    $('#question-text').text('Erro ao carregar pergunta. Tente novamente.');
                }
            });
        }

        function displayQuestion(questionData) {
            $('#question-text').text(questionData.pergunta_texto); // Alterado para pergunta_texto
            $('#options-list').empty();

            // Exibir imagem ou áudio se existirem
            $('#question-image').hide().attr('src', '');
            $('#question-audio').hide().attr('src', '');

            if (questionData.caminho_imagem) {
                $('#question-image').attr('src', 'uploads/images/' + questionData.caminho_imagem).show();
            }
            if (questionData.caminho_audio) {
                $('#question-audio source').attr('src', 'uploads/audio/' + questionData.caminho_audio);
                $('#question-audio')[0].load(); // Recarrega o elemento de áudio
                $('#question-audio').show();
            }

            // As opções agora vêm sempre de `opcoes` para ambos os tipos
            questionData.opcoes.forEach(function(opcao) {
                const listItem = $('<li class="option-item" data-id="' + opcao.id + '">' + opcao.opcao_texto + '</li>'); // Alterado para opcao_texto
                listItem.on('click', function() {
                    // Passar o ID da opção selecionada e o tipo de pergunta
                    checkAnswer(opcao.id, questionData.id, questionData.tipo_pergunta, listItem);
                });
                $('#options-list').append(listItem);
            });
            // Habilitar cliques apenas uma vez
            $('.option-item').off('click').on('click', handleOptionClick);
        }

        function handleOptionClick() {
            // Desabilitar todas as opções após um clique
            $('.option-item').off('click');
            const selectedOptionElement = $(this); // O elemento clicado

            const questionData = questions[answeredQuestionsCount]; // A pergunta atual
            const selectedOptionId = selectedOptionElement.data('id');
            checkAnswer(selectedOptionId, questionData.id, questionData.tipo_pergunta, selectedOptionElement);
        }


        function checkAnswer(selectedOptionId, questionId, tipo_pergunta, selectedOptionElement) {
            $.ajax({
                url: 'check_answer.php',
                type: 'POST',
                data: {
                    question_id: questionId,
                    selected_option_id: selectedOptionId, // Passar o ID da opção selecionada
                    tipo_pergunta: tipo_pergunta
                },
                dataType: 'json',
                success: function(response) {
                    answeredQuestionsCount++; // Incrementa o contador de perguntas respondidas

                    if (response.is_correct) {
                        score += 10; // Adicionar pontos (pode ajustar o valor)
                        $('#feedback-area').text('Correto!').css('color', 'green');
                        selectedOptionElement.addClass('correct');
                    } else {
                        $('#feedback-area').text('Incorreto! A resposta correta era: ' + response.correct_answer_text).css('color', 'red');
                        selectedOptionElement.addClass('incorrect');

                        // Mostrar a opção correta
                        $('.option-item[data-id="' + response.correct_answer_id + '"]').addClass('correct');
                    }
                    $('#current-score').text(score);
                    $('#next-question-btn').prop('disabled', false); // Habilitar botão "Próxima"

                    // Desabilitar cliques em outras opções após responder
                    $('.option-item').off('click');

                },
                error: function(xhr, status, error) {
                    console.error("Erro ao verificar resposta:", status, error);
                    $('#feedback-area').text('Erro ao verificar resposta.').css('color', 'red');
                }
            });
        }

        $('#next-question-btn').on('click', function() {
            currentQuestionIndex++;
            loadQuestion();
        });

        function showResults() {
            $('#quiz-area').hide();
            $('#quiz-results').show();
            $('#final-score').text(score);

            // Atualizar pontuação total no banco de dados e exibir
            $.ajax({
                url: 'update_total_score.php',
                type: 'POST',
                data: { score_earned: score },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#total-accumulated-score').text(response.new_total_score);
                    } else {
                        $('#total-accumulated-score').text('Erro ao atualizar.').css('color', 'red');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erro ao atualizar pontuação total:", status, error);
                    $('#total-accumulated-score').text('Erro de comunicação.').css('color', 'red');
                }
            });
        }

        $(document).ready(function() {
            loadQuestion(); // Carrega a primeira pergunta ao iniciar
        });
    </script>
</body>
</html>