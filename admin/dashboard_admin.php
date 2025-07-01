<?php
session_start();
require_once '../config.php'; // Caminho para config.php
include 'admin_header.php'; // Incluir cabeçalho administrativo

// Redirecionar se não for admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("location: ../login.php");
    exit;
}

// Lógica para obter contagens
$total_users = $conn->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0];
$total_questions = $conn->query("SELECT COUNT(*) FROM perguntas")->fetch_row()[0];
$total_categories = $conn->query("SELECT COUNT(*) FROM categorias")->fetch_row()[0];

// Obter ranking (Top 10) da tabela pontuacoes
$ranking_sql = "SELECT u.nome_usuario, p.pontuacao_total
                FROM pontuacoes p
                JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.pontuacao_total DESC LIMIT 10";
$ranking_result = $conn->query($ranking_sql);

?>

<main class="container admin-dashboard">
    <h2>Painel Administrativo</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total de Usuários</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total de Perguntas</h3>
            <p><?php echo $total_questions; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total de Categorias</h3>
            <p><?php echo $total_categories; ?></p>
        </div>
    </div>

    <div class="admin-sections">
        <section class="admin-section">
            <h3>Gerenciar Conteúdo</h3>
            <ul>
                <li><a href="manage_questions.php" class="btn primary">Perguntas</a></li>
                <li><a href="manage_categories.php" class="btn primary">Categorias</a></li>
            </ul>
        </section>

        <section class="admin-section">
            <h3>Gerenciar Usuários</h3>
            <ul>
                <li><a href="manage_users.php" class="btn primary">Usuários</a></li>
            </ul>
        </section>

        <section class="admin-section">
            <h3>Ranking Global (Top 10)</h3>
            <?php if ($ranking_result->num_rows > 0): ?>
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
                        <td><?php echo htmlspecialchars($row['nome_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['pontuacao_total']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>Nenhum dado de ranking disponível.</p>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include 'admin_footer.php'; ?>