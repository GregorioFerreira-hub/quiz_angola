-- Tabela para usuários (jogadores e administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, -- Para senhas hasheadas
    tipo_usuario ENUM('jogador', 'admin') DEFAULT 'jogador',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabela para categorias das perguntas (História, Geografia, etc.)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_categoria VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabela para perguntas
CREATE TABLE IF NOT EXISTS perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    pergunta_texto TEXT NOT NULL,
    tipo_pergunta ENUM('multipla_escolha', 'verdadeiro_falso') NOT NULL,
    caminho_imagem VARCHAR(255), -- Caminho para a imagem, se houver
    caminho_audio VARCHAR(255),  -- Caminho para o áudio, se houver
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabela para opções de resposta (para perguntas de múltipla escolha)
CREATE TABLE IF NOT EXISTS opcoes_resposta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta_id INT NOT NULL,
    opcao_texto VARCHAR(255) NOT NULL,
    correta BOOLEAN NOT NULL DEFAULT FALSE, -- TRUE se for a resposta correta
    FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabela para histórico de cada tentativa de quiz do usuário (para granularidade)
CREATE TABLE IF NOT EXISTS historico_quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    resposta_dada_id INT, -- ID da opção de resposta escolhida (para múltipla escolha)
    resposta_dada_vf BOOLEAN, -- TRUE/FALSE para verdadeiro ou falso
    correta BOOLEAN NOT NULL, -- TRUE se a resposta estiver correta
    data_tentativa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE,
    FOREIGN KEY (resposta_dada_id) REFERENCES opcoes_resposta(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabela para armazenar a pontuação total dos usuários para o ranking
CREATE TABLE IF NOT EXISTS pontuacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE, -- Um registro por usuário para a pontuação total
    pontuacao_total INT DEFAULT 0,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;