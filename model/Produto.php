<?php
class Produto {
    private $nome;
    private $categoria;
    private $preco;
    private $estoque;

    public function __construct($nome, $categoria, $preco, $estoque) {
        $this->nome = $nome;
        $this->categoria = $categoria;
        $this->preco = $preco;
        $this->estoque = $estoque;
    }

    // Getters
    public function getNome() {
        return $this->nome;
    }

    public function getCategoria() {
        return $this->categoria;
    }

    public function getPreco() {
        return $this->preco;
    }

    public function getEstoque() {
        return $this->estoque;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }

    public function setPreco($preco) {
        $this->preco = $preco;
    }

    public function setEstoque($estoque) {
        $this->estoque = $estoque;
    }
}
?>