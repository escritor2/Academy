<?php
require_once __DIR__ . '/../Model/ProdutoDAO.php';
require_once __DIR__ . '/../Model/Produto.php';

class ProdutoController {
    private $dao;

    public function __construct() {
        $this->dao = new ProdutoDAO();
    }

    public function listar() {
        return $this->dao->lerProdutos();
    }

    public function criar($nome, $categoria, $preco, $estoque) {
        $produto = new Produto($nome, $categoria, $preco, $estoque);
        $this->dao->criarProduto($produto);
    }

    public function atualizar($nome, $categoria, $preco, $estoque) {
        $this->dao->atualizarProduto($nome, $categoria, $preco, $estoque);
    }

    public function deletar($nome) {
        $this->dao->excluirProduto($nome);
    }
}
?>