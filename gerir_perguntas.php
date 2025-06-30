<?php
session_start();
require_once 'db_connect.php';

// --- Proteção do Painel Administrativo ---
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}
// --- Fim Proteção ---

$pergunta_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pergunta_data = ['texto_pergunta' => '', 'categoria' => '', 'nivel_dificuldade' => 'Facil'];
$respostas_data = [['texto_resposta' => '', 'correta' => 0], ['texto_resposta' => '', 'correta' => 0], ['texto_resposta' => '', 'correta' => 0], ['texto_resposta' => '', 'correta' => 0]];
$page_title = "Adicionar Nova Pergunta";
$mensagem = '';

// Carregar dados da pergunta e respostas se for uma edição
if ($pergunta_id > 0) {
    $page_title = "Editar Pergunta";
    $stmt_p = $conn->prepare("SELECT texto_pergunta, categoria, nivel_dificuldade FROM perguntas WHERE id_pergunta = ?");
    $stmt_p->bind_param("i", $pergunta_id);
    $stmt_p->execute();
    $result_p = $stmt_p->get_result();
    if ($result_p->num_rows > 0) {
        $pergunta_data = $result_p->fetch_assoc();
    } else {
        $mensagem = "<p class='error'>Pergunta não encontrada.</p>";
        $pergunta_id = 0; // Reset para modo de adição
    }
    $stmt_p->close();

    $stmt_r = $conn->prepare("SELECT id_resposta, texto_resposta, correta FROM respostas WHERE id_pergunta = ? ORDER BY id_resposta ASC");
    $stmt_r->bind_param("i", $pergunta_id);
    $stmt_r->execute();
    $result_r = $stmt_r->get_result();
    $respostas_carregadas = [];
    while($row_r = $result_r->fetch_assoc()) {
        $respostas_carregadas[] = $row_r;
    }
    // Preenche as respostas carregadas, garantindo 4 campos de resposta
    for($i = 0; $i < 4; $i++) {
        if (isset($respostas_carregadas[$i])) {
            $respostas_data[$i] = $respostas_carregadas[$i];
        } else {
            $respostas_data[$i] = ['texto_resposta' => '', 'correta' => 0];
        }
    }
    $stmt_r->close();
}

