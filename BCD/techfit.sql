create database techfit_ofc;

use techfit_ofc;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin', 'aluno', 'professor', 'recepcao') NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Detalhes específicos dos Alunos (Vinculado a usuarios)
CREATE TABLE alunos (
    usuario_id INT PRIMARY KEY,
    plano ENUM('Básico', 'Premium') DEFAULT 'Básico',
    status ENUM('Ativo', 'Inativo', 'Pendente') DEFAULT 'Pendente',
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    meta_agua INT DEFAULT 3000,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de Produtos (Do seu adm.php)
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50),
    quantidade INT DEFAULT 0,
    preco DECIMAL(10, 2)
);

-- Tabela de Exercícios (Catálogo geral)
CREATE TABLE exercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    equipamento VARCHAR(50),
    grupo_muscular VARCHAR(50) -- Ex: Peito, Costas
);

-- Tabela para montar as Fichas de Treino (A, B, C) do aluno
CREATE TABLE treinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    tipo CHAR(1) NOT NULL, -- 'A', 'B', 'C'
    exercicio_id INT NOT NULL,
    series INT DEFAULT 3,
    repeticoes VARCHAR(20) DEFAULT '10', -- Pode ser "10-12" ou "10"
    carga INT DEFAULT 0,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id),
    FOREIGN KEY (exercicio_id) REFERENCES exercicios(id)
);