<?php

class Aluno {
    private $nome;
    private $data_nascimento;
    private $email;
    private $telefone;
    private $cpf;
    private $genero;
    private $senha;
    private $objetivo; // 'goal' no form
    private $plano;    // 'plan' no form

    public function __construct($nome, $data_nascimento, $email, $telefone, $cpf, $genero, $senha, $objetivo, $plano) {
        $this->nome = $nome;
        $this->data_nascimento = $data_nascimento;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->cpf = $cpf;
        $this->genero = $genero;
        $this->senha = $senha;
        $this->objetivo = $objetivo;
        $this->plano = $plano;
    }

    // Getters
    public function getNome() { return $this->nome; }
    public function getDataNascimento() { return $this->data_nascimento; }
    public function getEmail() { return $this->email; }
    public function getTelefone() { return $this->telefone; }
    public function getCpf() { return $this->cpf; }
    public function getGenero() { return $this->genero; }
    public function getSenha() { return $this->senha; }
    public function getObjetivo() { return $this->objetivo; }
    public function getPlano() { return $this->plano; }
}