-- Inserir Categorias
INSERT INTO categorias (nome_categoria, descricao) VALUES
('História', 'Perguntas sobre a história de Angola.'),
('Geografia', 'Perguntas sobre a geografia de Angola.'),
('Cultura', 'Perguntas sobre a cultura e tradições angolanas.'),
('Personalidades', 'Perguntas sobre figuras notáveis de Angola.'),
('Desporto', 'Perguntas sobre o desporto angolano.'),
('Cultura Geral', 'Curiosidades e factos gerais sobre Angola.');

-- Inserir Perguntas e Respostas (Mínimo de 20 perguntas)

-- PERGUNTAS DE GEOGRAFIA (MÚLTIPLA ESCOLHA)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual é a capital de Angola?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Luanda', TRUE),
(@last_pergunta_id, 'Huambo', FALSE),
(@last_pergunta_id, 'Lobito', FALSE),
(@last_pergunta_id, 'Benguela', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual rio importante atravessa Angola e é um dos mais longos de África?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Rio Nilo', FALSE),
(@last_pergunta_id, 'Rio Congo (Zaire)', TRUE),
(@last_pergunta_id, 'Rio Kwanza', FALSE),
(@last_pergunta_id, 'Rio Cubango', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual província angolana é conhecida pelas suas praias paradisíacas e o deserto de Moçâmedes?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Bengo', FALSE),
(@last_pergunta_id, 'Namibe', TRUE),
(@last_pergunta_id, 'Cuanza Sul', FALSE),
(@last_pergunta_id, 'Cabinda', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Angola é banhada por qual oceano?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Oceano Atlântico', TRUE),
(@last_pergunta_id, 'Oceano Índico', FALSE),
(@last_pergunta_id, 'Oceano Pacífico', FALSE),
(@last_pergunta_id, 'Oceano Ártico', FALSE);

-- PERGUNTAS DE HISTÓRIA (VERDADEIRO/FALSO)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Angola foi colonizada por Portugal.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'História'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('A independência de Angola foi proclamada em 11 de Novembro de 1975.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'História'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O Reino do Congo foi um dos reinos mais importantes na região que hoje é Angola.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'História'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O MPLA foi o único movimento de libertação que lutou pela independência de Angola.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'História'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', FALSE), -- Falso, havia UNITA e FNLA
(@last_pergunta_id, 'Falso', TRUE);

-- PERGUNTAS DE CULTURA (MÚLTIPLA ESCOLHA)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual é um prato típico angolano feito com farinha de mandioca?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Feijoada', FALSE),
(@last_pergunta_id, 'Funji', TRUE),
(@last_pergunta_id, 'Moqueca', FALSE),
(@last_pergunta_id, 'Arroz de pato', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual é o estilo musical e de dança angolano mais conhecido internacionalmente?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Samba', FALSE),
(@last_pergunta_id, 'Kizomba', TRUE),
(@last_pergunta_id, 'Semba', FALSE),
(@last_pergunta_id, 'Fado', FALSE);

-- PERGUNTAS DE PERSONALIDADES (MÚLTIPLA ESCOLHA)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Quem foi o primeiro Presidente de Angola?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Personalidades'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'José Eduardo dos Santos', FALSE),
(@last_pergunta_id, 'Agostinho Neto', TRUE),
(@last_pergunta_id, 'Jonas Savimbi', FALSE),
(@last_pergunta_id, 'Holden Roberto', FALSE);

-- PERGUNTAS DE DESPORTO (VERDADEIRO/FALSO)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('A seleção angolana de basquetebol masculina é uma das mais vitoriosas de África.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Desporto'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

-- PERGUNTAS DE CULTURA GERAL (MÚLTIPLA ESCOLHA)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual é o animal símbolo de Angola presente no Brasão de Armas?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura Geral'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Leão', FALSE),
(@last_pergunta_id, 'Palanca Negra Gigante', TRUE),
(@last_pergunta_id, 'Elefante', FALSE),
(@last_pergunta_id, 'Zebra', FALSE);

-- Continuar preenchendo até atingir 20 perguntas
-- Mais Perguntas de Geografia (V/F)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Angola faz fronteira com a Zâmbia e a República Democrática do Congo.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O ponto mais alto de Angola é o Monte Moco, na província do Huambo.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('A província de Cabinda é um exclave de Angola.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

-- Mais Perguntas de História (Múltipla Escolha)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual dos seguintes movimentos de libertação angolanos foi liderado por Jonas Savimbi?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'História'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'MPLA', FALSE),
(@last_pergunta_id, 'FNLA', FALSE),
(@last_pergunta_id, 'UNITA', TRUE),
(@last_pergunta_id, 'FLEC', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Em que século os portugueses chegaram à costa de Angola?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'História'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Século XV', TRUE),
(@last_pergunta_id, 'Século XVI', FALSE),
(@last_pergunta_id, 'Século XVII', FALSE),
(@last_pergunta_id, 'Século XVIII', FALSE);

-- Mais Perguntas de Cultura (V/F)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O Semba é considerado o ritmo musical que deu origem à Kizomba.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('A maioria da população angolana fala apenas português.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', FALSE), -- Falso, há muitas línguas nacionais
(@last_pergunta_id, 'Falso', TRUE);

-- Mais Perguntas de Personalidades (V/F)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Lupita Nyong''o é uma atriz angolana.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Personalidades'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', FALSE), -- Ela é queniana-mexicana
(@last_pergunta_id, 'Falso', TRUE);

-- Mais Perguntas de Desporto (Múltipla Escolha)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual clube angolano é conhecido como "Os Tricolores"?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Desporto'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Petro de Luanda', TRUE),
(@last_pergunta_id, 'Primeiro de Agosto', FALSE),
(@last_pergunta_id, 'Sagrada Esperança', FALSE),
(@last_pergunta_id, 'Interclube', FALSE);

-- Mais Perguntas de Cultura Geral (V/F)
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O dia 4 de Fevereiro é celebrado em Angola como o Dia do Início da Luta Armada de Libertação Nacional.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura Geral'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('A moeda oficial de Angola é o Dólar.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura Geral'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', FALSE), -- É o Kwanza
(@last_pergunta_id, 'Falso', TRUE);

-- Mais Perguntas Variadas para completar as 20
INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual das seguintes cidades angolanas é um importante porto e centro industrial?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Geografia'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Huambo', FALSE),
(@last_pergunta_id, 'Malanje', FALSE),
(@last_pergunta_id, 'Lobito', TRUE),
(@last_pergunta_id, 'Saurimo', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O Dia da Independência de Angola é feriado nacional.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura Geral'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('Qual o nome da tradicional máscara angolana usada em rituais?', 'multipla_escolha', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Mascara Punu', FALSE),
(@last_pergunta_id, 'Mascara Chokwe (Cihongo/Mwana Pwo)', TRUE),
(@last_pergunta_id, 'Mascara Dan', FALSE),
(@last_pergunta_id, 'Mascara Baule', FALSE);

INSERT INTO perguntas (pergunta_texto, tipo_pergunta, categoria_id) VALUES
('O petróleo é o principal produto de exportação de Angola.', 'verdadeiro_falso', (SELECT id FROM categorias WHERE nome_categoria = 'Cultura Geral'));
SET @last_pergunta_id = LAST_INSERT_ID();
INSERT INTO opcoes_resposta (pergunta_id, opcao_texto, correta) VALUES
(@last_pergunta_id, 'Verdadeiro', TRUE),
(@last_pergunta_id, 'Falso', FALSE);