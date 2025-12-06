<?php
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/Aluno.php';

class AlunoController {
    private $dao;

    public function __construct() {
        $this->dao = new AlunoDAO();
    }

    public function cadastrar($nome, $data_nascimento, $email, $telefone, $cpf, $genero, $senha, $objetivo, $plano) {
        $aluno = new Aluno($nome, $data_nascimento, $email, $telefone, $cpf, $genero, $senha, $objetivo, $plano);
        return $this->dao->criarAluno($aluno);
    }
}