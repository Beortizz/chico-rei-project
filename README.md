# Chico Rei Store

![Status](https://status.laravieira.me/api/badge/59/status)

**Site:** https://chico-rei.beortizz.com

Teste técnico Full Stack: uma API em **Laravel** e um sistema web em **Vue.js** que a consome, do login ao CRUD de produtos.

Listagem pública (busca, filtros, ordenação e paginação), página de detalhe e área restrita a usuários autenticados para criar, editar e excluir produtos, com histórico de auditoria.

## Stack

| Camada | Tecnologia | Versão |
|---|---|---|
| Back-end | PHP | 8.4 (Docker), mínimo 8.3 |
| | Laravel, Sanctum, Fortify | 13.35, 4.3, 1.41 |
| | MySQL | 8.4 |
| | Elasticsearch (opcional) | 9.2 |
| Front-end | Node.js | 24 (mínimo 22.18) |
| | Vue, Vuetify, Vue Router, Pinia, Axios | 3.5, 4.2, 5.4, 4.0, 1.20 |
| | Vite, TypeScript | 8.3, 6.0 |

**Credenciais de teste** (criadas pelo seeder): `test@example.com` / `password`

## Checklist do teste técnico

**Requisitos obrigatórios**
- [x] Back-end Laravel com MySQL
- [x] Validação com FormRequests
- [x] Respostas da API padronizadas
- [x] Tratamento de erros consistente
- [x] Front-end Vue componentizado
- [x] Repositório Git com histórico de commits
- [x] README com instruções de instalação

**Autenticação e acesso**
- [x] Login
- [x] Só usuários autenticados criam, editam e excluem produtos
- [x] Visitantes têm acesso apenas à listagem e à visualização

**API de produtos**
- [x] CRUD completo, com os campos sugeridos
- [x] Paginação configurável, busca por nome, filtros de preço, categoria e estoque, e ordenação
- [x] Endpoint dedicado para o detalhe do produto
- [x] Logs de criação, atualização e exclusão

**Interface web**
- [x] Tela de login
- [x] Listagem com filtros, busca, paginação configurável e ordenação
- [x] Tela de visualização do produto
- [x] Criação, edição e exclusão restritas a usuários autenticados
- [x] Estados de carregamento, vazio e erro, e layout responsivo

**Dados**
- [x] Migrations e seeders com produtos de exemplo
- [x] Importação de produtos de uma API pública (opcional)

**Diferenciais**
- [x] Docker
- [x] Logs em job assíncrono
- [x] Elasticsearch
- [x] Testes automatizados no back-end
- [ ] Integração com Sentry
- [ ] Testes automatizados no front-end

## Como rodar

### Com Docker (recomendado)

```bash
cp chico-rei-api/.env.example chico-rei-api/.env
docker compose up -d --build
docker compose exec api php artisan key:generate
docker compose exec api php artisan db:seed
```

A primeira subida demora alguns minutos (compila a extensão GD e instala as dependências). Acompanhe com `docker compose logs -f api` até aparecer `Server running`. Ao subir, a API já roda as migrations e cria o link `public/storage`.

| Serviço | Endereço |
|---|---|
| Front-end | http://localhost:5173 |
| API | http://localhost:8000/api |
| MySQL | localhost:3307 (`chico_rei` / `secret`) |
| Elasticsearch | http://localhost:9200 |
| `queue` | worker que grava os logs de auditoria e indexa a busca |

```bash
docker compose exec api php artisan test              # testes automatizados
docker compose exec api php artisan products:reindex  # recria o índice de busca
docker compose exec api php artisan products:import   # importa produtos da Fake Store API
docker compose down                                   # para tudo (-v apaga os dados)
```

### Sem Docker

Requisitos: PHP 8.3+ com `gd` (JPEG e WebP), `pdo_mysql`, `zip`, `mbstring` e `fileinfo`; Composer 2; MySQL 8; Node 22.18+. O Elasticsearch é opcional.

```bash
cd chico-rei-api
composer install
cp .env.example .env          # ajuste DB_HOST=127.0.0.1 e crie o banco chico_rei
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve             # http://localhost:8000
php artisan queue:work        # outro terminal: logs de auditoria e indexação
```

```bash
cd chico-rei-web
npm install
npm run dev                   # http://localhost:5173
```

Sem Elasticsearch, a busca por nome usa `LIKE` no MySQL automaticamente. Para usá-lo, aponte `ELASTICSEARCH_HOST` para o servidor e rode `php artisan products:reindex`. O login usa cookies do Sanctum, então o front deve rodar em `localhost:5173` (valor de `SANCTUM_STATEFUL_DOMAINS`).

### Testes

São 56 testes (PHPUnit) de autenticação, CRUD, filtros, busca com fallback, upload de imagem, auditoria, seeders e reindexação. Rodam no banco separado `chico_rei_testing`, criado por `docker/mysql/init` junto com o volume do MySQL, com o Elasticsearch simulado. O front-end não tem testes automatizados.

## Banco de dados

- **Migrations:** usuários, sessões, cache, jobs, tokens do Sanctum, `products` e `product_logs`.
- **Seeders:** o `DatabaseSeeder` cria o usuário de teste e chama o `ProductSeeder`, com 24 produtos fixos (sem depender de internet), alguns sem estoque para exercitar o filtro. Ao final reindexa no Elasticsearch; se ele estiver fora do ar, só avisa. Rodar `db:seed` de novo não duplica dados.

### Importação de produtos (opcional)

`php artisan products:import {limit?}` busca produtos da [Fake Store API](https://fakestoreapi.com/products) e **cria um registro novo para cada item**, com id próprio. Um produto que já exista com o mesmo nome e categoria **não é importado de novo** (é mantido como está).

- Mapeamento: `title` → `name`; `description`, `image`, `price` e `category` são copiados como vêm.
- A origem não tem estoque, então `stock` recebe um valor aleatório entre 0 e 100.
- Limite: o padrão é 20 e o máximo é 100. Se omitido ou acima do máximo, o comando avisa e usa 20.

## Variáveis de ambiente

O modelo está em `chico-rei-api/.env.example`. No Docker, as variáveis de banco e de Elasticsearch também vêm do `docker-compose.yml` e prevalecem sobre o arquivo.

| Variável | Padrão | Uso |
|---|---|---|
| `APP_KEY` | vazio | Gerada com `php artisan key:generate` |
| `APP_URL` | `http://localhost:8000` | Base das URLs públicas das imagens |
| `APP_LOCALE` | `pt_BR` | Idioma das mensagens de validação (arquivos em `lang/pt_BR`) |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | `mysql`, `3306`, `chico_rei`, `chico_rei`, `secret` | MySQL (`DB_HOST=127.0.0.1` fora do Docker) |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | `database` | Sessão, cache e fila ficam no MySQL |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost:5173` | Origem do front (login por cookie) |
| `FRONTEND_URL` | `http://localhost:5173` | Origem permitida no CORS |
| `ELASTICSEARCH_HOST`, `ELASTICSEARCH_INDEX` | `http://elasticsearch:9200`, `products` | Busca (opcional) |
| `VITE_API_URL` (front) | `http://localhost:8000/api` | Endereço da API |

## API

Base `http://localhost:8000/api`. Autenticação por sessão com cookies (Sanctum SPA): chame `GET /sanctum/csrf-cookie` antes do login.

| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| POST | `/login`, `/logout` | público / logado | Autenticação (Fortify) |
| GET | `/user` | logado | Usuário atual |
| GET | `/products` | público | Listagem com filtros |
| GET | `/products/{id}` | público | Detalhe completo |
| POST | `/products` | logado | Cria (JSON ou `multipart/form-data`) |
| PUT/PATCH | `/products/{id}` | logado | Atualiza (com arquivo, use `POST` com `_method=PUT`) |
| DELETE | `/products/{id}` | logado | Exclui |
| GET | `/product-logs` | logado | Histórico de auditoria |

- **Filtros de `/products`:** `name`, `category`, `min_price`, `max_price`, `in_stock` (`1`/`0`), `sort` (`name`, `price`, `stock`, `created_at`), `direction`, `per_page` (1 a 100, padrão 15) e `page`.
- **Filtros de `/product-logs`:** `action` (`created`, `updated`, `deleted`), `product_id`, `name`, `per_page` e `page`.
- **Respostas:** item `{ "data": {...} }`; lista `{ "data": [...], "links": {...}, "meta": {...} }`; erro `{ "message": "...", "errors": { "campo": ["..."] } }`, com `errors` só em validação (422). O formato de erro é único para 401, 403, 404, 429 e 5xx (`ApiErrorResponse`).
- **Imagens:** JPG, PNG ou WebP de até 2 MB. O servidor reduz o maior lado a 1200 px e converte para WebP. `remove_image` na atualização limpa a imagem. URLs externas (seeder e importação) são exibidas como estão e nunca apagadas do disco.

## Interface web

| Rota | Tela | Acesso |
|---|---|---|
| `/produtos` | Listagem em grade com filtros, busca, ordenação e paginação | público |
| `/produtos/:id` | Detalhe | público |
| `/entrar` | Login | visitantes |
| `/produtos/novo`, `/produtos/:id/editar` | Cadastro e edição | logado |
| `/historico` | Histórico de alterações | logado |

Os filtros ficam na query string (`/produtos?name=camiseta&max_price=100&page=2`), então a URL é compartilhável e o botão voltar funciona.

```
chico-rei-web/src
  views/        uma tela por rota
  components/   interface reutilizável (props e eventos, sem chamar a API)
  composables/  useRouteQueryList: lista paginada sincronizada com a URL
  stores/       Pinia: usuário autenticado e avisos
  services/     acesso à API (axios), uma função por endpoint
  utils/        query string, formatação, tratamento de erros
  router/       rotas e proteção de acesso
```

## Decisões técnicas

- **Camadas no back-end:** `Controller` → `FormRequest` (validação) → `Service` (regra de negócio) → `Model`. Filtros em `ProductFilter` e apresentação em `Resource`.
- **Auditoria assíncrona:** o `ProductObserver` despacha `LogProductChangeJob`. O usuário é capturado no despacho, porque o worker não tem sessão, e o job recebe só dados primitivos, porque o produto pode já ter sido excluído. `product_logs` não tem foreign key em `product_id`, para o log sobreviver à exclusão. Sem o worker rodando, os logs não são gravados.
- **Busca com Elasticsearch opcional:** ele decide quais produtos casam com o nome (analisador em português, sem acento, tolerante a erros de digitação) e devolve até 1000 ids; o MySQL aplica os demais filtros, a ordenação e a paginação. Se o Elasticsearch cair, há timeout de 2 s, aviso no log e fallback para `LIKE`, mantido por 30 s para não pagar timeout a cada requisição. Operações em massa que não passam pelo Eloquent não disparam o observer; use `products:reindex` depois delas.
- **Imagens:** o `ProductService` guarda, otimiza (GD) e apaga os arquivos, dentro da requisição de upload.
- **Testes em MySQL:** a ordenação por relevância usa `FIELD()`, então os testes rodam no mesmo motor da aplicação.
- **Estado na URL no front:** o `useRouteQueryList` cancela a requisição anterior (`AbortController`) a cada mudança de filtro, evitando respostas fora de ordem. O Pinia guarda só o que é global.
- **Servidores de desenvolvimento:** `php artisan serve` e o Vite; não há configuração de produção.

## Solução de problemas

- **Porta em uso:** o front usa 5173, a API 8000, o MySQL 3307 e o Elasticsearch 9200. Libere a porta ou altere o mapeamento no `docker-compose.yml`.
- **Elasticsearch não sobe no Linux:** `sudo sysctl -w vm.max_map_count=262144`.
- **Imagens não aparecem:** `docker compose exec api php artisan storage:link`.
- **Logs ou busca não atualizam:** confirme que o serviço `queue` está rodando (`docker compose ps`).
