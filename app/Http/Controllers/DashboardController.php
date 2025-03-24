<?php

namespace App\Http\Controllers;

use App\Models\BenefitDelivery;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // Totais simples
        $totalUsers = User::count();
        $totalUnits = Unit::count();

        // Total de benefícios entregues no período
        $totalDelivered = BenefitDelivery::whereBetween('created_at', [$startDate, $endDate])->count();

        // Entregas por unidade
        $deliveriesByUnit = BenefitDelivery::whereBetween('created_at', [$startDate, $endDate])
            ->with('unit')
            ->get()
            ->groupBy(fn($item) => $item->unit->name ?? 'Sem unidade')
            ->map(fn($items) => $items->count());

        // Entregas por status
        $deliveriesByStatus = BenefitDelivery::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('status')
            ->map(fn($items) => $items->count());

        return view('dashboard', compact(
            'totalUsers',
            'totalUnits',
            'totalDelivered',
            'deliveriesByUnit',
            'deliveriesByStatus',
            'startDate',
            'endDate'
        ));
    }
}
