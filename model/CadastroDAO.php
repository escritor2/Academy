<?php
require_once __DIR__ . '/Usuario.php';

class CadastroDAO {
    private $usuarios = [];
    private $arquivo = 'Usuario.json';

    public function __construct() {
        if (file_exists($this->arquivo)) {
            $dados = file_get_contents($this->arquivo);
            $usuariosArray = json_decode($dados, true);
            
            if ($usuariosArray) {
                foreach ($usuariosArray as $email => $info) {
                    $this->usuarios[$email] = new Usuario(
                        $info['nome'],
                        $info['email'],
                        $info['senha'],
                        $info['tipo']
                    );
                }
            }
        }
    }

    private function salvar() {
        $dados = [];
        foreach ($this->usuarios as $email => $usuario) {
            $dados[$email] = [
                'nome' => $usuario->getNome(),
                'email' => $usuario->getEmail(),
                'senha' => $usuario->getSenha(),
                'tipo' => $usuario->getTipo()
            ];
        }
        file_put_contents($this->arquivo, json_encode($dados, JSON_PRETTY_PRINT));
    }

    public function criarUsuario(Usuario $usuario) {
        if (isset($this->usuarios[$usuario->getEmail()])) {
            return false; // Usuário já existe
        }
        
        $this->usuarios[$usuario->getEmail()] = $usuario;
        $this->salvar();
        return true;
    }

    public function autenticar($email, $senha) {
        if (isset($this->usuarios[$email]) && $this->usuarios[$email]->getSenha() === $senha) {
            return $this->usuarios[$email];
        }
        return false;
    }

    public function buscarPorEmail($email) {
        return isset($this->usuarios[$email]) ? $this->usuarios[$email] : null;
    }
}
?>