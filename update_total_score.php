<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado.']);
    exit;
}

$score_earned = isset($_POST['score_earned']) ? intval($_POST['score_earned']) : 0;
$user_id = $_SESSION['usuario_id'];

// Verificar se já existe um registro para o usuário na tabela pontuacoes
$stmt_check = $conn->prepare("SELECT pontuacao_total FROM pontuacoes WHERE usuario_id = ?");
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$stmt_check->store_result();

$new_total_score = $score_earned; // Pontuação inicial se for um novo registro

if ($stmt_check->num_rows > 0) {
    // Se existe, atualizar a pontuação
    $stmt_check->bind_result($current_total_score);
    $stmt_check->fetch();
    $new_total_score = $current_total_score + $score_earned;

    $stmt_update = $conn->prepare("UPDATE pontuacoes SET pontuacao_total = ? WHERE usuario_id = ?");
    $stmt_update->bind_param("ii", $new_total_score, $user_id);
    $success = $stmt_update->execute();
    $stmt_update->close();
} else {
    // Se não existe, inserir um novo registro
    $stmt_insert = $conn->prepare("INSERT INTO pontuacoes (usuario_id, pontuacao_total) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $score_earned);
    $success = $stmt_insert->execute();
    $stmt_insert->close();
}
$stmt_check->close();

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Pontuação atualizada com sucesso!',
        'new_total_score' => $new_total_score
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar pontuação: ' . $conn->error]);
}

$conn->close();

// Limpar as perguntas da sessão para que uma nova rodada comece "do zero" na próxima vez
unset($_SESSION['quiz_questions_ids']);
unset($_SESSION['current_quiz_score']);

?>