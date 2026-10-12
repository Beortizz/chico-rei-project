<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Dados fixos para a aplicação funcionar em máquina limpa, sem depender de internet.
     * Inclui itens sem estoque para exercitar o filtro de disponibilidade.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Camiseta Básica Algodão', 'description' => 'Camiseta de algodão penteado, corte reto e toque macio.', 'image' => 'https://picsum.photos/seed/camiseta-basica-algodao/600/600', 'price' => 59.9, 'category' => 'Camisetas', 'stock' => 120],
            ['name' => 'Camiseta Oversized Estampada', 'description' => 'Modelagem oversized com estampa frontal em silk.', 'image' => 'https://picsum.photos/seed/camiseta-oversized-estampada/600/600', 'price' => 89.9, 'category' => 'Camisetas', 'stock' => 45],
            ['name' => 'Camiseta Regata Fitness', 'description' => 'Regata leve em tecido dry fit para treino.', 'image' => 'https://picsum.photos/seed/camiseta-regata-fitness/600/600', 'price' => 49.9, 'category' => 'Camisetas', 'stock' => 0],
            ['name' => 'Camisa Social Slim', 'description' => 'Camisa social manga longa em tricoline com elastano.', 'image' => 'https://picsum.photos/seed/camisa-social-slim/600/600', 'price' => 159.9, 'category' => 'Camisas', 'stock' => 30],
            ['name' => 'Camisa Linho Manga Curta', 'description' => 'Camisa de linho respirável, ideal para dias quentes.', 'image' => 'https://picsum.photos/seed/camisa-linho-manga-curta/600/600', 'price' => 179.9, 'category' => 'Camisas', 'stock' => 18],
            ['name' => 'Camisa Xadrez Flanelada', 'description' => 'Camisa flanelada xadrez com bolsos frontais.', 'image' => 'https://picsum.photos/seed/camisa-xadrez-flanelada/600/600', 'price' => 139.9, 'category' => 'Camisas', 'stock' => 0],
            ['name' => 'Calça Jeans Reta', 'description' => 'Calça jeans de lavagem média, cintura média e corte reto.', 'image' => 'https://picsum.photos/seed/calca-jeans-reta/600/600', 'price' => 199.9, 'category' => 'Calças', 'stock' => 60],
            ['name' => 'Calça Jeans Skinny', 'description' => 'Jeans com elastano e acabamento desbotado.', 'image' => 'https://picsum.photos/seed/calca-jeans-skinny/600/600', 'price' => 209.9, 'category' => 'Calças', 'stock' => 38],
            ['name' => 'Calça Alfaiataria Preta', 'description' => 'Calça de alfaiataria com cós alto e vincos marcados.', 'image' => 'https://picsum.photos/seed/calca-alfaiataria-preta/600/600', 'price' => 249.9, 'category' => 'Calças', 'stock' => 22],
            ['name' => 'Calça Moletom Jogger', 'description' => 'Jogger em moletom flanelado com punhos canelados.', 'image' => 'https://picsum.photos/seed/calca-moletom-jogger/600/600', 'price' => 129.9, 'category' => 'Calças', 'stock' => 75],
            ['name' => 'Bermuda Sarja Masculina', 'description' => 'Bermuda de sarja com bolsos laterais e fechamento em botão.', 'image' => 'https://picsum.photos/seed/bermuda-sarja-masculina/600/600', 'price' => 119.9, 'category' => 'Bermudas', 'stock' => 52],
            ['name' => 'Bermuda Tactel Praia', 'description' => 'Bermuda tactel de secagem rápida com forro interno.', 'image' => 'https://picsum.photos/seed/bermuda-tactel-praia/600/600', 'price' => 79.9, 'category' => 'Bermudas', 'stock' => 0],
            ['name' => 'Vestido Midi Floral', 'description' => 'Vestido midi em viscose com estampa floral e decote V.', 'image' => 'https://picsum.photos/seed/vestido-midi-floral/600/600', 'price' => 229.9, 'category' => 'Vestidos', 'stock' => 27],
            ['name' => 'Vestido Curto Linho', 'description' => 'Vestido curto de linho com botões frontais.', 'image' => 'https://picsum.photos/seed/vestido-curto-linho/600/600', 'price' => 189.9, 'category' => 'Vestidos', 'stock' => 14],
            ['name' => 'Saia Plissada', 'description' => 'Saia midi plissada com cós elástico.', 'image' => 'https://picsum.photos/seed/saia-plissada/600/600', 'price' => 139.9, 'category' => 'Saias', 'stock' => 33],
            ['name' => 'Jaqueta Jeans Clássica', 'description' => 'Jaqueta jeans com botões metálicos e bolsos no peito.', 'image' => 'https://picsum.photos/seed/jaqueta-jeans-classica/600/600', 'price' => 299.9, 'category' => 'Jaquetas', 'stock' => 20],
            ['name' => 'Jaqueta Corta-Vento', 'description' => 'Jaqueta impermeável leve com capuz retrátil.', 'image' => 'https://picsum.photos/seed/jaqueta-corta-vento/600/600', 'price' => 279.9, 'category' => 'Jaquetas', 'stock' => 41],
            ['name' => 'Moletom Canguru', 'description' => 'Moletom com capuz e bolso frontal em malha felpada.', 'image' => 'https://picsum.photos/seed/moletom-canguru/600/600', 'price' => 169.9, 'category' => 'Moletons', 'stock' => 66],
            ['name' => 'Moletom Gola Careca', 'description' => 'Moletom básico sem capuz, interior macio.', 'image' => 'https://picsum.photos/seed/moletom-gola-careca/600/600', 'price' => 149.9, 'category' => 'Moletons', 'stock' => 5],
            ['name' => 'Boné Aba Curva', 'description' => 'Boné de sarja com regulagem traseira.', 'image' => 'https://picsum.photos/seed/bone-aba-curva/600/600', 'price' => 69.9, 'category' => 'Acessórios', 'stock' => 90],
            ['name' => 'Cinto Couro Legítimo', 'description' => 'Cinto de couro com fivela metálica.', 'image' => 'https://picsum.photos/seed/cinto-couro-legitimo/600/600', 'price' => 99.9, 'category' => 'Acessórios', 'stock' => 0],
            ['name' => 'Meia Cano Médio Kit 3', 'description' => 'Kit com 3 pares de meias de algodão.', 'image' => 'https://picsum.photos/seed/meia-cano-medio-kit-3/600/600', 'price' => 39.9, 'category' => 'Acessórios', 'stock' => 150],
            ['name' => 'Tênis Casual Branco', 'description' => 'Tênis casual de couro sintético com solado de borracha.', 'image' => 'https://picsum.photos/seed/tenis-casual-branco/600/600', 'price' => 249.9, 'category' => 'Calçados', 'stock' => 48],
            ['name' => 'Sandália Rasteira', 'description' => 'Sandália rasteira em couro com tira ajustável.', 'image' => 'https://picsum.photos/seed/sandalia-rasteira/600/600', 'price' => 119.9, 'category' => 'Calçados', 'stock' => 12],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['name' => $product['name']], $product);
        }
    }
}
