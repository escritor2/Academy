<?php
require_once __DIR__ . '/../Model/FornecedorDAO.php';
require_once __DIR__ . '/../Model/Fornecedor.php';

class FornecedorController {
    private $dao;

    public function __construct() {
        $this->dao = new FornecedorDAO();
    }

    public function listar() {
        return $this->dao->lerFornecedores();
    }

    public function criar($nome, $contato, $produto, $telefone) {
        $fornecedor = new Fornecedor($nome, $contato, $produto, $telefone);
        $this->dao->criarFornecedor($fornecedor);
    }

    public function atualizar($nome, $contato, $produto, $telefone) {
        $this->dao->atualizarFornecedor($nome, $contato, $produto, $telefone);
    }

    public function deletar($nome) {
        $this->dao->excluirFornecedor($nome);
    }
}
?>