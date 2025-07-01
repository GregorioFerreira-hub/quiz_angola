<?php
session_start();
require_once '../config.php';
include 'admin_header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

$message = '';

// Lógica para Adicionar/Editar Usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_edit_user') {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $nome_usuario = trim($_POST['nome_usuario']);
        $email = trim($_POST['email']);
        $tipo_usuario = $_POST['tipo_usuario'];
        $senha = $_POST['senha'];

        if (empty($nome_usuario) || empty($email) || empty($tipo_usuario)) {
            $message = '<div class="message error">Por favor, preencha todos os campos obrigatórios.</div>';
        } else {
            // Verificar duplicidade de nome de usuário/email
            $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE (nome_usuario = ? OR email = ?) AND id != ?");
            $stmt_check->bind_param("ssi", $nome_usuario, $email, $user_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $message = '<div class="message error">Nome de usuário ou e-mail já existe.</div>';
            } else {
                if ($user_id == 0) { // Adicionar novo usuário
                    if (empty($senha) || strlen($senha) < 6) {
                        $message = '<div class="message error">A senha é obrigatória e deve ter no mínimo 6 caracteres para novos usuários.</div>';
                    } else {
                        $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, email, senha, tipo_usuario) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $nome_usuario, $email, $senha_hashed, $tipo_usuario);
                        if ($stmt->execute()) {
                            $message = '<div class="message success">Usuário adicionado com sucesso!</div>';
                        } else {
                            $message = '<div class="message error">Erro ao adicionar usuário: ' . $conn->error . '</div>';
                        }
                        $stmt->close();
                    }
                } else { // Editar usuário existente
                    $sql_update = "UPDATE usuarios SET nome_usuario = ?, email = ?, tipo_usuario = ? WHERE id = ?";
                    if (!empty($senha)) {
                        if (strlen($senha) < 6) {
                            $message = '<div class="message error">A senha deve ter no mínimo 6 caracteres.</div>';
                        } else {
                            $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);
                            $sql_update = "UPDATE usuarios SET nome_usuario = ?, email = ?, senha = ?, tipo_usuario = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql_update);
                            $stmt->bind_param("ssssi", $nome_usuario, $email, $senha_hashed, $tipo_usuario, $user_id);
                        }
                    } else {
                        $stmt = $conn->prepare($sql_update);
                        $stmt->bind_param("sssi", $nome_usuario, $email, $tipo_usuario, $user_id);
                    }

                    if (empty($message) && $stmt->execute()) {
                        $message = '<div class="message success">Usuário atualizado com sucesso!</div>';
                    } elseif (empty($message)) {
                        $message = '<div class="message error">Erro ao atualizar usuário: ' . $conn->error . '</div>';
                    }
                    $stmt->close();
                }
            }
            $stmt_check->close();
        }
    }
}

// Lógica para Deletar Usuário
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Evitar que um admin se auto-delete (opcional, mas boa prática)
    if ($delete_id == $_SESSION['usuario_id']) {
        $message = '<div class="message error">Você não pode excluir sua própria conta de administrador.</div>';
    } else {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = '<div class="message success">Usuário excluído com sucesso!</div>';
        } else {
            $message = '<div class="message error">Erro ao excluir usuário: ' . $conn->error . '</div>';
        }
        $stmt->close();
    }
}

// Lógica para carregar usuário para edição
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT id, nome_usuario, email, tipo_usuario FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
    $stmt->close();
}

// Obter todos os usuários para exibição (JOIN com pontuacoes)
$users_result = $conn->query("SELECT u.id, u.nome_usuario, u.email, u.tipo_usuario, COALESCE(p.pontuacao_total, 0) AS pontuacao_total
                               FROM usuarios u
                               LEFT JOIN pontuacoes p ON u.id = p.usuario_id
                               ORDER BY u.nome_usuario ASC");
?>

<main class="container admin-dashboard">
    <h2>Gerenciar Usuários</h2>
    <?php echo $message; ?>

    <div class="admin-form">
        <h3><?php echo $edit_user ? 'Editar Usuário' : 'Adicionar Novo Usuário'; ?></h3>
        <form action="manage_users.php" method="POST">
            <input type="hidden" name="action" value="add_edit_user">
            <input type="hidden" name="user_id" value="<?php echo $edit_user ? htmlspecialchars($edit_user['id']) : '0'; ?>">

            <div class="form-group">
                <label for="nome_usuario">Nome de Usuário:</label>
                <input type="text" id="nome_usuario" name="nome_usuario" value="<?php echo $edit_user ? htmlspecialchars($edit_user['nome_usuario']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha (deixe em branco para manter a atual):</label>
                <input type="password" id="senha" name="senha">
                <?php if ($edit_user): ?><small>Preencha apenas se quiser alterar a senha.</small><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="tipo_usuario">Tipo de Usuário:</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="jogador" <?php echo ($edit_user && $edit_user['tipo_usuario'] == 'jogador') ? 'selected' : ''; ?>>Jogador</option>
                    <option value="admin" <?php echo ($edit_user && $edit_user['tipo_usuario'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="<?php echo $edit_user ? 'Atualizar Usuário' : 'Adicionar Usuário'; ?>">
            </div>
        </form>
    </div>

    <section class="admin-section">
        <h3>Lista de Usuários</h3>
        <?php if ($users_result->num_rows > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome de Usuário</th>
                    <th>E-mail</th>
                    <th>Tipo</th>
                    <th>Pontuação Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nome_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['tipo_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($row['pontuacao_total']); ?></td>
                    <td class="actions">
                        <a href="manage_users.php?edit_id=<?php echo $row['id']; ?>" class="edit">Editar</a>
                        <?php if ($row['id'] != $_SESSION['usuario_id']): // Não permitir deletar a própria conta ?>
                            <a href="manage_users.php?delete_id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Nenhum usuário cadastrado.</p>
        <?php endif; ?>
    </section>
</main>

<?php
$conn->close();
include 'admin_footer.php';
?>