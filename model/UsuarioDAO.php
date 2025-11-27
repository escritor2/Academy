<?php
Class UsuarioDAO{
    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }
    public function lerUsuario(){
        $sql = $this->pdo->prepare("SELECT * FROM usuarios");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    public function criarUsuario(Usuario $usuario){
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
        $stmt->bindValue(':nome', $usuario->getNome());
        $stmt->bindValue(':email', $usuario->getEmail());
        $stmt->bindValue(':senha', md5($usuario->getSenha()));
        $stmt->bindValue(':tipo', $usuario->getTipo());
        return $stmt->execute();
    }

    public function autenticar($email, $senha){
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario && password_verify($senha, $usuario['senha'])){
            return new Usuario($usuario['nome'], $usuario['email'], $usuario['senha'], $usuario['tipo']);
        }
        return null;
    }

    public function buscarPorEmail($email){
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if($usuario){
            return new Usuario($usuario['nome'], $usuario['email'], $usuario['senha'], $usuario['tipo']);
        }
        return null;
    }
}