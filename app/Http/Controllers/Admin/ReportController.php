<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const ALLOWED_DAYS = [7, 14, 30];

    public function index(Request $request): View
    {
        $days = (int) $request->query('periode', 14);
        if (! in_array($days, self::ALLOWED_DAYS, true)) {
            $days = 14;
        }

        $start = now()->startOfDay()->subDays($days - 1);

        $ordersInRange = Order::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total', 'status']);

        $ordersByDay = $ordersInRange
            ->groupBy(fn (Order $order) => $order->created_at->toDateString());

        $chartLabels = [];
        $chartValues = [];
        $chartMax = 1;

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $count = $ordersByDay->get($day->toDateString(), collect())->count();

            $chartLabels[] = $day->locale('id')->isoFormat('DD MMM');
            $chartValues[] = $count;
            $chartMax = max($chartMax, $count);
        }

        $statusRows = collect(OrderStatus::cases())
            ->map(function (OrderStatus $status): array {
                $query = Order::query()->where('status', $status);

                return [
                    'status' => $status,
                    'label' => $status->label(),
                    'count' => (int) $query->count(),
                    'total' => (float) (clone $query)->sum('total'),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();

        $pieSlices = $statusRows
            ->map(fn (array $row): array => [
                'label' => $row['label'],
                'value' => $row['count'],
            ])
            ->values();

        $pieTotal = (int) $pieSlices->sum('value');

        $revenue = (float) Order::query()
            ->whereIn('status', [
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::Ready,
                OrderStatus::Completed,
            ])
            ->sum('total');

        $completedCount = Order::query()->where('status', OrderStatus::Completed)->count();
        $totalOrders = (int) Order::query()->count();
        $customerCount = User::query()->where('role', Role::Customer)->count();
        $activeServices = Service::query()->active()->count();
        $avgOrder = $completedCount > 0 ? $revenue / $completedCount : 0;

        $topServices = OrderItem::query()
            ->selectRaw('service_name_snapshot as name, SUM(quantity) as qty')
            ->whereNotNull('service_name_snapshot')
            ->groupBy('service_name_snapshot')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.reports.index', [
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'chartMax' => $chartMax,
            'pieSlices' => $pieSlices,
            'pieTotal' => $pieTotal,
            'statusRows' => $statusRows,
            'revenue' => $revenue,
            'totalOrders' => $totalOrders,
            'completedCount' => $completedCount,
            'customerCount' => $customerCount,
            'activeServices' => $activeServices,
            'avgOrder' => $avgOrder,
            'topServices' => $topServices,
            'days' => $days,
            'periodOptions' => self::ALLOWED_DAYS,
            'rangeLabel' => $start->locale('id')->isoFormat('D MMM YYYY').' – '.now()->locale('id')->isoFormat('D MMM YYYY'),
        ]);
    }
}
