<?php
class Venda {
    private $id;
    private $produto;
    private $cliente;
    private $quantidade;
    private $valorTotal;
    private $data;

    public function __construct($produto, $cliente, $quantidade, $valorTotal, $data = null) {
        $this->produto = $produto;
        $this->cliente = $cliente;
        $this->quantidade = $quantidade;
        $this->valorTotal = $valorTotal;
        $this->data = $data ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getProduto() {
        return $this->produto;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getQuantidade() {
        return $this->quantidade;
    }

    public function getValorTotal() {
        return $this->valorTotal;
    }

    public function getData() {
        return $this->data;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setProduto($produto) {
        $this->produto = $produto;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setQuantidade($quantidade) {
        $this->quantidade = $quantidade;
    }

    public function setValorTotal($valorTotal) {
        $this->valorTotal = $valorTotal;
    }

    public function setData($data) {
        $this->data = $data;
    }
}
?>