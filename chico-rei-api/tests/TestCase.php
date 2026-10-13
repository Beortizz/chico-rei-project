<?php

namespace Tests;

use App\Services\ProductSearchService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    private const string TESTING_DATABASE = 'chico_rei_testing';

    protected MockInterface $searchService;

    /**
     * Força o banco de testes na configuração da aplicação. As variáveis de ambiente do
     * container (DB_DATABASE) têm precedência sobre o phpunit.xml, e o RefreshDatabase
     * executa migrate:fresh, o que apagaria os dados de desenvolvimento.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $app['config']->set("database.connections.{$connection}.database", self::TESTING_DATABASE);

        return $app;
    }

    /**
     * Substitui o Elasticsearch por um mock para que os testes não dependam do serviço no ar.
     * Sem expectativa definida, search() devolve null e o filtro usa o fallback do MySQL.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = $this->mock(ProductSearchService::class)->shouldIgnoreMissing();
    }
}
