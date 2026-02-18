<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderAnalyticsController extends Controller
{
    public function complexReport(Request $request): JsonResponse|View
    {
        $paidOrders = Order::query()
            ->select([
                'orders.id',
                'orders.code',
                'orders.status',
                'orders.ordered_at',
                'users.name as customer_name',
                'users.email as customer_email',
                DB::raw('COUNT(DISTINCT order_items.product_id) as unique_products'),
                DB::raw('SUM(order_items.quantity) as total_items'),
                DB::raw('SUM(order_items.line_total) as calculated_total'),
                DB::raw('AVG(order_items.unit_price) as average_item_price'),
            ])
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'paid')
            ->where('products.active', true)
            ->groupBy([
                'orders.id',
                'orders.code',
                'orders.status',
                'orders.ordered_at',
                'users.name',
                'users.email',
            ])
            ->havingRaw('SUM(order_items.line_total) > 200')
            ->orderByDesc('calculated_total')
            ->get();

        $topCustomers = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT orders.id) as paid_orders_count'),
                DB::raw('SUM(order_items.line_total) as gross_revenue'),
            ])
            ->join('orders', 'orders.user_id', '=', 'users.id')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->groupBy(['users.id', 'users.name', 'users.email'])
            ->orderByDesc('gross_revenue')
            ->limit(5)
            ->get();

        $payload = [
            'summary' => [
                'paid_orders_analyzed' => $paidOrders->count(),
                'top_customers_count' => $topCustomers->count(),
            ],
            'paid_orders' => $paidOrders,
            'top_customers' => $topCustomers,
        ];

        if ($request->query('format') === 'json' || $request->expectsJson()) {
            return response()->json($payload);
        }

        return view('reports.orders-complex', $payload);
    }
}
