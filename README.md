# Base_Arch (Laravel + JWT + Relatórios Financeiros)

Projeto base em Laravel 12 com autenticação JWT, fluxo de login/registro e módulos de relatórios com consultas Eloquent complexas (`inner join`, agregações e métricas financeiras).

## Objetivo

Este projeto foi evoluído para servir como base arquitetural e portfólio técnico, com foco em cenários de negócio (e-commerce + finanças):

- Autenticação com JWT (sem roles, por enquanto)
- Cadastro e login com validações robustas de senha
- Relatório de pedidos com joins e ranking de clientes
- Relatório financeiro de carteiras com retorno, volatilidade, Sharpe e concentração

## Stack

- PHP 8.2+
- Laravel 12
- PostgreSQL
- Blade (views server-side)
- `firebase/php-jwt`

## Como rodar localmente

1. Instale dependências:

```bash
composer install
```

2. Configure ambiente:

```bash
cp .env.example .env
php artisan key:generate
```

3. Ajuste o banco no `.env` (PostgreSQL):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=base_arch
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

4. Execute migrations + seed:

```bash
php artisan migrate --seed
```

5. Suba a aplicação:

```bash
php artisan serve
```

6. Acesse:

- `http://127.0.0.1:8000/login`

## Usuários de teste (seed)

- `test@example.com` / `Senha@123`
- `maria@example.com` / `Senha@123`
- `joao@example.com` / `Senha@123`

## Autenticação

### Fluxo

- `GET /login`: tela de login
- `POST /login`: valida credenciais e gera JWT
- `GET /registrar`: tela de registro
- `POST /registrar`: cria usuário e autentica via JWT
- `POST /logout`: remove token e encerra sessão

### Regras de senha (registro)

- Mínimo 8 caracteres
- Letra maiúscula e minúscula
- Número
- Caractere especial
- Sem espaços

### Observação técnica

O JWT é armazenado em cookie `HttpOnly` (`auth_token`) e validado por middleware para rotas protegidas.

## Módulos de domínio

## 1) Pedidos (e-commerce)

Entidades:

- `products`
- `orders`
- `order_items`

Relatório:

- `GET /relatorios/pedidos-complexo`
- JSON opcional: `GET /relatorios/pedidos-complexo?format=json`

Principais cálculos:

- Quantidade total de itens por pedido
- Produtos únicos por pedido
- Total calculado por pedido (`SUM(line_total)`)
- Preço médio dos itens
- Ranking de clientes por receita bruta

## 2) Financeiro (carteiras)

Entidades:

- `assets`
- `portfolios`
- `portfolio_positions`
- `price_histories`

Relatório:

- `GET /relatorios/financeiro/carteiras`
- JSON opcional: `GET /relatorios/financeiro/carteiras?format=json`

Principais métricas:

- `cost_basis` (custo)
- `market_value` (valor de mercado)
- `pnl` (lucro/prejuízo)
- `return_pct` (retorno %)
- `annualized_volatility_pct` (volatilidade anualizada)
- `annualized_return_pct` (retorno anualizado)
- `sharpe_ratio`
- `concentration_hhi`
- Exposição por classe de ativo

Fórmulas usadas:

- `P&L = market_value - cost_basis`
- `Retorno = P&L / cost_basis`
- `Volatilidade anual = vol_diaria * sqrt(252)`
- `Sharpe = (retorno_anual - taxa_livre_risco) / vol_anual`
- `HHI = sum(w_i^2)`

## Navegação no app

Após autenticação:

- `/bem-vindo` mostra atalhos para os relatórios
- Relatórios podem ser vistos em HTML ou JSON (`?format=json`)

## Configurações de ambiente relevantes

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

## Estrutura principal (arquivos-chave)

- Autenticação: `app/Http/Controllers/AuthController.php`
- Middleware JWT: `app/Http/Middleware/JwtAuthenticate.php`
- Relatórios:
	- `app/Http/Controllers/OrderAnalyticsController.php`
	- `app/Http/Controllers/FinanceAnalyticsController.php`
- Rotas: `routes/web.php`
- Views:
	- `resources/views/auth/*`
	- `resources/views/welcome.blade.php`
	- `resources/views/reports/*`
- Seed: `database/seeders/DatabaseSeeder.php`

## Próximos passos sugeridos

- Adicionar testes de integração para os relatórios
- Implementar refresh de token JWT
- Adicionar paginação/filtros por período nos relatórios
- Evoluir para RBAC (roles/permissões) quando necessário