// Processar formulário de submissão
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $texto_pergunta = $_POST['texto_pergunta'];
    $categoria = $_POST['categoria'];
    $nivel_dificuldade = $_POST['nivel_dificuldade'];
    $respostas_submetidas = $_POST['respostas']; // Array de textos de resposta
    $resposta_correta_indice = (int)$_POST['correta_indice']; // Índice da resposta correta (0 a 3)

    // Validação básica
    if (empty($texto_pergunta) || empty($categoria) || empty($nivel_dificuldade) || empty($respostas_submetidas)) {
        $mensagem = "<p class='error'>Por favor, preencha todos os campos da pergunta e respostas.</p>";
    } elseif ($resposta_correta_indice < 0 || $resposta_correta_indice > 3 || !isset($respostas_submetidas[$resposta_correta_indice])) {
        $mensagem = "<p class='error'>Selecione uma resposta correta válida.</p>";
    } else {
        $conn->begin_transaction(); // Inicia transação para garantir integridade

        try {
            if ($pergunta_id == 0) { // Adicionar nova pergunta
                $stmt_p = $conn->prepare("INSERT INTO perguntas (texto_pergunta, categoria, nivel_dificuldade) VALUES (?, ?, ?)");
                $stmt_p->bind_param("sss", $texto_pergunta, $categoria, $nivel_dificuldade);
                $stmt_p->execute();
                $nova_pergunta_id = $conn->insert_id;
                $stmt_p->close();
            } else { // Editar pergunta existente
                $stmt_p = $conn->prepare("UPDATE perguntas SET texto_pergunta = ?, categoria = ?, nivel_dificuldade = ? WHERE id_pergunta = ?");
                $stmt_p->bind_param("sssi", $texto_pergunta, $categoria, $nivel_dificuldade, $pergunta_id);
                $stmt_p->execute();
                $stmt_p->close();

                // Excluir respostas antigas para reinserir (simplifica a lógica de atualização)
                $stmt_delete_r = $conn->prepare("DELETE FROM respostas WHERE id_pergunta = ?");
                $stmt_delete_r->bind_param("i", $pergunta_id);
                $stmt_delete_r->execute();
                $stmt_delete_r->close();
                $nova_pergunta_id = $pergunta_id;
            }

            // Inserir/Atualizar respostas
            $stmt_r = $conn->prepare("INSERT INTO respostas (id_pergunta, texto_resposta, correta) VALUES (?, ?, ?)");
            for ($i = 0; $i < 4; $i++) {
                $is_correta = ($i == $resposta_correta_indice) ? 1 : 0;
                $stmt_r->bind_param("isi", $nova_pergunta_id, $respostas_submetidas[$i], $is_correta);
                $stmt_r->execute();
            }
            $stmt_r->close();

            $conn->commit(); // Confirma a transação
            header("Location: admin_dashboard.php?msg=" . urlencode("Pergunta salva com sucesso!"));
            exit();

        } catch (mysqli_sql_exception $e) {
            $conn->rollback(); // Reverte em caso de erro
            $mensagem = "<p class='error'>Erro ao salvar pergunta: " . $e->getMessage() . "</p>";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Quiz Conheça Angola</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #EF0000 50%, #000000 50%);
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
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 700px;
            color: #333;
            text-align: left;
        }
        h1 {
            font-size: 2.2em;
            margin-bottom: 25px;
            color: #EF0000;
            text-align: center;
        }
        .message {
            margin-bottom: 15px;
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
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        .radio-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }
        .radio-group input[type="radio"] {
            margin-right: 5px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            border: 1px solid #218838;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: 1px solid #5a6268;
            margin-left: 10px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $page_title; ?></h1>

        <?php if ($mensagem): ?>
            <p class="message <?php echo strpos($mensagem, 'sucesso') !== false ? 'success' : 'error'; ?>">
                <?php echo $mensagem; ?>
            </p>
        <?php endif; ?>

        <form action="gerir_perguntas.php<?php echo $pergunta_id > 0 ? '?id=' . $pergunta_id : ''; ?>" method="POST">
            <div class="form-group">
                <label for="texto_pergunta">Texto da Pergunta:</label>
                <textarea id="texto_pergunta" name="texto_pergunta" required><?php echo htmlspecialchars($pergunta_data['texto_pergunta']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="categoria">Categoria:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione uma categoria</option>
                    <option value="Historia" <?php echo ($pergunta_data['categoria'] == 'Historia') ? 'selected' : ''; ?>>História</option>
                    <option value="Geografia" <?php echo ($pergunta_data['categoria'] == 'Geografia') ? 'selected' : ''; ?>>Geografia</option>
                    <option value="Cultura" <?php echo ($pergunta_data['categoria'] == 'Cultura') ? 'selected' : ''; ?>>Cultura</option>
                    <option value="Curiosidades" <?php echo ($pergunta_data['categoria'] == 'Curiosidades') ? 'selected' : ''; ?>>Curiosidades</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nivel_dificuldade">Nível de Dificuldade:</label>
                <select id="nivel_dificuldade" name="nivel_dificuldade" required>
                    <option value="Facil" <?php echo ($pergunta_data['nivel_dificuldade'] == 'Facil') ? 'selected' : ''; ?>>Fácil</option>
                    <option value="Medio" <?php echo ($pergunta_data['nivel_dificuldade'] == 'Medio') ? 'selected' : ''; ?>>Médio</option>
                    <option value="Dificil" <?php echo ($pergunta_data['nivel_dificuldade'] == 'Dificil') ? 'selected' : ''; ?>>Difícil</option>
                </select>
            </div>

            <div class="form-group">
                <label>Respostas (Marque a correta):</label>
                <?php for($i = 0; $i < 4; $i++): ?>
                    <div class="radio-group">
                        <input type="radio" id="resposta_<?php echo $i; ?>" name="correta_indice" value="<?php echo $i; ?>" <?php echo ($respostas_data[$i]['correta'] == 1) ? 'checked' : ''; ?> required>
                        <input type="text" name="respostas[]" placeholder="Texto da Resposta <?php echo $i + 1; ?>" value="<?php echo htmlspecialchars($respostas_data[$i]['texto_resposta']); ?>" required>
                    </div>
                <?php endfor; ?>
            </div>

            <button type="submit" class="btn btn-submit">Salvar Pergunta</button>
            <a href="admin_dashboard.php" class="btn btn-back">Voltar</a>
        </form>
    </div>
</body>
</html>