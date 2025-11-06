<?php
require_once __DIR__ . '/../Model/AlunoDAO.php';
require_once __DIR__ . '/../Model/Aluno.php';

Class AlunoController {
    private $dao;

    public function __construct() {
        $this->dao = new AlunoDAO();
    }

    public function ler () {
        return $this->dao->lerBebidas();
    }

    public function criar() {

    }

    public function atualizar() {
        $this->dao->atualizarAluno($nome, $idade, $cpf, $matricula);
    }

    public function deletar($nome) {
        $this->dao->excluirAluno($nome);
    }

}