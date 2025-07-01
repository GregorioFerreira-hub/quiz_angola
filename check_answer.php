<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

$user_id = $_SESSION['usuario_id'];
$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
$selected_option_id = isset($_POST['selected_option_id']) ? intval($_POST['selected_option_id']) : null; // ID da opção selecionada
$tipo_pergunta = $_POST['tipo_pergunta'];

$is_correct = false;
$correct_answer_text = '';
$correct_answer_id = null;
$resposta_dada_vf = null; // Para o historico_quiz

if ($question_id > 0) {
    // 1. Obter a resposta correta para a pergunta
    $stmt_correct = $conn->prepare("SELECT orp.id, orp.opcao_texto, p.tipo_pergunta FROM opcoes_resposta orp JOIN perguntas p ON orp.pergunta_id = p.id WHERE orp.pergunta_id = ? AND orp.correta = 1");
    $stmt_correct->bind_param("i", $question_id);
    $stmt_correct->execute();
    $result_correct = $stmt_correct->get_result();
    $correct_option = $result_correct->fetch_assoc();
    $stmt_correct->close();

    if ($correct_option) {
        $correct_answer_id = $correct_option['id'];
        $correct_answer_text = htmlspecialchars($correct_option['opcao_texto']);

        // 2. Verificar se a opção selecionada é a correta
        if ($selected_option_id == $correct_answer_id) {
            $is_correct = true;
        }

        // 3. Determinar resposta_dada_vf para historico_quiz se for V/F
        if ($tipo_pergunta === 'verdadeiro_falso') {
            $stmt_selected_option = $conn->prepare("SELECT opcao_texto FROM opcoes_resposta WHERE id = ?");
            $stmt_selected_option->bind_param("i", $selected_option_id);
            $stmt_selected_option->execute();
            $result_selected = $stmt_selected_option->get_result();
            $selected_option_data = $result_selected->fetch_assoc();
            $stmt_selected_option->close();

            if ($selected_option_data) {
                $resposta_dada_vf = ($selected_option_data['opcao_texto'] === 'Verdadeiro');
            }
        }
    }

    // 4. Registrar no historico_quiz
    $stmt_history = $conn->prepare("INSERT INTO historico_quiz (usuario_id, pergunta_id, resposta_dada_id, resposta_dada_vf, correta) VALUES (?, ?, ?, ?, ?)");
    $stmt_history->bind_param("iiisi", $user_id, $question_id, $selected_option_id, $resposta_dada_vf, $is_correct);
    $stmt_history->execute();
    $stmt_history->close();

    // 5. Se a resposta estiver correta, incrementa a pontuação da sessão atual do quiz
    if ($is_correct) {
        $_SESSION['current_quiz_score'] = ($_SESSION['current_quiz_score'] ?? 0) + 10; // Exemplo: 10 pontos por pergunta
    }

    echo json_encode([
        'is_correct' => $is_correct,
        'correct_answer_text' => $correct_answer_text,
        'correct_answer_id' => $correct_answer_id,
        'current_score' => $_SESSION['current_quiz_score'] ?? 0
    ]);

} else {
    echo json_encode(['error' => 'ID da pergunta inválido.']);
}
$conn->close();
?>