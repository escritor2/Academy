<?php
require_once __DIR__ . '/../Model/CadastroDAO.php';
require_once __DIR__ . '/../Model/Usuario.php';

class CadastroController {
    private $dao;

    public function __construct() {
        $this->dao = new CadastroDAO();
    }

    public function criarUsuario($nome, $email, $senha, $tipo) {
        $usuario = new Usuario($nome, $email, $senha, $tipo);
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