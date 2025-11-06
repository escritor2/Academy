<?php
require_once __DIR__ . '/../Model/FuncionarioDAO.php';
require_once __DIR__ . '/../Model/Funcionario.php';

class FuncionarioController {
    private $dao;

    public function __construct() {
        $this->dao = new FuncionarioDAO();
    }

    public function listar() {
        return $this->dao->lerFuncionarios();
    }

    public function criar($nome, $cargo, $salario, $email) {
        $funcionario = new Funcionario($nome, $cargo, $salario, $email);
        $this->dao->criarFuncionario($funcionario);
    }

    public function atualizar($nome, $cargo, $salario, $email) {
        $this->dao->atualizarFuncionario($nome, $cargo, $salario, $email);
    }

    public function deletar($nome) {
        $this->dao->excluirFuncionario($nome);
    }
}
?>