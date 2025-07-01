<?php
session_start();
require_once '../config.php';
include 'admin_header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$message = '';

// Diretórios para upload de mídia
$upload_dir_images = '../uploads/images/';
$upload_dir_audio = '../uploads/audio/';

if (!is_dir($upload_dir_images)) mkdir($upload_dir_images, 0777, true);
if (!is_dir($upload_dir_audio)) mkdir($upload_dir_audio, 0777, true);


// Lógica para Adicionar/Editar Pergunta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_edit_question') {
        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $pergunta_texto = trim($_POST['pergunta_texto']); // Alterado para pergunta_texto
        $tipo_pergunta = $_POST['tipo_pergunta'];
        $categoria_id = intval($_POST['categoria_id']);
        // $nivel_dificuldade = $_POST['nivel_dificuldade']; // Removido

        $caminho_imagem = '';
        $caminho_audio = '';

        // Processar upload de imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
            $image_name = uniqid() . '_' . basename($_FILES['imagem']['name']);
            $target_file = $upload_dir_images . $image_name;
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $target_file)) {
                $caminho_imagem = $image_name;
            } else {
                $message = '<div class="message error">Erro ao fazer upload da imagem.</div>';
            }
        } elseif ($question_id > 0 && isset($_POST['current_image']) && !empty($_POST['current_image'])) {
             $caminho_imagem = $_POST['current_image']; // Manter imagem existente se não houver nova
        }

        // Processar upload de áudio
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] == UPLOAD_ERR_OK) {
            $audio_name = uniqid() . '_' . basename($_FILES['audio']['name']);
            $target_file = $upload_dir_audio . $audio_name;
            if (move_uploaded_file($_FILES['audio']['tmp_name'], $target_file)) {
                $caminho_audio = $audio_name;
            } else {
                $message .= '<div class="message error">Erro ao fazer upload do áudio.</div>';
            }
        } elseif ($question_id > 0 && isset($_POST['current_audio']) && !empty($_POST['current_audio'])) {
             $caminho_audio = $_POST['current_audio']; // Manter áudio existente se não houver novo
        }

        if (empty($pergunta_texto) || $categoria_id == 0) {
            $message = '<div class="message error">Por favor, preencha o texto da pergunta e selecione uma categoria.</div>';
        } else {
            if ($question_id == 0) { // Adicionar nova pergunta
                $stmt = $conn->prepare("INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id, caminho_imagem, caminho_audio) VALUES (?, ?, ?, ?, ?)"); // Removido nivel_dificuldade
                $stmt->bind_param("ssiss", $pergunta_texto, $tipo_pergunta, $categoria_id, $caminho_imagem, $caminho_audio);
                if ($stmt->execute()) {
                    $new_question_id = $stmt->insert_id;
                    // Lógica para opções de resposta (múltipla escolha ou V/F)
                    if ($tipo_pergunta == 'multipla_escolha') {
                        $opcoes = $_POST['opcoes'];
                        $correta_index = intval($_POST['correta']); // Índice da opção correta
                        foreach ($opcoes as $index => $opcao_texto) {
                            $correta = ($index == $correta_index) ? 1 : 0; // Alterado para 'correta'
                            $stmt_opcao = $conn->prepare("INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES (?, ?, ?)"); // Alterado para opcao_texto, correta
                            $stmt_opcao->bind_param("isi", $new_question_id, $opcao_texto, $correta);
                            $stmt_opcao->execute();
                            $stmt_opcao->close();
                        }
                    } elseif ($tipo_pergunta == 'verdadeiro_falso') {
                        $resposta_vf = intval($_POST['resposta_vf_correta']); // 1 para Verdadeiro, 0 para Falso
                        // Inserir as duas opções "Verdadeiro" e "Falso"
                        $stmt_vf_true = $conn->prepare("INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES (?, ?, ?)");
                        $stmt_vf_true->bind_param("isi", $new_question_id, $v = "Verdadeiro", $t = ($resposta_vf == 1));
                        $stmt_vf_true->execute();
                        $stmt_vf_true->close();

                        $stmt_vf_false = $conn->prepare("INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES (?, ?, ?)");
                        $stmt_vf_false->bind_param("isi", $new_question_id, $f = "Falso", $t = ($resposta_vf == 0));
                        $stmt_vf_false->execute();
                        $stmt_vf_false->close();
                    }
                    $message = '<div class="message success">Pergunta adicionada com sucesso!</div>';
                } else {
                    $message = '<div class="message error">Erro ao adicionar pergunta: ' . $conn->error . '</div>';
                }
            } else { // Editar pergunta existente
                $sql_update = "UPDATE perguntas SET pergunta_texto = ?, tipo_pergunta = ?, categoria_id = ?, caminho_imagem = ?, caminho_audio = ? WHERE id = ?"; // Removido nivel_dificuldade
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("ssissi", $pergunta_texto, $tipo_pergunta, $categoria_id, $caminho_imagem, $caminho_audio, $question_id);

                if ($stmt->execute()) {
                    // Remover opções antigas para múltiplos escolha ou V/F
                    $conn->query("DELETE FROM opcoes_resposta WHERE pergunta_id = $question_id");

                    if ($tipo_pergunta == 'multipla_escolha') {
                        $opcoes = $_POST['opcoes'];
                        $correta_index = intval($_POST['correta']);
                        foreach ($opcoes as $index => $opcao_texto) {
                            $correta = ($index == $correta_index) ? 1 : 0;
                            $stmt_opcao = $conn->prepare("INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES (?, ?, ?)");
                            $stmt_opcao->bind_param("isi", $question_id, $opcao_texto, $correta);
                            $stmt_opcao->execute();
                            $stmt_opcao->close();
                        }
                    } elseif ($tipo_pergunta == 'verdadeiro_falso') {
                        $resposta_vf = intval($_POST['resposta_vf_correta']);
                        // Inserir as duas opções "Verdadeiro" e "Falso"
                        $stmt_vf_true = $conn->prepare("INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES (?, ?, ?)");
                        $stmt_vf_true->bind_param("isi", $question_id, $v = "Verdadeiro", $t = ($resposta_vf == 1));
                        $stmt_vf_true->execute();
                        $stmt_vf_true->close();

                        $stmt_vf_false = $conn->prepare("INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES (?, ?, ?)");
                        $stmt_vf_false->bind_param("isi", $question_id, $f = "Falso", $t = ($resposta_vf == 0));
                        $stmt_vf_false->execute();
                        $stmt_vf_false->close();
                    }
                    $message = '<div class="message success">Pergunta atualizada com sucesso!</div>';
                } else {
                    $message = '<div class="message error">Erro ao atualizar pergunta: ' . $conn->error . '</div>';
                }
            }
            $stmt->close();
        }
    }
}

