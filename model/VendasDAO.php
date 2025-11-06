<?php
require_once __DIR__ . '/Venda.php';

class VendasDAO {
    private $vendas = [];
    private $arquivo = 'Venda.json';

    public function __construct() {
        if (file_exists($this->arquivo)) {
            $dados = file_get_contents($this->arquivo);
            $vendasArray = json_decode($dados, true);
            
            if ($vendasArray) {
                foreach ($vendasArray as $id => $info) {
                    $this->vendas[$id] = new Venda(
                        $info['produto'],
                        $info['cliente'],
                        $info['quantidade'],
                        $info['valorTotal'],
                        $info['data']
                    );
                }
            }
        }
    }

    private function salvar() {
        $dados = [];
        foreach ($this->vendas as $id => $venda) {
            $dados[$id] = [
                'produto' => $venda->getProduto(),
                'cliente' => $venda->getCliente(),
                'quantidade' => $venda->getQuantidade(),
                'valorTotal' => $venda->getValorTotal(),
                'data' => $venda->getData()
            ];
        }
        file_put_contents($this->arquivo, json_encode($dados, JSON_PRETTY_PRINT));
    }

    public function criarVenda(Venda $venda) {
        $id = uniqid();
        $venda->setId($id);
        $this->vendas[$id] = $venda;
        $this->salvar();
        return $id;
    }

    public function lerVendas() {
        return $this->vendas;
    }

    public function buscarPorId($id) {
        return isset($this->vendas[$id]) ? $this->vendas[$id] : null;
    }

    public function excluirVenda($id) {
        unset($this->vendas[$id]);
        $this->salvar();
    }
}
?>