<?php
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/Aluno.php';

class AlunoController {
    private $dao;

    public function __construct() {
        $this->dao = new AlunoDAO();
    }

    public function ler() {
        return $this->dao->lerAluno();
    }

    public function criar($nome, $idade, $cpf, $matricula) {
        $aluno = new Aluno($nome, $idade, $cpf, $matricula);
        $this->dao->criarAluno($aluno);
    }

    public function atualizar($nome, $idade, $cpf, $matricula) {
        $this->dao->atualizarAluno($nome, $idade, $cpf, $matricula);
    }

    public function deletar($nome) {
        $this->dao->excluirAluno($nome);
    }
}
?>