// Lógica para Deletar Pergunta
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Automaticamente deleta opções de resposta devido ao ON DELETE CASCADE
    $stmt = $conn->prepare("DELETE FROM perguntas WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = '<div class="message success">Pergunta excluída com sucesso!</div>';
    } else {
        $message = '<div class="message error">Erro ao excluir pergunta: ' . $conn->error . '</div>';
    }
    $stmt->close();
}

// Lógica para carregar pergunta para edição
$edit_question = null;
$edit_options = [];
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT id, pergunta_texto, tipo_pergunta, categoria_id, caminho_imagem, caminho_audio FROM perguntas WHERE id = ?"); // Removido nivel_dificuldade
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_question = $result->fetch_assoc();
    $stmt->close();

    if ($edit_question) {
        $stmt_opcoes = $conn->prepare("SELECT id, opcao_texto, correta FROM opcoes_resposta WHERE pergunta_id = ? ORDER BY id ASC"); // Alterado para opcao_texto, correta
        $stmt_opcoes->bind_param("i", $edit_id);
        $stmt_opcoes->execute();
        $opcoes_result = $stmt_opcoes->get_result();
        while ($row = $opcoes_result->fetch_assoc()) {
            $edit_options[] = $row;
        }
        $stmt_opcoes->close();
    }
}

