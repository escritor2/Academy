<?php
class Aluno {
    private $nome;
    private $idade;
    private $cpf;
    private $matricula;

    public function __construct($nome, $idade, $cpf, $matricula) {
        $this->nome = $nome;
        $this->idade = $idade;
        $this->cpf = $cpf;
        $this->matricula = $matricula;
    }

    // Getters
    public function getNome() {
        return $this->nome;
    }

    public function getIdade() {
        return $this->idade;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function getMatricula() {
        return $this->matricula;
    }

    // Setters
    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setIdade($idade) {
        $this->idade = $idade;
    }

    public function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    public function setMatricula($matricula) {
        $this->matricula = $matricula;
    }
}
?>