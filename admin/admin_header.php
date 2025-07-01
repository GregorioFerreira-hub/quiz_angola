<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quiz Conheça Angola</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Caminho relativo ao style.css -->
    <style>
        .admin-header {
            background-color: var(--color-red);
            color: var(--color-white);
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .admin-header h1 {
            margin-bottom: 10px;
        }
        .admin-header nav a {
            color: var(--color-white);
            text-decoration: none;
            margin: 0 15px;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .admin-header nav a:hover {
            color: var(--color-yellow);
        }
        .admin-dashboard {
            padding: 40px 20px;
            background-color: var(--color-light-gray);
            border-radius: 10px;
            margin-top: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background-color: var(--color-white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: var(--color-dark-gray);
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        .stat-card p {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--color-red);
        }
        .admin-sections {
            display: grid;
            grid-template-columns: 1fr; /* Por padrão, uma coluna */
            gap: 30px;
        }
        .admin-section {
            background-color: var(--color-white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-section h3 {
            color: var(--color-black);
            margin-bottom: 20px;
            font-size: 1.8em;
            border-bottom: 2px solid var(--color-yellow);
            padding-bottom: 10px;
        }
        .admin-section ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .admin-section ul li .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            background-color: var(--color-yellow);
            color: var(--color-black);
            border: none;
        }
        .admin-section ul li .btn:hover {
            background-color: var(--color-red);
            color: var(--color-white);
        }

        /* Tabela de gerência de itens (perguntas, usuários, etc.) */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .admin-table th {
            background-color: var(--color-dark-gray);
            color: var(--color-white);
        }
        .admin-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .admin-table .actions a {
            margin-right: 10px;
            color: var(--color-red);
            text-decoration: none;
        }
        .admin-table .actions a.edit { color: var(--color-yellow); }
        .admin-table .actions a.delete { color: var(--color-red); }

        .admin-form {
            background-color: var(--color-white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .admin-form .form-group label {
            color: var(--color-dark-gray);
        }
        .admin-form .form-group input[type="text"],
        .admin-form .form-group input[type="email"],
        .admin-form .form-group input[type="password"],
        .admin-form .form-group textarea,
        .admin-form .form-group select {
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            color: var(--color-dark-gray);
        }
        .admin-form .form-group input[type="submit"] {
            background-color: var(--color-yellow);
            color: var(--color-black);
        }
        .admin-form .form-group input[type="submit"]:hover {
            background-color: var(--color-red);
            color: var(--color-white);
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1>Admin Panel</h1>
            <nav>
                <a href="dashboard_admin.php">Dashboard</a>
                <a href="manage_questions.php">Perguntas</a>
                <a href="manage_categories.php">Categorias</a>
                <a href="manage_users.php">Usuários</a>
                <a href="../logout.php">Sair</a>
            </nav>
        </div>
    </header>