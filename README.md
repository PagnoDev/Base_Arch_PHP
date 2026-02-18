# Base_Arch (Laravel + JWT + RBAC + Relatórios)

Projeto base em Laravel 12 com autenticação JWT, controle de acesso por perfil (`admin` e `user`) e relatórios de negócio (pedidos + financeiro) com consultas Eloquent complexas (`inner join`, agregações e métricas).

## Visão geral

Este projeto foi evoluído como base arquitetural e portfólio técnico para cenários de:

- autenticação e segurança
- administração de usuários
- relatórios com escopo por permissão
- modelagem de domínio de e-commerce e finanças

## Stack

- PHP 8.2+
- Laravel 12
- PostgreSQL
- Blade (server-side rendering)
- `firebase/php-jwt`

## Funcionalidades implementadas

### 1) Autenticação JWT

- Login e registro com validação robusta de senha
- Token JWT armazenado em cookie `HttpOnly` (`auth_token`)
- Middleware de autenticação para rotas protegidas

Regras de senha:

- mínimo 8 caracteres
- maiúsculas e minúsculas
- números
- caracteres especiais
- sem espaços

### 2) Roles e autorização (RBAC)

Papéis:

- `admin`
- `user`

Permissões:

- `admin`:
	- visão geral dos relatórios
	- acesso à listagem de usuários e roles
	- exclusão de usuários
- `user`:
	- sem acesso à listagem geral de usuários
	- sem visão geral de relatórios
	- acesso apenas aos dados próprios nos relatórios

Regras de segurança na exclusão de usuários:

- admin não pode excluir a própria conta
- não é permitido remover o último admin

### 3) Relatórios

#### Pedidos (e-commerce)

Entidades:

- `products`
- `orders`
- `order_items`

Rota:

- `GET /relatorios/pedidos-complexo`
- JSON opcional: `?format=json`

Métricas:

- itens totais por pedido
- produtos únicos
- total calculado (`SUM(line_total)`)
- ticket médio de item (`AVG(unit_price)`)
- ranking de clientes (somente admin)

#### Financeiro (carteiras)

Entidades:

- `assets`
- `portfolios`
- `portfolio_positions`
- `price_histories`

Rota:

- `GET /relatorios/financeiro/carteiras`
- JSON opcional: `?format=json`

Métricas:

- `cost_basis`, `market_value`, `pnl`, `return_pct`
- `annualized_volatility_pct`
- `annualized_return_pct`
- `sharpe_ratio`
- `concentration_hhi`
- exposição por classe de ativo

Fórmulas usadas:

- `P&L = market_value - cost_basis`
- `Retorno = P&L / cost_basis`
- `Vol anual = vol diária * sqrt(252)`
- `Sharpe = (retorno_anual - taxa_livre_risco) / vol_anual`
- `HHI = sum(w_i^2)`

## Rotas principais

Autenticação:

- `GET /login`
- `POST /login`
- `GET /registrar`
- `POST /registrar`
- `POST /logout`

Área protegida:

- `GET /bem-vindo`
- `GET /relatorios/pedidos-complexo`
- `GET /relatorios/financeiro/carteiras`

Admin-only:

- `GET /admin/usuarios`
- `DELETE /admin/usuarios/{user}`

## Como executar

1. Instalar dependências:

```bash
composer install
```

2. Configurar ambiente:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configurar banco (`.env`):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=base_arch
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

4. Rodar estrutura + dados:

```bash
php artisan migrate --seed
```

5. Subir app:

```bash
php artisan serve
```

6. Acessar:

- `http://127.0.0.1:8000/login`

## Credenciais seed

- `test@example.com` / `Senha@123` (`admin`)
- `maria@example.com` / `Senha@123` (`user`)
- `joao@example.com` / `Senha@123` (`user`)

## Variáveis de ambiente relevantes

```env
# JWT
JWT_SECRET=
JWT_ALGORITHM=HS256
JWT_TTL_MINUTES=120

# Finance
FIN_RISK_FREE_RATE_ANNUAL=0.08
FIN_TRADING_DAYS_PER_YEAR=252
FIN_LOOKBACK_DAYS=30
```

## Arquivos-chave

- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/AdminUserController.php`
- `app/Http/Controllers/OrderAnalyticsController.php`
- `app/Http/Controllers/FinanceAnalyticsController.php`
- `app/Http/Middleware/JwtAuthenticate.php`
- `app/Http/Middleware/EnsureAdmin.php`
- `routes/web.php`
- `resources/views/auth/*`
- `resources/views/admin/*`
- `resources/views/reports/*`
- `database/seeders/DatabaseSeeder.php`

## Próximos passos sugeridos

- testes de integração para regras de autorização
- refresh token JWT
- paginação e filtros nos relatórios
- auditoria de ações administrativas (exclusão de usuário)
