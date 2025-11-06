<?php
require_once __DIR__ . '/Aluno.php';

class AlunoDAO {
    private $alunoArray = [];   
    private $alunoJson = 'Aluno.json';

    public function __construct() {
        if (file_exists($this->alunoJson)) {
            $conteudoAluno = file_get_contents($this->alunoJson);
            $dadosAlunoEmArray = json_decode($conteudoAluno, true);

            if ($dadosAlunoEmArray) {
                foreach ($dadosAlunoEmArray as $nome => $info) {
                    $this->alunoArray[$nome] = new Aluno(
                        $info['nome'],
                        $info['idade'],
                        $info['cpf'],
                        $info['matricula']
                    );
                }
            }
        }
    }

    private function salvarAluno() {
        $dadosParaSalvar = [];

        foreach ($this->alunoArray as $nome => $aluno) {
            $dadosParaSalvar[$nome] = [
                'nome' => $aluno->getNome(),
                'idade' => $aluno->getIdade(),
                'cpf' => $aluno->getCpf(),
                'matricula' => $aluno->getMatricula()
            ];
        }
        file_put_contents($this->alunoJson, json_encode($dadosParaSalvar, JSON_PRETTY_PRINT));
    }

    // Create
    public function criarAluno(Aluno $aluno) {
        $this->alunoArray[$aluno->getNome()] = $aluno;
        $this->salvarAluno();
    }

    // Read
    public function lerAluno() {
        return $this->alunoArray;
    }

    // Update
    public function atualizarAluno($nome, $idade, $cpf, $matricula) {
        if (isset($this->alunoArray[$nome])) {
            $this->alunoArray[$nome]->setIdade($idade);
            $this->alunoArray[$nome]->setCpf($cpf);
            $this->alunoArray[$nome]->setMatricula($matricula);
        }
        $this->salvarAluno();
    }

    // Delete
    public function excluirAluno($nome) {
        unset($this->alunoArray[$nome]);
        $this->salvarAluno();
    }
}
?>