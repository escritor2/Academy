<?php
function homeControl(){
    ob_start();
    $maneiroCara = "Techfit";
    $title = 'Home';
    $content = <<<HTML
    <h1>Página inicial</h1>
    
HTML;
    require __DIR__ . '/../view/templates/a.php';
    $output = ob_get_clean();
    echo $output;

}