// Obter todas as categorias para o dropdown
$categories_query = $conn->query("SELECT id, nome_categoria FROM categorias ORDER BY nome_categoria ASC");

// Obter todas as perguntas para exibição
$questions_result = $conn->query("SELECT p.id, p.pergunta_texto, p.tipo_pergunta, c.nome_categoria FROM perguntas p JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC"); // Removido nivel_dificuldade
?>

<main class="container admin-dashboard">
    <h2>Gerenciar Perguntas</h2>
    <?php echo $message; ?>

    <div class="admin-form">
        <h3><?php echo $edit_question ? 'Editar Pergunta' : 'Adicionar Nova Pergunta'; ?></h3>
        <form action="manage_questions.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_edit_question">
            <input type="hidden" name="question_id" value="<?php echo $edit_question ? htmlspecialchars($edit_question['id']) : '0'; ?>">
            <input type="hidden" name="current_image" value="<?php echo $edit_question ? htmlspecialchars($edit_question['caminho_imagem']) : ''; ?>">
            <input type="hidden" name="current_audio" value="<?php echo $edit_question ? htmlspecialchars($edit_question['caminho_audio']) : ''; ?>">

            <div class="form-group">
                <label for="pergunta_texto">Pergunta:</label>
                <textarea id="pergunta_texto" name="pergunta_texto" rows="4" required><?php echo $edit_question ? htmlspecialchars($edit_question['pergunta_texto']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label for="categoria_id">Categoria:</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione uma categoria</option>
                    <?php while($cat = $categories_query->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_question && $edit_question['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nome_categoria']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <!-- Nível de Dificuldade Removido do formulário -->
            <div class="form-group">
                <label for="tipo_pergunta">Tipo de Pergunta:</label>
                <select id="tipo_pergunta" name="tipo_pergunta" onchange="toggleOptions(this.value)" required>
                    <option value="multipla_escolha" <?php echo ($edit_question && $edit_question['tipo_pergunta'] == 'multipla_escolha') ? 'selected' : ''; ?>>Múltipla Escolha</option>
                    <option value="verdadeiro_falso" <?php echo ($edit_question && $edit_question['tipo_pergunta'] == 'verdadeiro_falso') ? 'selected' : ''; ?>>Verdadeiro/Falso</option>
                </select>
            </div>

            <div id="multipla-escolha-options" style="display: <?php echo ($edit_question && $edit_question['tipo_pergunta'] == 'verdadeiro_falso') ? 'none' : 'block'; ?>;">
                <h4>Opções de Resposta (Múltipla Escolha)</h4>
                <?php
                // Preencher opções para edição
                $mc_options_count = count($edit_options);
                for ($i = 0; $i < 4; $i++) {
                    $option_text = isset($edit_options[$i]) ? htmlspecialchars($edit_options[$i]['opcao_texto']) : '';
                    $is_correct_checked = (isset($edit_options[$i]) && $edit_options[$i]['correta']) ? 'checked' : '';
                    echo '<div class="form-group">';
                    echo '<label>Opção ' . ($i + 1) . ':</label>';
                    echo '<input type="text" name="opcoes[]" value="' . $option_text . '" required>';
                    echo '<input type="radio" name="correta" value="' . $i . '" ' . $is_correct_checked . ' required> Correta';
                    echo '</div>';
                }
                ?>
            </div>

            <div id="verdadeiro-falso-options" style="display: <?php echo ($edit_question && $edit_question['tipo_pergunta'] == 'verdadeiro_falso') ? 'block' : 'none'; ?>;">
                <h4>Resposta Correta (Verdadeiro/Falso)</h4>
                <?php
                $vf_correct = null;
                if ($edit_question && $edit_question['tipo_pergunta'] == 'verdadeiro_falso') {
                    foreach ($edit_options as $opt) {
                        if ($opt['correta']) {
                            $vf_correct = ($opt['opcao_texto'] == 'Verdadeiro') ? 1 : 0;
                            break;
                        }
                    }
                }
                ?>
                <div class="form-group">
                    <input type="radio" name="resposta_vf_correta" value="1" <?php echo ($vf_correct === 1) ? 'checked' : ''; ?>> Verdadeiro
                    <input type="radio" name="resposta_vf_correta" value="0" <?php echo ($vf_correct === 0) ? 'checked' : ''; ?>> Falso
                </div>
            </div>

            <div class="form-group">
                <label for="imagem">Imagem (opcional):</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">
                <?php if ($edit_question && $edit_question['caminho_imagem']): ?>
                    <p>Imagem atual: <a href="../uploads/images/<?php echo htmlspecialchars($edit_question['caminho_imagem']); ?>" target="_blank"><?php echo htmlspecialchars($edit_question['caminho_imagem']); ?></a></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="audio">Áudio (opcional):</label>
                <input type="file" id="audio" name="audio" accept="audio/*">
                 <?php if ($edit_question && $edit_question['caminho_audio']): ?>
                    <p>Áudio atual: <a href="../uploads/audio/<?php echo htmlspecialchars($edit_question['caminho_audio']); ?>" target="_blank"><?php echo htmlspecialchars($edit_question['caminho_audio']); ?></a></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <input type="submit" value="<?php echo $edit_question ? 'Atualizar Pergunta' : 'Adicionar Pergunta'; ?>">
            </div>
        </form>
    </div>

    <section class="admin-section">
        <h3>Lista de Perguntas</h3>
        <?php if ($questions_result->num_rows > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pergunta</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $questions_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($row['pergunta_texto'], 0, 80, "...")); ?></td> <!-- Alterado para pergunta_texto -->
                    <td><?php echo htmlspecialchars($row['tipo_pergunta'] == 'multipla_escolha' ? 'Múltipla Escolha' : 'Verdadeiro/Falso'); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                    <td class="actions">
                        <a href="manage_questions.php?edit_id=<?php echo $row['id']; ?>" class="edit">Editar</a>
                        <a href="manage_questions.php?delete_id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir esta pergunta e suas opções?');">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Nenhuma pergunta cadastrada.</p>
        <?php endif; ?>
    </section>
</main>

<script>
    function toggleOptions(type) {
        if (type === 'multipla_escolha') {
            document.getElementById('multipla-escolha-options').style.display = 'block';
            document.getElementById('verdadeiro-falso-options').style.display = 'none';
            // Set required for multiple choice inputs when active
            document.querySelectorAll('#multipla-escolha-options input[type="text"]').forEach(input => input.setAttribute('required', 'required'));
            document.querySelector('#multipla-escolha-options input[type="radio"]').setAttribute('required', 'required');
            // Remove required for true/false inputs when inactive
            document.querySelectorAll('#verdadeiro-falso-options input[type="radio"]').forEach(input => input.removeAttribute('required'));
        } else {
            document.getElementById('multipla-escolha-options').style.display = 'none';
            document.getElementById('verdadeiro-falso-options').style.display = 'block';
             // Remove required for multiple choice inputs when inactive
            document.querySelectorAll('#multipla-escolha-options input[type="text"]').forEach(input => input.removeAttribute('required'));
            document.querySelector('#multipla-escolha-options input[type="radio"]').removeAttribute('required');
            // Set required for true/false inputs when active
            document.querySelectorAll('#verdadeiro-falso-options input[type="radio"]').forEach(input => input.setAttribute('required', 'required'));
        }
    }

    // Chamar no carregamento da página para definir o estado inicial
    document.addEventListener('DOMContentLoaded', function() {
        toggleOptions(document.getElementById('tipo_pergunta').value);
    });
</script>

<?php
$conn->close();
include 'admin_footer.php';
?>