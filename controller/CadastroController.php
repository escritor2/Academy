<?php
require_once __DIR__ . '/../Model/CadastroDAO.php';
require_once __DIR__ . '/../Model/Usuario.php';

class CadastroController {
    private $dao;

    public function __construct() {
        $this->dao = new CadastroDAO();
    }

    public function criarUsuario($cpf, $nome, $email, $senha, $tipo='cliente') {
        $usuario = new Usuario($nome, $email, $senha, $tipo);
        switch ($tipo) {
            °
            case 'cliente':
                $aluno = new Aluno($nome,null,$cpf, "2025.$cpf");
                break;
            case 'instrutor':
                $usuario->setTipo('instrutor');
                break;
            default:
                $usuario->setTipo('cliente');
                break;
        }
        return $this->dao->criarUsuario($usuario);
    }

    public function autenticar($email, $senha) {
        return $this->dao->autenticar($email, $senha);
    }

    public function buscarPorEmail($email) {
        return $this->dao->buscarPorEmail($email);
    }
}
?>