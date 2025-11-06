<?php
require_once __DIR__ . '/Funcionario.php';

class FuncionarioDAO {
    private $funcionarios = [];
    private $arquivo = 'Funcionario.json';

    public function __construct() {
        if (file_exists($this->arquivo)) {
            $dados = file_get_contents($this->arquivo);
            $funcionariosArray = json_decode($dados, true);
            
            if ($funcionariosArray) {
                foreach ($funcionariosArray as $nome => $info) {
                    $this->funcionarios[$nome] = new Funcionario(
                        $info['nome'],
                        $info['cargo'],
                        $info['salario'],
                        $info['email']
                    );
                }
            }
        }
    }

    private function salvar() {
        $dados = [];
        foreach ($this->funcionarios as $nome => $funcionario) {
            $dados[$nome] = [
                'nome' => $funcionario->getNome(),
                'cargo' => $funcionario->getCargo(),
                'salario' => $funcionario->getSalario(),
                'email' => $funcionario->getEmail()
            ];
        }
        file_put_contents($this->arquivo, json_encode($dados, JSON_PRETTY_PRINT));
    }

    public function criarFuncionario(Funcionario $funcionario) {
        $this->funcionarios[$funcionario->getNome()] = $funcionario;
        $this->salvar();
    }

    public function lerFuncionarios() {
        return $this->funcionarios;
    }

    public function atualizarFuncionario($nome, $cargo, $salario, $email) {
        if (isset($this->funcionarios[$nome])) {
            $this->funcionarios[$nome]->setCargo($cargo);
            $this->funcionarios[$nome]->setSalario($salario);
            $this->funcionarios[$nome]->setEmail($email);
            $this->salvar();
        }
    }

    public function excluirFuncionario($nome) {
        unset($this->funcionarios[$nome]);
        $this->salvar();
    }
}
?>