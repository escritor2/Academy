<?php
// controllers/AlunoController.php

// Importamos as configurações e models necessários
require_once '../config/Database.php';
require_once '../Model/Treino.php';

class AlunoController {

    public function index() {
        // 1. Inicia a sessão (Obrigatório para login)
        session_start(); 

        // --- MODO DE TESTE (TEMPORÁRIO) ---
        // Se não tiver ninguém logado, fingimos que é o Aluno de ID 1
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1; 
            $_SESSION['user_nome'] = "Aluno Teste";
        }
        // ----------------------------------

        // 2. Conecta ao Banco
        $database = new Database();
        $db = $database->getConnection();
        
        // 3. Instancia o Model de Treino
        $treinoModel = new Treino($db); 

        // 4. Busca os treinos do aluno logado (ID da sessão)
        $treinosBrutos = $treinoModel->lerPorAluno($_SESSION['user_id']); 

        // 5. Organiza os dados para o formato A, B, C
        $treinosOrganizados = [
            'A' => [],
            'B' => [],
            'C' => []
        ];

        foreach ($treinosBrutos as $exercicio) {
            $tipo = $exercicio['tipo']; // ex: 'A'
            
            // Só adiciona se o tipo for válido (A, B ou C)
            if(isset($treinosOrganizados[$tipo])) {
                $treinosOrganizados[$tipo][] = [
                    'name' => $exercicio['nome'],
                    'equip' => $exercicio['equipamento'],
                    'sets' => $exercicio['series'],
                    'reps' => $exercicio['repeticoes'],
                    'weight' => $exercicio['carga'],
                    'done' => false
                ];
            }
        }

        // 6. Carrega a View (HTML)
        // As variáveis criadas aqui ($treinosOrganizados) estarão disponíveis lá
        include '../views/aluno/painel.php';
    }
}
?>