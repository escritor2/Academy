<?php
require_once __DIR__ . '/../Model/VendasDAO.php';
require_once __DIR__ . '/../Model/Venda.php';

class VendasController {
    private $dao;

    public function __construct() {
        $this->dao = new VendasDAO();
    }

    public function listar() {
        return $this->dao->lerVendas();
    }

    public function criar($produto, $cliente, $quantidade, $valorTotal) {
        $venda = new Venda($produto, $cliente, $quantidade, $valorTotal);
        $this->dao->criarVenda($venda);
    }

    public function buscarPorId($id) {
        return $this->dao->buscarPorId($id);
    }

    public function deletar($id) {
        $this->dao->excluirVenda($id);
    }
}
?>