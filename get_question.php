<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Não autenticado.']);
    exit;
}

$categoria_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Se for o início de um novo quiz, ou se as perguntas anteriores já acabaram
if (!isset($_SESSION['quiz_questions_ids'])) {
    $_SESSION['quiz_questions_ids'] = [];
    $_SESSION['current_quiz_score'] = 0; // Reinicia a pontuação da rodada
}

// Construir a cláusula WHERE para filtrar por categoria e excluir perguntas já mostradas
$where_clauses = ["1=1"];
$params = [];
$param_types = "";

if ($categoria_id > 0) {
    $where_clauses[] = "p.categoria_id = ?";
    $params[] = $categoria_id;
    $param_types .= "i";
}

if (!empty($_SESSION['quiz_questions_ids'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['quiz_questions_ids']), '?'));
    $where_clauses[] = "p.id NOT IN ($placeholders)";
    foreach ($_SESSION['quiz_questions_ids'] as $id) {
        $params[] = $id;
        $param_types .= "i";
    }
}

$sql = "SELECT p.id, p.pergunta_texto, p.tipo_pergunta, p.caminho_imagem, p.caminho_audio
        FROM perguntas p
        WHERE " . implode(' AND ', $where_clauses) . "
        ORDER BY RAND() LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$question = $result->fetch_assoc();

if ($question) {
    $_SESSION['quiz_questions_ids'][] = $question['id']; // Adiciona a pergunta à lista de perguntas já mostradas

    $response_data = [
        'id' => $question['id'],
        'pergunta_texto' => htmlspecialchars($question['pergunta_texto']), // Alterado para pergunta_texto
        'tipo_pergunta' => $question['tipo_pergunta'],
        'caminho_imagem' => $question['caminho_imagem'],
        'caminho_audio' => $question['caminho_audio']
    ];

    // Sempre buscar opções da tabela opcoes_resposta
    $stmt_opcoes = $conn->prepare("SELECT id, opcao_texto FROM opcoes_resposta WHERE pergunta_id = ? ORDER BY RAND()"); // Alterado para opcao_texto
    $stmt_opcoes->bind_param("i", $question['id']);
    $stmt_opcoes->execute();
    $opcoes_result = $stmt_opcoes->get_result();
    $opcoes = [];
    while ($opcao = $opcoes_result->fetch_assoc()) {
        $opcoes[] = ['id' => $opcao['id'], 'opcao_texto' => htmlspecialchars($opcao['opcao_texto'])]; // Alterado para opcao_texto
    }
    $response_data['opcoes'] = $opcoes;
    $stmt_opcoes->close();

    echo json_encode(['question' => $response_data]);
} else {
    echo json_encode(['message' => 'Todas as perguntas foram respondidas ou não há perguntas.']);
}

$stmt->close();
$conn->close();
?>