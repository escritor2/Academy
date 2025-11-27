<?php
class Usuario {
    private $nome;
    private $email;
    private $senha;
    private $tipo;

    public function __construct($nome, $email, $senha, $tipo ='cliente') {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->tipo = $tipo;
    }

    // Getters
    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getSenha() {
        return $this->senha;
    }

    public function getTipo() {
        return $this->tipo;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
}
?>