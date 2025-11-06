<?php
class Funcionario {
    private $nome;
    private $cargo;
    private $salario;
    private $email;

    public function __construct($nome, $cargo, $salario, $email) {
        $this->nome = $nome;
        $this->cargo = $cargo;
        $this->salario = $salario;
        $this->email = $email;
    }

    // Getters
    public function getNome() {
        return $this->nome;
    }

    public function getCargo() {
        return $this->cargo;
    }

    public function getSalario() {
        return $this->salario;
    }

    public function getEmail() {
        return $this->email;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setCargo($cargo) {
        $this->cargo = $cargo;
    }

    public function setSalario($salario) {
        $this->salario = $salario;
    }

    public function setEmail($email) {
        $this->email = $email;
    }
}
?>