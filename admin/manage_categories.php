<?php
session_start();
require_once '../config.php';
include 'admin_header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$message = '';

// Lógica para Adicionar/Editar Categoria
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_edit_category') {
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $nome_categoria = trim($_POST['nome_categoria']);
        $descricao = trim($_POST['descricao']);

        if (empty($nome_categoria)) {
            $message = '<div class="message error">O nome da categoria não pode ser vazio.</div>';
        } else {
            // Verificar duplicidade de nome
            $stmt_check = $conn->prepare("SELECT id FROM categorias WHERE nome_categoria = ? AND id != ?");
            $stmt_check->bind_param("si", $nome_categoria, $category_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $message = '<div class="message error">Já existe uma categoria com este nome.</div>';
            } else {
                if ($category_id == 0) { // Adicionar nova categoria
                    $stmt = $conn->prepare("INSERT INTO categorias (nome_categoria, descricao) VALUES (?, ?)");
                    $stmt->bind_param("ss", $nome_categoria, $descricao);
                    if ($stmt->execute()) {
                        $message = '<div class="message success">Categoria adicionada com sucesso!</div>';
                    } else {
                        $message = '<div class="message error">Erro ao adicionar categoria: ' . $conn->error . '</div>';
                    }
                } else { // Editar categoria existente
                    $stmt = $conn->prepare("UPDATE categorias SET nome_categoria = ?, descricao = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $nome_categoria, $descricao, $category_id);
                    if ($stmt->execute()) {
                        $message = '<div class="message success">Categoria atualizada com sucesso!</div>';
                    } else {
                        $message = '<div class="message error">Erro ao atualizar categoria: ' . $conn->error . '</div>';
                    }
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
    }
}

// Lógica para Deletar Categoria
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Antes de deletar, verificar se há perguntas associadas para evitar erro de FK
    $stmt_check_questions = $conn->prepare("SELECT COUNT(*) FROM perguntas WHERE categoria_id = ?");
    $stmt_check_questions->bind_param("i", $delete_id);
    $stmt_check_questions->execute();
    $stmt_check_questions->bind_result($num_questions);
    $stmt_check_questions->fetch();
    $stmt_check_questions->close();

    if ($num_questions > 0) {
        $message = '<div class="message error">Não é possível excluir esta categoria porque ela possui perguntas associadas. Exclua as perguntas primeiro ou redefina suas categorias.</div>';
    } else {
        $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = '<div class="message success">Categoria excluída com sucesso!</div>';
        } else {
            $message = '<div class="message error">Erro ao excluir categoria: ' . $conn->error . '</div>';
        }
        $stmt->close();
    }
}

// Lógica para carregar categoria para edição
$edit_category = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT id, nome_categoria, descricao FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_category = $result->fetch_assoc();
    $stmt->close();
}

// Obter todas as categorias para exibição
$categories_result = $conn->query("SELECT id, nome_categoria, descricao FROM categorias ORDER BY nome_categoria ASC");
?>

<main class="container admin-dashboard">
    <h2>Gerenciar Categorias</h2>
    <?php echo $message; ?>

    <div class="admin-form">
        <h3><?php echo $edit_category ? 'Editar Categoria' : 'Adicionar Nova Categoria'; ?></h3>
        <form action="manage_categories.php" method="POST">
            <input type="hidden" name="action" value="add_edit_category">
            <input type="hidden" name="category_id" value="<?php echo $edit_category ? htmlspecialchars($edit_category['id']) : '0'; ?>">

            <div class="form-group">
                <label for="nome_categoria">Nome da Categoria:</label>
                <input type="text" id="nome_categoria" name="nome_categoria" value="<?php echo $edit_category ? htmlspecialchars($edit_category['nome_categoria']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['descricao']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="<?php echo $edit_category ? 'Atualizar Categoria' : 'Adicionar Categoria'; ?>">
            </div>
        </form>
    </div>

    <section class="admin-section">
        <h3>Lista de Categorias</h3>
        <?php if ($categories_result->num_rows > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $categories_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_categoria']); ?></td>
                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                    <td class="actions">
                        <a href="manage_categories.php?edit_id=<?php echo $row['id']; ?>" class="edit">Editar</a>
                        <a href="manage_categories.php?delete_id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir esta categoria? Isso não exclui as perguntas associadas, mas pode causar problemas se não forem re-categorizadas. Prefira editar as perguntas antes de excluir a categoria.');">Excluir</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Nenhuma categoria cadastrada.</p>
        <?php endif; ?>
    </section>
</main>

<?php
$conn->close();
include 'admin_footer.php';
?>