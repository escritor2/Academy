<?php
class Fornecedor {
    private $nome;
    private $contato;
    private $produto;
    private $telefone;

    public function __construct($nome, $contato, $produto, $telefone) {
        $this->nome = $nome;
        $this->contato = $contato;
        $this->produto = $produto;
        $this->telefone = $telefone;
    }

    // Getters
    public function getNome() {
        return $this->nome;
    }

    public function getContato() {
        return $this->contato;
    }

    public function getProduto() {
        return $this->produto;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setContato($contato) {
        $this->contato = $contato;
    }

    public function setProduto($produto) {
        $this->produto = $produto;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }
}
?>