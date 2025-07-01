<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Global - Quiz Conheça Angola</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="app-header">
        <div class="container">
            <h1>Ranking Global</h1>
            <nav>
                <a href="<?php echo isset($_SESSION['usuario_id']) ? 'dashboard.php' : 'index.php'; ?>" class="btn ghost">Voltar</a>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="logout.php" class="btn ghost">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="btn ghost">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container quiz-container">
        <h2>Top Jogadores do Quiz Conheça Angola</h2>
        <?php
        // Obter ranking (Top 20) da tabela pontuacoes
        $ranking_sql = "SELECT u.nome_usuario, p.pontuacao_total
                        FROM pontuacoes p
                        JOIN usuarios u ON p.usuario_id = u.id
                        ORDER BY p.pontuacao_total DESC, u.nome_usuario ASC LIMIT 20";
        $ranking_result = $conn->query($ranking_sql);

        if ($ranking_result->num_rows > 0):
        ?>
        <table class="ranking-table">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Usuário</th>
                    <th>Pontuação Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $pos = 1; while($row = $ranking_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $pos++; ?>º</td>
                    <td><?php echo htmlspecialchars($row['nome_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($row['pontuacao_total']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Nenhum jogador no ranking ainda. Seja o primeiro a jogar!</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Quiz Conheça Angola. Todos os direitos reservados.</p>
    </footer>
</body>
</html>