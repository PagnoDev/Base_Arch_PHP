<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Portfolio;
use App\Models\PortfolioPosition;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('Senha@123'),
        ]);

        $maria = User::query()->updateOrCreate([
            'email' => 'maria@example.com',
        ], [
            'name' => 'Maria Souza',
            'password' => Hash::make('Senha@123'),
        ]);

        $joao = User::query()->updateOrCreate([
            'email' => 'joao@example.com',
        ], [
            'name' => 'João Lima',
            'password' => Hash::make('Senha@123'),
        ]);

        $products = [
            'PRD-001' => Product::query()->updateOrCreate(['sku' => 'PRD-001'], ['name' => 'Notebook Pro 14', 'price' => 5200, 'active' => true]),
            'PRD-002' => Product::query()->updateOrCreate(['sku' => 'PRD-002'], ['name' => 'Mouse Gamer', 'price' => 280, 'active' => true]),
            'PRD-003' => Product::query()->updateOrCreate(['sku' => 'PRD-003'], ['name' => 'Monitor 27 2K', 'price' => 1900, 'active' => true]),
            'PRD-004' => Product::query()->updateOrCreate(['sku' => 'PRD-004'], ['name' => 'Teclado Mecânico', 'price' => 450, 'active' => true]),
        ];

        $this->syncOrder($testUser, 'PED-2026-0001', 'paid', now()->subDays(8), [
            ['product' => $products['PRD-001'], 'quantity' => 1],
            ['product' => $products['PRD-002'], 'quantity' => 2],
        ]);

        $this->syncOrder($maria, 'PED-2026-0002', 'paid', now()->subDays(3), [
            ['product' => $products['PRD-003'], 'quantity' => 1],
            ['product' => $products['PRD-004'], 'quantity' => 1],
            ['product' => $products['PRD-002'], 'quantity' => 1],
        ]);

        $this->syncOrder($joao, 'PED-2026-0003', 'pending', now()->subDay(), [
            ['product' => $products['PRD-004'], 'quantity' => 2],
            ['product' => $products['PRD-002'], 'quantity' => 1],
        ]);

        $assets = [
            'PETR4' => Asset::query()->updateOrCreate(['symbol' => 'PETR4'], ['name' => 'Petrobras PN', 'asset_class' => 'acoes', 'risk_level' => 4, 'active' => true]),
            'ITUB4' => Asset::query()->updateOrCreate(['symbol' => 'ITUB4'], ['name' => 'Itaú Unibanco PN', 'asset_class' => 'acoes', 'risk_level' => 3, 'active' => true]),
            'TESOURO-IPCA' => Asset::query()->updateOrCreate(['symbol' => 'TESOURO-IPCA'], ['name' => 'Tesouro IPCA+', 'asset_class' => 'renda_fixa', 'risk_level' => 2, 'active' => true]),
            'BTC' => Asset::query()->updateOrCreate(['symbol' => 'BTC'], ['name' => 'Bitcoin', 'asset_class' => 'cripto', 'risk_level' => 5, 'active' => true]),
        ];

        $this->seedPriceHistory($assets['PETR4'], 36.8, 60, 0.022);
        $this->seedPriceHistory($assets['ITUB4'], 30.5, 60, 0.016);
        $this->seedPriceHistory($assets['TESOURO-IPCA'], 104.2, 60, 0.0025);
        $this->seedPriceHistory($assets['BTC'], 265000, 60, 0.035);

        $carteiraModerada = Portfolio::query()->updateOrCreate([
            'user_id' => $testUser->id,
            'name' => 'Carteira Moderada',
        ], [
            'currency' => 'BRL',
        ]);

        $carteiraAgressiva = Portfolio::query()->updateOrCreate([
            'user_id' => $maria->id,
            'name' => 'Carteira Agressiva',
        ], [
            'currency' => 'BRL',
        ]);

        $this->syncPosition($carteiraModerada, $assets['PETR4'], 120, 35.7);
        $this->syncPosition($carteiraModerada, $assets['ITUB4'], 80, 29.9);
        $this->syncPosition($carteiraModerada, $assets['TESOURO-IPCA'], 60, 102.8);

        $this->syncPosition($carteiraAgressiva, $assets['PETR4'], 200, 34.5);
        $this->syncPosition($carteiraAgressiva, $assets['BTC'], 0.18, 240000);
    }

    private function syncOrder(User $user, string $code, string $status, \DateTimeInterface $orderedAt, array $items): void
    {
        $order = Order::query()->updateOrCreate([
            'code' => $code,
        ], [
            'user_id' => $user->id,
            'status' => $status,
            'ordered_at' => $orderedAt,
            'total' => 0,
        ]);

        OrderItem::query()->where('order_id', $order->id)->delete();

        $total = 0;

        foreach ($items as $item) {
            $lineTotal = $item['quantity'] * (float) $item['product']->price;

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['product']->price,
                'line_total' => $lineTotal,
            ]);

            $total += $lineTotal;
        }

        $order->update(['total' => $total]);
    }

    private function syncPosition(Portfolio $portfolio, Asset $asset, float $quantity, float $averageCost): void
    {
        PortfolioPosition::query()->updateOrCreate([
            'portfolio_id' => $portfolio->id,
            'asset_id' => $asset->id,
        ], [
            'quantity' => $quantity,
            'average_cost' => $averageCost,
            'opened_at' => now()->subMonths(6)->toDateString(),
        ]);
    }

    private function seedPriceHistory(Asset $asset, float $startPrice, int $days, float $volatility): void
    {
        $startDate = CarbonImmutable::now()->subDays($days - 1);
        $price = $startPrice;

        for ($index = 0; $index < $days; $index++) {
            $date = $startDate->addDays($index);

            $shock = mt_rand(-1000, 1000) / 1000;
            $drift = 0.0006;
            $change = ($drift + ($shock * $volatility));
            $price = max(0.01, $price * (1 + $change));

            PriceHistory::query()->updateOrCreate([
                'asset_id' => $asset->id,
                'price_date' => $date->toDateString(),
            ], [
                'close_price' => round($price, 4),
            ]);
        }
    }
}
