<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Quiz Conheça Angola</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            /* Fundo inspirado na bandeira: Vermelho e Preto */
            background: linear-gradient(to bottom, #EF0000 50%, #000000 50%); /* Vermelho em cima, Preto em baixo */
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95); /* Fundo branco semitransparente para contraste */
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5); /* Sombra mais escura para destacar */
            max-width: 700px;
            width: 90%;
            color: #333;
            position: relative; /* Necessário para posicionar a estrela e roda */
            overflow: hidden; /* Garante que elementos fora do container não sejam visíveis */
        }

        /* Estrela e Roda Dentada (Elemento Decorativo) */
        .icon-angola {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 8em; /* Tamanho do ícone */
            color: rgba(255, 255, 0, 0.2); /* Amarelo da bandeira, semi-transparente */
            pointer-events: none; /* Garante que não interfira com cliques */
            z-index: 0; /* Fica atrás do conteúdo */
            text-shadow: 0 0 10px rgba(255, 255, 0, 0.1);
        }

        h1 {
            font-size: 2.8em;
            margin-bottom: 20px;
            color: #EF0000; /* Vermelho vibrante */
            position: relative;
            z-index: 1; /* Garante que o texto fique por cima do ícone */
        }
        p {
            font-size: 1.2em;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #333;
            position: relative;
            z-index: 1;
        }
        .category-highlight {
            color: #FFCD00; /* Amarelo da bandeira */
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2); /* Sombra suave para destacar */
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Sombra mais pronunciada */
        }
        .btn-primary {
            background-color: #FFCD00; /* Amarelo da bandeira para o botão principal */
            color: #000000; /* Texto preto para contraste no botão amarelo */
            border: 2px solid #CCA300; /* Borda escura para definir */
        }
        .btn-primary:hover {
            background-color: #CCA300; /* Amarelo mais escuro ao passar o rato */
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }
        .btn-secondary {
            background-color: #000000; /* Preto para o botão secundário */
            color: white;
            border: 2px solid #333333;
        }
        .btn-secondary:hover {
            background-color: #333333;
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }
        footer {
            margin-top: 40px;
            font-size: 0.9em;
            color: #555;
            position: relative;
            z-index: 1;
        }
        footer a {
            color: #FFCD00; /* Amarelo para links no footer */
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-angola">⚙️⭐</div>

        <h1>Bem-vindo ao Quiz Conheça Angola!</h1>
        <p>Prepare-se para testar e expandir os seus conhecimentos sobre a rica 
           <span class="category-highlight">história</span>, 
           <span class="category-highlight">geografia</span>, 
           <span class="category-highlight">cultura</span> e 
           <span class="category-highlight">curiosidades de Angola!</span>
        </p>
        <p>Desafie-se e descubra factos fascinantes sobre este país incrível.</p>
        
        <div class="buttons-container">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="cadastro.php" class="btn btn-secondary">Registar</a>
        </div>

        <footer>
            <p>&copy; <?php echo date("Y"); ?> Quiz Conheça Angola. Todos os direitos reservados.</p>
            <p>Um projeto para celebrar a riqueza angolana.</p>
        </footer>
    </div>
</body>
</html>