<?php

namespace App\Http\Controllers\Api\V1;

use App\BusinessLocation;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    protected $businessUtil;
    protected $transactionUtil;

    public function __construct(BusinessUtil $businessUtil, TransactionUtil $transactionUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Store-level KPI summary.
     */
    public function kpis(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
        $start_date = $date;
        $end_date = $date;
        
        $yesterday_start = Carbon::parse($date)->subDay()->format('Y-m-d');
        $yesterday_end = $yesterday_start;

        // Today's Stats
        $today_stats = $this->getStats($business_id, $start_date, $end_date);
        
        // Yesterday's Stats for comparison
        $yesterday_stats = $this->getStats($business_id, $yesterday_start, $yesterday_end);

        $data = [
            'date' => $date,
            'revenue' => [
                'today' => (float)$today_stats['total_revenue'],
                'yesterday' => (float)$yesterday_stats['total_revenue'],
                'changePercent' => $this->calculateChange($today_stats['total_revenue'], $yesterday_stats['total_revenue'])
            ],
            'transactions' => [
                'today' => (int)$today_stats['transaction_count'],
                'yesterday' => (int)$yesterday_stats['transaction_count'],
                'changePercent' => $this->calculateChange($today_stats['transaction_count'], $yesterday_stats['transaction_count'])
            ],
            'averageTransactionValue' => [
                'today' => (float)$today_stats['avg_value'],
                'yesterday' => (float)$yesterday_stats['avg_value'],
                'changePercent' => $this->calculateChange($today_stats['avg_value'], $yesterday_stats['avg_value'])
            ],
            'itemsSold' => [
                'today' => (float)$today_stats['items_sold'],
                'yesterday' => (float)$yesterday_stats['items_sold']
            ],
            'lowStockAlertCount' => $this->getLowStockCount($business_id),
            'pendingReversalRequests' => 0, // Placeholder
            'pendingOverrideRequests' => 0, // Placeholder
            'activeCashiers' => DB::table('users')->where('business_id', $business_id)->where('status', 'active')->count(),
            'openShifts' => DB::table('cash_registers')->where('business_id', $business_id)->where('status', 'open')->count()
        ];

        return $this->success($data);
    }

    /**
     * Products at or below reorder level.
     */
    public function lowStockAlerts(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;

        $query = Variation::join('products as p', 'p.id', '=', 'variations.product_id')
            ->join('variation_location_details as vld', 'vld.variation_id', '=', 'variations.id')
            ->leftjoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->whereRaw('vld.qty_available <= p.alert_quantity')
            ->select(
                'variations.id as variation_id',
                'p.name as product_name',
                'variations.sub_sku as sku',
                'vld.qty_available as stock_quantity',
                'p.alert_quantity as reorder_level',
                'c.name as category',
                'p.updated_at as last_updated'
            );

        $low_stock = $query->get();

        $data = $low_stock->map(function($item) {
            return [
                'productId' => (int)$item->variation_id,
                'productName' => $item->product_name,
                'sku' => $item->sku,
                'stockQuantity' => (float)$item->stock_quantity,
                'reorderLevel' => (float)$item->reorder_level,
                'category' => $item->category ?? 'N/A',
                'lastRestockedAt' => $item->last_updated ? Carbon::parse($item->last_updated)->toIso8601String() : null
            ];
        });

        return $this->success($data);
    }

    /**
     * Live activity feed - recent transactions.
     */
    public function recentTransactions(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);
        if ($limit > 50) $limit = 50;

        $transactions = Transaction::where('business_id', $user->business_id)
            ->where('type', 'sell')
            ->with(['sales_person', 'sell_lines', 'payment_lines'])
            ->latest('transaction_date')
            ->limit($limit)
            ->get();

        $data = $transactions->map(function($t) {
            return [
                'id' => (int)$t->id,
                'referenceNo' => $t->invoice_no,
                'cashierName' => trim(($t->sales_person->first_name ?? '') . ' ' . ($t->sales_person->last_name ?? '')),
                'totalAmount' => (float)$t->final_total,
                'itemCount' => $t->sell_lines->count(),
                'paymentMethods' => $t->payment_lines->pluck('method')->map(fn($m) => strtoupper($m))->unique()->values(),
                'status' => strtoupper($t->status),
                'createdAt' => $t->created_at->toIso8601String()
            ];
        });

        return $this->success($data);
    }

    private function getStats($business_id, $start, $end)
    {
        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', $start)
            ->whereDate('transaction_date', '<=', $end);

        $stats = $query->select(
            DB::raw('SUM(final_total) as total_revenue'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('AVG(final_total) as avg_value')
        )->first();

        $items_sold = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $start)
            ->whereDate('t.transaction_date', '<=', $end)
            ->sum('tsl.quantity');

        return [
            'total_revenue' => $stats->total_revenue ?? 0,
            'transaction_count' => $stats->transaction_count ?? 0,
            'avg_value' => $stats->avg_value ?? 0,
            'items_sold' => $items_sold ?? 0
        ];
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getLowStockCount($business_id)
    {
        return Variation::join('products as p', 'p.id', '=', 'variations.product_id')
            ->join('variation_location_details as vld', 'vld.variation_id', '=', 'variations.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->whereRaw('vld.qty_available <= p.alert_quantity')
            ->count();
    }
}
