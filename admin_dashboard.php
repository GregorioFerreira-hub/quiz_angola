<?php
session_start();
require_once 'db_connect.php';

// --- Proteção do Painel Administrativo ---
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php"); // Redireciona se não estiver logado ou não for admin
    exit();
}
// --- Fim Proteção ---

$mensagem = '';
if (isset($_GET['msg'])) {
    $mensagem = htmlspecialchars($_GET['msg']);
}

// Lógica para excluir pergunta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id_pergunta_delete = (int)$_POST['id_pergunta'];

    // Para garantir que as respostas também são excluídas devido à FOREIGN KEY com ON DELETE CASCADE
    $stmt = $conn->prepare("DELETE FROM perguntas WHERE id_pergunta = ?");
    $stmt->bind_param("i", $id_pergunta_delete);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=" . urlencode("Pergunta excluída com sucesso!"));
        exit();
    } else {
        $mensagem = "<p class='error'>Erro ao excluir pergunta: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Buscar todas as perguntas para exibir
$sql = "SELECT id_pergunta, texto_pergunta, categoria, nivel_dificuldade FROM perguntas ORDER BY categoria, id_pergunta DESC";
$result = $conn->query($sql);

$perguntas = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $perguntas[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Quiz Conheça Angola</title>
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
            max-width: 900px;
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
        .admin-actions {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            margin: 5px;
        }
        .btn-add {
            background-color: #28a745; /* Verde */
            color: white;
            border: 1px solid #218838;
        }
        .btn-add:hover {
            background-color: #218838;
        }
        .btn-logout {
            background-color: #000000; /* Preto */
            color: white;
            border: 1px solid #333333;
        }
        .btn-logout:hover {
            background-color: #333333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            color: #333;
        }
        th {
            background-color: #f2f2f2;
            color: #555;
            font-weight: bold;
        }
        .table-actions {
            display: flex;
            gap: 5px;
            justify-content: flex-start;
        }
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            font-size: 0.9em;
            text-align: center;
            white-space: nowrap;
        }
        .btn-edit {
            background-color: #007bff;
            color: white;
            border: 1px solid #0056b3;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: 1px solid #c82333;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        form.delete-form {
            display: inline; /* Para alinhar o botão de exclusão */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Painel Administrativo</h1>

        <?php if ($mensagem): ?>
            <p class="message <?php echo strpos($mensagem, 'sucesso') !== false ? 'success' : 'error'; ?>">
                <?php echo $mensagem; ?>
            </p>
        <?php endif; ?>

        <div class="admin-actions">
            <a href="gerir_perguntas.php" class="btn btn-add">Adicionar Nova Pergunta</a>
            <a href="logout.php" class="btn btn-logout">Sair do Admin</a>
        </div>

        <?php if (!empty($perguntas)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pergunta</th>
                        <th>Categoria</th>
                        <th>Dificuldade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perguntas as $pergunta): ?>
                        <tr>
                            <td><?php echo $pergunta['id_pergunta']; ?></td>
                            <td><?php echo htmlspecialchars($pergunta['texto_pergunta']); ?></td>
                            <td><?php echo htmlspecialchars($pergunta['categoria']); ?></td>
                            <td><?php echo htmlspecialchars($pergunta['nivel_dificuldade']); ?></td>
                            <td class="table-actions">
                                <a href="gerir_perguntas.php?id=<?php echo $pergunta['id_pergunta']; ?>" class="btn btn-edit">Editar</a>
                                <form action="admin_dashboard.php" method="POST" class="delete-form" onsubmit="return confirm('Tem certeza que deseja excluir esta pergunta e suas respostas?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_pergunta" value="<?php echo $pergunta['id_pergunta']; ?>">
                                    <button type="submit" class="btn btn-delete">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #555;">Nenhuma pergunta encontrada. Adicione a sua primeira pergunta!</p>
        <?php endif; ?>
    </div>
</body>
</html>