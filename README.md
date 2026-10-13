# Chico Rei Store

Teste técnico Full Stack: uma API em **Laravel** e um sistema web em **Vue.js** que a consome, do login ao CRUD de produtos.

- [Visão geral e stack](#visão-geral-e-stack)
- [Como rodar](#como-rodar)
- [Banco de dados: migrations, seeders e importação](#banco-de-dados-migrations-seeders-e-importação)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [API](#api)
- [Interface web](#interface-web)
- [Decisões técnicas e trade-offs](#decisões-técnicas-e-trade-offs)
- [O que ficou de fora e o que eu faria diferente](#o-que-ficou-de-fora-e-o-que-eu-faria-diferente)
- [Solução de problemas](#solução-de-problemas)

## Visão geral e stack

Loja de produtos com listagem pública (busca, filtros, ordenação e paginação), página de detalhe e área restrita a usuários autenticados para criar, editar e excluir produtos, com histórico de auditoria.

| Camada | Tecnologia | Versão |
|---|---|---|
| Back-end | PHP | 8.4 (Docker) / mínimo 8.3 |
| | Laravel | 13.35 |
| | Laravel Sanctum (SPA) e Fortify (login) | 4.3 / 1.41 |
| | MySQL | 8.4 |
| | Elasticsearch (opcional) | 9.2, client PHP 9.5 |
| Front-end | Node.js | 24 (mínimo 22.18) |
| | Vue | 3.5 |
| | Vuetify, Vue Router, Pinia, Axios | 4.2, 5.4, 4.0, 1.20 |
| | Vite, TypeScript | 8.3, 6.0 |
| Infra | Docker Compose | api, queue, web, mysql, elasticsearch |

**Credenciais de teste** (criadas pelo seeder): `test@example.com` / `password`

### Estrutura do repositório

```
chico-rei-api/   API Laravel
chico-rei-web/   Aplicação Vue
docker/          Scripts de inicialização do MySQL
docker-compose.yml
```

### Checklist do enunciado

| Requisito | Onde |
|---|---|
| MySQL, FormRequests, respostas e erros padronizados | `app/Http/Requests`, `app/Http/Resources`, `app/Exceptions/ApiErrorResponse.php` |
| Login; só autenticados criam, editam e excluem | Fortify + `auth:sanctum` em `routes/api.php` |
| CRUD, paginação configurável, busca, filtros (preço, categoria, estoque), ordenação, detalhe | `ProductController`, `ProductFilter` |
| Logs de criação, atualização e exclusão | `ProductObserver` → `LogProductChangeJob` → tabela `product_logs` |
| Migrations e seeders | `database/migrations`, `database/seeders` |
| Front componentizado, loading/vazio/erro, responsivo | `chico-rei-web/src` |
| Diferenciais feitos | Docker, logs em job assíncrono, Elasticsearch, importação por API pública, testes automatizados (back-end) |
| Diferenciais não feitos | Sentry (veja [O que ficou de fora](#o-que-ficou-de-fora-e-o-que-eu-faria-diferente)) |

## Como rodar

### Com Docker (recomendado)

Pré-requisito: Docker com Compose. Nada mais precisa estar instalado.

```bash
cp chico-rei-api/.env.example chico-rei-api/.env
docker compose up -d --build
docker compose exec api php artisan key:generate
docker compose exec api php artisan db:seed
```

A primeira subida demora alguns minutos: a imagem compila a extensão GD e o Composer instala as dependências. Acompanhe com `docker compose logs -f api` até aparecer `Server running`.

Ao subir, a API já executa as migrations e cria o link `public/storage`. O comando `db:seed` cria o usuário de teste, os 24 produtos de exemplo e indexa-os no Elasticsearch.

| Serviço | Endereço | Função |
|---|---|---|
| Front-end | http://localhost:5173 | Aplicação Vue (Vite) |
| API | http://localhost:8000/api | Laravel |
| MySQL | localhost:3307 | usuário `chico_rei`, senha `secret` |
| Elasticsearch | http://localhost:9200 | Busca por nome |
| `queue` | – | Worker que grava os logs e indexa a busca |

Comandos úteis:

```bash
docker compose exec api php artisan test              # testes automatizados
docker compose exec api php artisan products:reindex  # recria o índice de busca
docker compose exec api php artisan products:import   # importa produtos de uma API pública
docker compose logs -f queue                          # acompanha os jobs
docker compose down                                   # para tudo (use -v para apagar os dados)
```

### Sem Docker

Requisitos: PHP 8.3+ com as extensões `gd` (com JPEG e WebP), `pdo_mysql`, `zip`, `mbstring` e `fileinfo`; Composer 2; MySQL 8; Node 22.18+ ou 24+. O Elasticsearch é opcional.

**Back-end**

```bash
cd chico-rei-api
composer install
cp .env.example .env
php artisan key:generate
```

No `.env`, ajuste `DB_HOST=127.0.0.1` (e `DB_PORT` se necessário) e crie o banco `chico_rei`. Depois:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve          # http://localhost:8000
php artisan queue:work     # em outro terminal: logs de auditoria e indexação
```

Sem Elasticsearch, a busca por nome usa `LIKE` no MySQL automaticamente. Para usá-lo, aponte `ELASTICSEARCH_HOST` para o servidor e rode `php artisan products:reindex`.

**Front-end**

```bash
cd chico-rei-web
npm install
npm run dev                # http://localhost:5173
```

O front lê `VITE_API_URL` (padrão `http://localhost:8000/api`). O login usa cookies do Sanctum, então o front precisa estar em `localhost:5173`, valor de `SANCTUM_STATEFUL_DOMAINS`, ou você deve alterar essa variável.

### Testes

```bash
docker compose exec api php artisan test
```

São 56 testes (PHPUnit) que cobrem autenticação, CRUD, filtros, busca com fallback, upload de imagem, logs de auditoria, seeders e o comando de reindexação. Eles usam o banco separado `chico_rei_testing`, forçado em `tests/TestCase.php` para nunca tocar nos dados de desenvolvimento, e simulam o Elasticsearch. O banco é criado por `docker/mysql/init` quando o volume do MySQL é criado. Se o seu volume já existia, crie-o uma vez:

```bash
docker compose exec mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS chico_rei_testing; GRANT ALL ON chico_rei_testing.* TO 'chico_rei'@'%';"
```

O front-end não tem testes automatizados.

## Banco de dados: migrations, seeders e importação

- **Migrations** (`database/migrations`): usuários, sessões, cache, jobs, tokens do Sanctum, `products` e `product_logs`.
- **Seeders**: `DatabaseSeeder` cria o usuário de teste e chama o `ProductSeeder`, com 24 produtos fixos em 10 categorias, alguns sem estoque para exercitar o filtro. Os dados não dependem de internet. Ao final, o seeder reindexa os produtos no Elasticsearch e, se ele estiver fora do ar, só avisa e segue. Rodar `db:seed` duas vezes não duplica dados.

### Importação de produtos (opcional)

`php artisan products:import {limit?}` busca produtos da API pública [Fake Store API](https://fakestoreapi.com/products) (`/products?limit=N`).

Regra adotada:
- Cada produto é gravado com `updateOrCreate` pelo **mesmo `id` da API de origem**: se já existe um produto local com aquele id, ele é **sobrescrito**; se não, é criado.
- Mapeamento: `title` → `name`, e `description`, `image`, `price` e `category` copiados como vêm.
- A API de origem não tem estoque, então `stock` recebe um valor aleatório entre 0 e 100.
- O limite máximo pretendido é 100 e o padrão é 20.

Como a importação usa os ids da origem, ela é indicada para um banco ainda vazio. Em um banco já semeado com ids de 1 a 20, ela substituiria esses produtos.

## Variáveis de ambiente

O modelo está em `chico-rei-api/.env.example`. No Docker, as variáveis de banco e de Elasticsearch também são definidas no `docker-compose.yml` e prevalecem sobre o arquivo. O `.env.example` da raiz só personaliza as credenciais do MySQL no Compose (os valores padrão já funcionam).

| Variável | Padrão | Uso |
|---|---|---|
| `APP_KEY` | vazio | Gerada com `php artisan key:generate` |
| `APP_URL` | `http://localhost:8000` | Base das URLs públicas das imagens |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | `mysql`, `mysql`, `3306`, `chico_rei`, `chico_rei`, `secret` | Conexão MySQL (`DB_HOST=127.0.0.1` fora do Docker) |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | `database` | Sessão, cache e fila ficam no MySQL |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost:5173` | Origem do front que usa login por cookie |
| `FRONTEND_URL` | `http://localhost:5173` | Origem permitida no CORS |
| `ELASTICSEARCH_HOST`, `ELASTICSEARCH_INDEX` | `http://elasticsearch:9200`, `products` | Busca (opcional) |
| `VITE_API_URL` (front) | `http://localhost:8000/api` | Endereço da API |

## API

Base: `http://localhost:8000/api`. Autenticação por sessão com cookies (Sanctum SPA): peça `GET /sanctum/csrf-cookie` antes do login.

| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| POST | `/login`, `/logout` | público / logado | Autenticação (Fortify) |
| GET | `/user` | logado | Usuário atual |
| GET | `/products` | público | Listagem com filtros |
| GET | `/products/{id}` | público | Detalhe completo |
| POST | `/products` | logado | Cria (JSON ou `multipart/form-data`) |
| PUT/PATCH | `/products/{id}` | logado | Atualiza. Com arquivo, envie `POST` com `_method=PUT` |
| DELETE | `/products/{id}` | logado | Exclui |
| GET | `/product-logs` | logado | Histórico de auditoria |

**Filtros de `/products`:** `name` (busca), `category`, `min_price`, `max_price`, `in_stock` (`1`/`0`), `sort` (`name`, `price`, `stock`, `created_at`), `direction` (`asc`/`desc`), `per_page` (1 a 100, padrão 15) e `page`. Sem `sort`, uma busca por nome vem em ordem de relevância e a listagem normal, do mais recente.

**Filtros de `/product-logs`:** `action` (`created`, `updated`, `deleted`), `product_id`, `name`, `per_page` e `page`.

**Formato das respostas**
- Item: `{ "data": { ... } }`. Lista: `{ "data": [...], "links": {...}, "meta": {...} }`.
- Erro: `{ "message": "...", "errors": { "campo": ["..."] } }`, com `errors` apenas em validação (422). O mesmo formato vale para 401, 403, 404, 429 e 5xx, definido em `ApiErrorResponse`.

**Imagens:** aceita JPG, PNG e WebP de até 2 MB. O servidor reduz o maior lado a 1200 px e converte para WebP. A atualização aceita `remove_image` para limpar a imagem. Imagens com URL externa (seeder e importação) são exibidas como estão e nunca apagadas do disco.

## Interface web

| Rota (navegador) | Tela | Acesso |
|---|---|---|
| `/produtos` | Listagem em grade, com filtros, busca, ordenação e paginação | público |
| `/produtos/:id` | Detalhe do produto | público |
| `/entrar` | Login | visitantes |
| `/produtos/novo` | Cadastro | logado |
| `/produtos/:id/editar` | Edição | logado |
| `/historico` | Histórico de alterações dos produtos | logado |

As URLs são em português e os nomes de rota e o código, em inglês. Os filtros ficam na query string (`/produtos?name=camiseta&max_price=100&page=2`): a URL é compartilhável e o botão voltar funciona. Há estados de carregamento, vazio e erro em todas as telas, o layout é responsivo, e o formulário valida em português e marca os campos obrigatórios com `*`.

Organização de `chico-rei-web/src`:

```
views/        uma tela por rota; orquestram os componentes
components/   interface reutilizável (props e eventos, sem chamar a API)
composables/  useRouteQueryList: lista paginada sincronizada com a URL
stores/       Pinia: usuário autenticado e avisos
services/     acesso à API (axios), uma função por endpoint
utils/        funções puras: query string, formatação, erros
types/        tipos TypeScript das respostas da API
router/       rotas e proteção de acesso
```

## Decisões técnicas e trade-offs

**Back-end**
- **Camadas:** `Controller` → `FormRequest` (validação) → `Service` (regra de negócio) → `Model`. Filtros ficam em `ProductFilter` e a apresentação em `Resource`.
- **Respostas e erros:** um único formato de erro é gerado em um só lugar (`ApiErrorResponse`).
- **Auditoria assíncrona:** o `ProductObserver` despacha `LogProductChangeJob`. O usuário é capturado no despacho, porque o worker não tem sessão, e o job recebe só dados primitivos, porque o produto pode já ter sido excluído. `product_logs` não tem foreign key em `product_id`, para o log sobreviver à exclusão. Custo: sem o worker rodando, os logs não são gravados.
- **Busca com Elasticsearch opcional:** ele decide *quais* produtos casam com o nome (analisador em português, sem acento, tolerante a erros de digitação) e devolve até 1000 ids. O MySQL aplica os demais filtros, a ordenação e a paginação. Se o Elasticsearch cair, há um timeout de 2 s, um aviso no log e um fallback para `LIKE`, que dura 30 s para não pagar timeout a cada requisição. Custo: dois lugares com dados de produto, mantidos em sincronia pelo observer e pelo `products:reindex`. Operações em massa que não passam pelo Eloquent não disparam o observer.
- **Imagens:** o `ProductService` guarda, otimiza (GD) e apaga os arquivos. A otimização roda dentro da requisição de upload, o que é simples, mas bloqueia por alguns milissegundos.
- **Banco de testes MySQL** (e não SQLite): a ordenação por relevância usa `FIELD()`, e os testes rodam no mesmo motor da aplicação.

**Front-end**
- **Estado na URL:** em vez de estado local, os filtros vivem na query string. O composable `useRouteQueryList` cancela a requisição anterior (`AbortController`) a cada mudança, evitando respostas fora de ordem.
- **Pinia só para o que é global:** usuário autenticado e avisos.
- **Vuetify importado por inteiro:** o bundle de produção fica em torno de 685 kB (cerca de 218 kB comprimido). É o custo de não configurar tree-shaking.

**Ambiente**
- **Desempenho no Windows:** a pasta `vendor` da API fica em um volume nomeado e o OPcache é ligado na imagem. Sem isso, o Docker Desktop no Windows levava cerca de 4 s por requisição (agora cerca de 0,13 s), porque lê milhares de arquivos de uma pasta montada lenta. Efeito: o `vendor` da sua pasta local não é mais atualizado pelo container.
- **Servidor:** `php artisan serve` e o servidor do Vite são para desenvolvimento. Não há configuração de produção (nginx + php-fpm, build estático).

## O que ficou de fora e o que eu faria diferente

- **Sentry:** não integrado.
- **Testes do front-end:** não escritos (Vitest + Vue Test Utils seriam o caminho). Só o back-end tem testes.
- **Categorias:** o filtro de categoria é um campo de texto com comparação exata, porque o enunciado pede o filtro, mas não uma lista de categorias. Um `select` alimentado pelas categorias existentes seria melhor.
- **Mensagens de validação em inglês:** o front valida em português antes de enviar, mas erros que só o servidor detecta chegam em inglês (`APP_LOCALE=en`). Faltam os arquivos de idioma `pt_BR`.
- **Permissões:** qualquer usuário autenticado gerencia qualquer produto; não há papéis.
- **Otimização de imagem em job**, com miniaturas para a listagem, em vez de gerar uma versão única na requisição.
- **Importação:** o comando usa o `id` da origem como id local (ver acima). O ideal seria guardar um identificador externo separado, e o limite padrão não é aplicado quando o argumento é omitido.
- **Bundle:** configurar tree-shaking do Vuetify para reduzir o JavaScript inicial.
- **Retenção de logs:** `product_logs` cresce sem limite; faltaria uma política de expiração.
- **CI** executando os testes e o type-check a cada commit.

## Solução de problemas

- **Porta em uso:** o front usa 5173, a API 8000, o MySQL 3307 e o Elasticsearch 9200. Pare o processo que ocupa a porta ou altere o mapeamento no `docker-compose.yml`.
- **Elasticsearch não sobe no Linux:** rode `sudo sysctl -w vm.max_map_count=262144`.
- **Lentidão no Windows:** mantenha o repositório em um disco local e use o Docker Desktop com WSL 2. O `vendor` e o `node_modules` já ficam em volumes nativos.
- **Autocompletar da IDE:** para a IDE enxergar as dependências, rode `npm install` em `chico-rei-web` e `composer install --ignore-platform-reqs` em `chico-rei-api` (o Composer local pode ter um PHP menor que o exigido).
- **Imagens não aparecem:** confirme que o link existe: `docker compose exec api php artisan storage:link`.
- **Logs ou busca não atualizam:** verifique se o serviço `queue` está rodando (`docker compose ps`).
