<?php
require_once __DIR__ . '/Produto.php';

class ProdutoDAO {
    private $produtos = [];
    private $arquivo = 'Produto.json';

    public function __construct() {
        if (file_exists($this->arquivo)) {
            $dados = file_get_contents($this->arquivo);
            $produtosArray = json_decode($dados, true);
            
            if ($produtosArray) {
                foreach ($produtosArray as $nome => $info) {
                    $this->produtos[$nome] = new Produto(
                        $info['nome'],
                        $info['categoria'],
                        $info['preco'],
                        $info['estoque']
                    );
                }
            }
        }
    }

    private function salvar() {
        $dados = [];
        foreach ($this->produtos as $nome => $produto) {
            $dados[$nome] = [
                'nome' => $produto->getNome(),
                'categoria' => $produto->getCategoria(),
                'preco' => $produto->getPreco(),
                'estoque' => $produto->getEstoque()
            ];
        }
        file_put_contents($this->arquivo, json_encode($dados, JSON_PRETTY_PRINT));
    }

    public function criarProduto(Produto $produto) {
        $this->produtos[$produto->getNome()] = $produto;
        $this->salvar();
    }

    public function lerProdutos() {
        return $this->produtos;
    }

    public function atualizarProduto($nome, $categoria, $preco, $estoque) {
        if (isset($this->produtos[$nome])) {
            $this->produtos[$nome]->setCategoria($categoria);
            $this->produtos[$nome]->setPreco($preco);
            $this->produtos[$nome]->setEstoque($estoque);
            $this->salvar();
        }
    }

    public function excluirProduto($nome) {
        unset($this->produtos[$nome]);
        $this->salvar();
    }
}
?>