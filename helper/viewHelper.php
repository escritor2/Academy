<?php 
    function render($view, $titulo, $data = []){
        extract($data);
        $title = $titulo;
        ob_start();
        require __DIR__ . "/../view/$view.php";
        $conteudo = ob_get_clean();
        require __DIR__ . "/../view/templates/base.php";
    }