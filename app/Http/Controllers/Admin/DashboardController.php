<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Service;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingCount = (int) ($counts[OrderStatus::PendingPayment->value] ?? 0);
        $paidCount = (int) ($counts[OrderStatus::Paid->value] ?? 0);
        $processingCount = (int) ($counts[OrderStatus::Processing->value] ?? 0);
        $readyCount = (int) ($counts[OrderStatus::Ready->value] ?? 0);
        $activeServices = Service::query()->active()->count();

        $recentOrders = Order::query()
            ->with(['customer', 'items'])
            ->withCount('items')
            ->latest()
            ->take(5)
            ->get();

        $upcomingBookings = Booking::query()
            ->whereDate('booking_date', '>=', now()->toDateString())
            ->with(['customer', 'orders' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('booking_date')
            ->orderBy('time_slot')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'pendingCount' => $pendingCount,
            'paidCount' => $paidCount,
            'processingCount' => $processingCount,
            'readyCount' => $readyCount,
            'activeServices' => $activeServices,
            'recentOrders' => $recentOrders,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }
}
