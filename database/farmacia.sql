CREATE DATABASE IF NOT EXISTS saude_plus
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE saude_plus;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('Gestor', 'Atendente', 'Farmaceutico') NOT NULL DEFAULT 'Atendente',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL UNIQUE,
    descricao VARCHAR(255) NULL,
    controlado TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fornecedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    cnpj VARCHAR(18) NULL,
    contato VARCHAR(120) NULL,
    telefone VARCHAR(20) NULL,
    email VARCHAR(120) NULL,
    cidade VARCHAR(80) NULL,
    uf CHAR(2) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medicamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NULL,
    fornecedor_id INT UNSIGNED NULL,
    nome VARCHAR(150) NOT NULL,
    principio_ativo VARCHAR(150) NOT NULL,
    dosagem VARCHAR(60) NULL,
    apresentacao VARCHAR(60) NULL,
    fabricante VARCHAR(120) NULL,
    lote VARCHAR(60) NULL,
    registro_anvisa VARCHAR(30) NULL,
    validade DATE NULL,
    estoque_atual INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 0,
    preco_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    controlado TINYINT(1) NOT NULL DEFAULT 0,
    localizacao VARCHAR(120) NULL,
    status ENUM('ATIVO', 'EM FALTA', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    descricao TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_medicamentos_nome (nome),
    INDEX idx_medicamentos_status (status),
    INDEX idx_medicamentos_validade (validade),
    CONSTRAINT fk_medicamentos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT fk_medicamentos_fornecedor
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
);

CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medicamento_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tipo ENUM('ENTRADA', 'SAIDA', 'AJUSTE') NOT NULL,
    quantidade INT NOT NULL,
    motivo VARCHAR(150) NULL,
    observacao TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_movimentacoes_tipo (tipo),
    CONSTRAINT fk_movimentacoes_medicamento
        FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id),
    CONSTRAINT fk_movimentacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

INSERT INTO usuarios (id, nome, username, senha_hash, perfil)
VALUES
    (1, 'Administrador', 'admin', '$2y$12$o8Qhd9Ib1gnR9Et5.2I8denPYYbEK9nz9kB8wQCtZ4itTVGoN7XmK', 'Gestor'),
    (2, 'Equipe da Unidade', 'farmacia', '$2y$12$NxNjVWpwnZvWvuModpwwxuvemA4xvenDKIH7ezYMw1sjzU7423J86', 'Atendente')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    senha_hash = VALUES(senha_hash),
    perfil = VALUES(perfil);

INSERT INTO categorias (id, nome, descricao, controlado)
VALUES
    (1, 'Analgesico', 'Medicamentos para dor e febre', 0),
    (2, 'Gastro', 'Medicamentos gastricos', 0),
    (3, 'Antibiotico', 'Tratamentos com antimicrobianos', 0),
    (4, 'Controlado', 'Itens com rastreabilidade especial', 1),
    (5, 'Suplemento', 'Vitaminas e suplementos', 0)
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao),
    controlado = VALUES(controlado);

INSERT INTO fornecedores (id, nome, cnpj, contato, telefone, email, cidade, uf)
VALUES
    (1, 'Distribuidora Vida', '11.111.111/0001-11', 'Julia Martins', '(11) 4000-1000', 'comercial@vida.com', 'Sao Paulo', 'SP'),
    (2, 'Central Pharma', '22.222.222/0001-22', 'Carlos Lima', '(21) 3000-2000', 'vendas@centralpharma.com', 'Rio de Janeiro', 'RJ'),
    (3, 'Bio Distribuicao', '33.333.333/0001-33', 'Renata Souza', '(31) 3500-3000', 'contato@biodistribuicao.com', 'Belo Horizonte', 'MG'),
    (4, 'ControlMed', '44.444.444/0001-44', 'Patricia Alves', '(41) 3600-4000', 'controlados@controlmed.com', 'Curitiba', 'PR')
ON DUPLICATE KEY UPDATE
    contato = VALUES(contato),
    telefone = VALUES(telefone),
    email = VALUES(email);

INSERT INTO medicamentos (
    id, categoria_id, fornecedor_id, nome, principio_ativo, dosagem, apresentacao, fabricante, lote,
    registro_anvisa, validade, estoque_atual, estoque_minimo, preco_unitario, controlado,
    localizacao, status, descricao
)
VALUES
    (1, 1, 1, 'Dipirona', 'Metamizol sodico', '500 mg', 'Comprimido', 'Neo Quimica', 'DIP-2026-001', '123456789001', '2026-11-30', 180, 50, 12.90, 0, 'Prateleira A1', 'ATIVO', 'Medicamento para controle de dor e febre.'),
    (2, 2, 2, 'Omeprazol', 'Omeprazol', '20 mg', 'Capsula', 'Medley', 'OME-2026-114', '223456789001', '2026-08-15', 64, 30, 18.50, 0, 'Prateleira B2', 'ATIVO', 'Protecao gastrica e tratamento de refluxo.'),
    (3, 3, 3, 'Amoxicilina', 'Amoxicilina tri-hidratada', '500 mg', 'Capsula', 'EMS', 'AMO-2026-332', '323456789001', '2026-05-21', 22, 25, 27.40, 0, 'Geladeira Farmaceutica 01', 'EM FALTA', 'Antibiotico oral com estoque abaixo do minimo.'),
    (4, 4, 4, 'Rivotril', 'Clonazepam', '2 mg', 'Comprimido', 'Roche', 'RIV-2026-090', '423456789001', '2026-07-12', 16, 10, 32.80, 1, 'Armario Controlados C3', 'ATIVO', 'Medicamento controlado com acesso restrito.'),
    (5, 5, 1, 'Vitamina C', 'Acido ascorbico', '1 g', 'Envelope', 'Cimed', 'VTC-2026-441', '523456789001', '2027-02-28', 210, 80, 9.70, 0, 'Prateleira D4', 'ATIVO', 'Suplemento vitaminico de alta rotatividade.')
ON DUPLICATE KEY UPDATE
    categoria_id = VALUES(categoria_id),
    fornecedor_id = VALUES(fornecedor_id),
    principio_ativo = VALUES(principio_ativo),
    dosagem = VALUES(dosagem),
    estoque_atual = VALUES(estoque_atual),
    estoque_minimo = VALUES(estoque_minimo),
    preco_unitario = VALUES(preco_unitario),
    status = VALUES(status),
    updated_at = CURRENT_TIMESTAMP;

INSERT INTO movimentacoes_estoque (medicamento_id, usuario_id, tipo, quantidade, motivo, observacao)
VALUES
    (1, 1, 'ENTRADA', 180, 'Carga inicial', 'Importacao dos dados iniciais do sistema'),
    (2, 1, 'ENTRADA', 64, 'Carga inicial', 'Importacao dos dados iniciais do sistema'),
    (3, 2, 'AJUSTE', -3, 'Baixo estoque', 'Ajuste apos conferencia de inventario'),
    (4, 1, 'ENTRADA', 16, 'Controle especial', 'Entrada de medicamento controlado'),
    (5, 2, 'ENTRADA', 210, 'Reposicao', 'Carga inicial de suplemento');

SELECT 'Credenciais de acesso: admin/admin123 e farmacia/farmacia123' AS observacao;
