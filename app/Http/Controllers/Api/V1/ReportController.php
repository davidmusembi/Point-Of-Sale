<?php

namespace App\Http\Controllers\Api\V1;

use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    protected $transactionUtil;

    protected $productUtil;

    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Profit & Loss report summary.
     */
    public function getProfitLoss(Request $request)
    {
        $user = $request->user();
        if (!$user->can('profit_loss_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view profit loss report.', null, 403);
        }

        $business_id = $user->business_id;
        $location_id = $request->get('location_id');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $user_id = $request->get('user_id');

        $permitted_locations = $this->getPermittedLocations();

        $data = $this->transactionUtil->getProfitLossDetails(
            $business_id,
            $location_id,
            $start_date,
            $end_date,
            $user_id,
            $permitted_locations
        );

        return $this->success($data);
    }

    /**
     * Purchase & Sell report summary.
     */
    public function getPurchaseSell(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view purchase sell report.', null, 403);
        }

        $business_id = $user->business_id;
        $location_id = $request->get('location_id');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start_date, $end_date, $location_id);
        $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id);

        $difference = [
            'total_purchase_inc_tax' => $purchase_details['total_purchase_inc_tax'] - $sell_details['total_sell_inc_tax'],
            'total_purchase_return_inc_tax' => $purchase_details['total_purchase_return_inc_tax'] - $sell_details['total_sell_return_inc_tax'],
            'purchase_due' => $purchase_details['purchase_due'] - $sell_details['invoice_due'],
        ];

        return $this->success([
            'purchase' => $purchase_details,
            'sell' => $sell_details,
            'difference' => $difference
        ]);
    }

    /**
     * Tax report summary.
     */
    public function getTaxReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('tax_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view tax report.', null, 403);
        }

        $business_id = $user->business_id;
        $location_id = $request->get('location_id');
        $contact_id = $request->get('contact_id');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $input_tax_details = $this->transactionUtil->getInputTax($business_id, $start_date, $end_date, $location_id, $contact_id);
        $output_tax_details = $this->transactionUtil->getOutputTax($business_id, $start_date, $end_date, $location_id, $contact_id);
        $expense_tax_details = $this->transactionUtil->getExpenseTax($business_id, $start_date, $end_date, $location_id, $contact_id);

        $total_output_tax = $output_tax_details['total_tax'];
        $tax_diff = $total_output_tax - $input_tax_details['total_tax'] - $expense_tax_details['total_tax'];

        return $this->success([
            'input_tax' => $input_tax_details,
            'output_tax' => $output_tax_details,
            'expense_tax' => $expense_tax_details,
            'tax_diff' => $tax_diff
        ]);
    }

    /**
     * Expense report summary.
     */
    public function getExpenseReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('expense_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view expense report.', null, 403);
        }

        $business_id = $user->business_id;
        $filters = $request->only(['category', 'location_id']);
        $filters['start_date'] = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $filters['end_date'] = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $expenses = $this->transactionUtil->getExpenseReport($business_id, $filters);

        return $this->success($expenses);
    }

    /**
     * Stock adjustment report summary.
     */
    public function getStockAdjustmentReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('stock_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view stock adjustment report.', null, 403);
        }

        $business_id = $user->business_id;
        $location_id = $request->get('location_id');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'stock_adjustment');

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('location_id', $permitted_locations);
        }

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        if (!empty($location_id)) {
            $query->where('location_id', $location_id);
        }

        $stock_adjustment_details = $query->select(
            DB::raw('SUM(final_total) as total_amount'),
            DB::raw('SUM(total_amount_recovered) as total_recovered'),
            DB::raw("SUM(IF(adjustment_type = 'normal', final_total, 0)) as total_normal"),
            DB::raw("SUM(IF(adjustment_type = 'abnormal', final_total, 0)) as total_abnormal")
        )->first();

        return $this->success($stock_adjustment_details);
    }

    /**
     * Register report summary.
     */
    public function getRegisterReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('register_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view register report.', null, 403);
        }

        $business_id = $user->business_id;
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $user_id = $request->get('user_id');

        $permitted_locations = $this->getPermittedLocations();

        $registers = $this->transactionUtil->registerReport($business_id, $permitted_locations, $start_date, $end_date, $user_id);

        $perPage = $request->get('perPage', 20);
        $paginated_registers = $registers->paginate($perPage);

        return $this->paginate($paginated_registers);
    }

    /**
     * Stock report summary.
     */
    public function getStockReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('stock_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view stock report.', null, 403);
        }

        $business_id = $user->business_id;
        $filters = $request->only(['location_id', 'category_id', 'sub_category_id', 'brand_id', 'unit_id', 'tax_id', 'type', 'active_state', 'not_for_selling']);

        $perPage = $request->get('perPage', 20);
        $products = $this->productUtil->getProductStockDetails($business_id, $filters, 'api');

        return $this->paginate($products);
    }

    /**
     * Stock expiry report summary.
     */
    public function getStockExpiryReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('stock_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view stock expiry report.', null, 403);
        }

        $business_id = $user->business_id;
        $query = \App\PurchaseLine::leftjoin('transactions as t', 'purchase_lines.transaction_id', '=', 't.id')
            ->leftjoin('products as p', 'purchase_lines.product_id', '=', 'p.id')
            ->leftjoin('variations as v', 'purchase_lines.variation_id', '=', 'v.id')
            ->leftjoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftjoin('business_locations as l', 't.location_id', '=', 'l.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('p.enable_stock', 1);

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($request->input('location_id'))) {
            $query->where('t.location_id', $request->input('location_id'));
        }

        $query->select(
            'p.name as product',
            'v.name as variation',
            'pv.name as product_variation',
            'l.name as location',
            'u.short_name as unit',
            'purchase_lines.exp_date',
            'purchase_lines.lot_number',
            't.ref_no',
            DB::raw('(purchase_lines.quantity - purchase_lines.quantity_sold - purchase_lines.quantity_adjusted - purchase_lines.quantity_returned) as stock_left')
        );

        $perPage = $request->get('perPage', 20);
        $expiring_stock = $query->paginate($perPage);

        return $this->paginate($expiring_stock);
    }

    /**
     * Lot report summary.
     */
    public function getLotReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('stock_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view lot report.', null, 403);
        }

        $business_id = $user->business_id;
        $query = \App\Product::where('products.business_id', $business_id)
            ->leftjoin('units', 'products.unit_id', '=', 'units.id')
            ->join('variations as v', 'products.id', '=', 'v.product_id')
            ->join('purchase_lines as pl', 'v.id', '=', 'pl.variation_id')
            ->join('transactions as t', 'pl.transaction_id', '=', 't.id');

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($request->input('location_id'))) {
            $query->where('t.location_id', $request->input('location_id'));
        }

        $query->select(
            'products.name as product',
            'v.name as variation',
            't.ref_no',
            'pl.lot_number',
            'pl.exp_date',
            DB::raw('(pl.quantity - pl.quantity_sold - pl.quantity_adjusted - pl.quantity_returned) as stock_left')
        );

        $perPage = $request->get('perPage', 20);
        $lots = $query->paginate($perPage);

        return $this->paginate($lots);
    }

    /**
     * Stock value summary.
     */
    public function getStockValue(Request $request)
    {
        $user = $request->user();
        if (!$user->can('stock_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view stock value.', null, 403);
        }

        $business_id = $user->business_id;
        $location_id = $request->input('location_id');
        $filters = $request->only(['category_id', 'sub_category_id', 'brand_id', 'unit_id']);
        $end_date = Carbon::now()->format('Y-m-d');

        $permitted_locations = $this->getPermittedLocations();

        $closing_stock_by_pp = $this->transactionUtil->getOpeningClosingStock($business_id, $end_date, $location_id, false, false, $filters, $permitted_locations);
        $closing_stock_by_sp = $this->transactionUtil->getOpeningClosingStock($business_id, $end_date, $location_id, false, true, $filters, $permitted_locations);

        $potential_profit = $closing_stock_by_sp - $closing_stock_by_pp;
        $profit_margin = empty($closing_stock_by_sp) ? 0 : ($potential_profit / $closing_stock_by_sp) * 100;

        return $this->success([
            'closing_stock_by_pp' => (float) $closing_stock_by_pp,
            'closing_stock_by_sp' => (float) $closing_stock_by_sp,
            'potential_profit' => (float) $potential_profit,
            'profit_margin' => (float) $profit_margin
        ]);
    }

    /**
     * Revenue trends and breakdown over a date range.
     */
    public function sales(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;

        $from = $request->get('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', Carbon::now()->format('Y-m-d'));
        $group_by = $request->get('groupBy', 'day');

        $summary = $this->getSalesSummary($business_id, $from, $to);
        $series = $this->getSalesSeries($business_id, $from, $to, $group_by);
        $byCategory = $this->getSalesByCategory($business_id, $from, $to);
        $byPaymentMethod = $this->getSalesByPaymentMethod($business_id, $from, $to);

        return $this->success([
            'from' => $from,
            'to' => $to,
            'groupBy' => $group_by,
            'summary' => $summary,
            'series' => $series,
            'byCategory' => $byCategory,
            'byPaymentMethod' => $byPaymentMethod
        ]);
    }

    private function getSalesSummary($business_id, $from, $to)
    {
        $stats = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->select(
                DB::raw('SUM(final_total) as total_revenue'),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('AVG(final_total) as avg_transaction_value')
            )->first();

        $total_refunds = Transaction::where('business_id', $business_id)
            ->where('type', 'sell_return')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->sum('final_total');

        $days = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
        $total_revenue = (float) $stats->total_revenue;

        return [
            'totalRevenue' => $total_revenue,
            'totalTransactions' => (int) $stats->total_transactions,
            'averageDailyRevenue' => $days > 0 ? round($total_revenue / $days, 2) : 0,
            'averageTransactionValue' => round((float) $stats->avg_transaction_value, 2),
            'totalRefunds' => (float) $total_refunds,
            'netRevenue' => $total_revenue - (float) $total_refunds
        ];
    }

    private function getSalesSeries($business_id, $from, $to, $group_by)
    {
        $date_format = $group_by == 'day' ? '%Y-%m-%d' : ($group_by == 'week' ? '%Y-w%u' : '%Y-%m');

        return Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->groupBy('period')
            ->orderBy('period')
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '$date_format') as period"),
                DB::raw('SUM(final_total) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )->get()->map(function ($item) {
                return [
                    'period' => $item->period,
                    'revenue' => (float) $item->revenue,
                    'transactions' => (int) $item->transactions,
                    'refunds' => 0.0
                ];
            });
    }

    private function getSalesByCategory($business_id, $from, $to)
    {
        $results = TransactionSellLine::join('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('products as p', 'p.id', '=', 'transaction_sell_lines.product_id')
            ->leftjoin('categories as c', 'p.category_id', '=', 'c.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('c.id')
            ->select(
                DB::raw('COALESCE(c.name, "Uncategorized") as categoryName'),
                DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price) as revenue')
            )->get();

        $total = $results->sum('revenue');

        return $results->map(function ($item) use ($total) {
            return [
                'categoryName' => $item->categoryName,
                'revenue' => (float) $item->revenue,
                'percent' => $total > 0 ? round(($item->revenue / $total) * 100, 2) : 0
            ];
        });
    }

    private function getSalesByPaymentMethod($business_id, $from, $to)
    {
        $results = TransactionPayment::join('transactions as t', 't.id', '=', 'transaction_payments.transaction_id')
            ->where('t.business_id', $business_id)
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('transaction_payments.method')
            ->select(
                'transaction_payments.method',
                DB::raw('SUM(transaction_payments.amount) as amount')
            )->get();

        $total = $results->sum('amount');

        return $results->map(function ($item) use ($total) {
            return [
                'method' => strtoupper($item->method),
                'amount' => (float) $item->amount,
                'percent' => $total > 0 ? round(($item->amount / $total) * 100, 2) : 0
            ];
        });
    }

    /**
     * Stock valuation, turnover, slow-movers, and top-movers.
     */
    public function inventoryAnalysis(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;

        $from = $request->get('from', Carbon::now()->subMonth()->format('Y-m-d'));
        $to = $request->get('to', Carbon::now()->format('Y-m-d'));

        $totalSkus = DB::table('variations as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->where('p.business_id', $business_id)
            ->count();

        $lowStockCount = DB::table('variations as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->join('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->whereRaw('vld.qty_available <= p.alert_quantity')
            ->count();

        return $this->success([
            'asOf' => Carbon::now()->toIso8601String(),
            'totalSkus' => $totalSkus,
            'lowStockCount' => $lowStockCount,
            'stockValuation' => $this->getStockValuation($business_id),
            'turnoverRate' => 0.0,
            'slowMovers' => $this->getSlowMovers($business_id),
            'topMovers' => $this->getTopMovers($business_id, $from, $to)
        ]);
    }

    private function getStockValuation($business_id)
    {
        $query = DB::table('variations as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->join('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->select(
                DB::raw('SUM(vld.qty_available * v.default_purchase_price) as total_cost'),
                DB::raw('SUM(vld.qty_available * v.default_sell_price) as total_retail')
            )->first();

        $total_cost = (float) $query->total_cost;
        $total_retail = (float) $query->total_retail;
        $margin = $total_retail - $total_cost;

        return [
            'totalCostValue' => round($total_cost, 2),
            'totalRetailValue' => round($total_retail, 2),
            'potentialMargin' => round($margin, 2),
            'potentialMarginPercent' => $total_retail > 0 ? round(($margin / $total_retail) * 100, 2) : 0
        ];
    }

    private function getTopMovers($business_id, $from, $to)
    {
        return TransactionSellLine::join('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->join('variations as v', 'v.id', '=', 'transaction_sell_lines.variation_id')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('v.id')
            ->orderByDesc('unitsSold')
            ->limit(10)
            ->select(
                'v.id as productId',
                'p.name as productName',
                DB::raw('SUM(transaction_sell_lines.quantity) as unitsSold'),
                DB::raw('SUM(transaction_sell_lines.quantity * transaction_sell_lines.unit_price) as revenue')
            )->get()->map(function ($item) {
                return [
                    'productId' => (int) $item->productId,
                    'productName' => $item->productName,
                    'unitsSold' => (float) $item->unitsSold,
                    'revenue' => (float) $item->revenue
                ];
            });
    }

    private function getSlowMovers($business_id)
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        return DB::table('variations as v')
            ->join('products as p', 'p.id', '=', 'v.product_id')
            ->join('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
            ->leftjoin('transaction_sell_lines as tsl', 'tsl.variation_id', '=', 'v.id')
            ->leftjoin('transactions as t', function ($join) use ($thirtyDaysAgo) {
                $join->on('t.id', '=', 'tsl.transaction_id')
                    ->where('t.type', 'sell')
                    ->where('t.status', 'final')
                    ->whereDate('t.transaction_date', '>=', $thirtyDaysAgo);
            })
            ->where('p.business_id', $business_id)
            ->where('p.enable_stock', 1)
            ->where('vld.qty_available', '>', 0)
            ->whereNull('t.id')
            ->groupBy('v.id')
            ->limit(10)
            ->select(
                'v.id as productId',
                'p.name as productName',
                'v.sub_sku as sku',
                DB::raw('SUM(vld.qty_available) as stockQuantity')
            )->get()->map(function ($item) {
                return [
                    'productId' => (int) $item->productId,
                    'productName' => $item->productName,
                    'sku' => $item->sku,
                    'stockQuantity' => (float) $item->stockQuantity,
                    'lastSoldAt' => null,
                    'daysWithoutSale' => 30
                ];
            });
    }

    /**
     * Customer acquisition and spending trends.
     */
    public function customers(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;
        $from = $request->get('from', Carbon::now()->subMonth()->format('Y-m-d'));
        $to = $request->get('to', Carbon::now()->format('Y-m-d'));

        $diffInDays = Carbon::parse($from)->diffInDays(Carbon::parse($to));
        $prevFrom = Carbon::parse($from)->subDays($diffInDays + 1)->format('Y-m-d');
        $prevTo = Carbon::parse($from)->subDay()->format('Y-m-d');

        // Current Period New Customers
        $newCustomers = DB::table('contacts')
            ->where('business_id', $business_id)
            ->where('type', 'customer')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->count();

        // Previous Period New Customers for Change Percent
        $prevNewCustomers = DB::table('contacts')
            ->where('business_id', $business_id)
            ->where('type', 'customer')
            ->whereDate('created_at', '>=', $prevFrom)
            ->whereDate('created_at', '<=', $prevTo)
            ->count();

        $newCustomersChangePercent = 0;
        if ($prevNewCustomers > 0) {
            $newCustomersChangePercent = round((($newCustomers - $prevNewCustomers) / $prevNewCustomers) * 100, 2);
        } elseif ($newCustomers > 0) {
            $newCustomersChangePercent = 100;
        }

        $totalCustomers = DB::table('contacts')
            ->where('business_id', $business_id)
            ->where('type', 'customer')
            ->count();

        $spendingStats = DB::table('transactions')
            ->where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->select(
                DB::raw('COUNT(DISTINCT contact_id) as activeCustomers'),
                DB::raw('SUM(final_total) as totalSpend')
            )->first();

        $topSpenders = DB::table('transactions as t')
            ->join('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('t.contact_id')
            ->orderByDesc('totalSpend')
            ->limit(10)
            ->select(
                'c.id as customerId',
                'c.name',
                DB::raw('SUM(t.final_total) as totalSpend'),
                DB::raw('COUNT(*) as visitCount')
            )->get()->map(function ($item) {
                return [
                    'customerId' => (int) $item->customerId,
                    'name' => !empty($item->name) ? $item->name : 'Walk-In Customer',
                    'totalSpend' => (float) $item->totalSpend,
                    'visitCount' => (int) $item->visitCount
                ];
            });

        // Acquisition by Day (for trendlines)
        $acquisitionByDay = DB::table('contacts')
            ->where('business_id', $business_id)
            ->where('type', 'customer')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )->get()->map(function ($item) {
                return [
                    'date' => $item->date,
                    'newCustomers' => (int) $item->count
                ];
            });

        return $this->success([
            'from' => $from,
            'to' => $to,
            'totalCustomers' => $totalCustomers,
            'newCustomers' => $newCustomers,
            'newCustomersChangePercent' => $newCustomersChangePercent,
            'returningCustomers' => (int) $spendingStats->activeCustomers,
            'averageSpendPerCustomer' => $spendingStats->activeCustomers > 0 ? round($spendingStats->totalSpend / $spendingStats->activeCustomers, 2) : 0,
            'topSpenders' => $topSpenders,
            'acquisitionByDay' => $acquisitionByDay
        ]);
    }

    /**
     * Per-cashier performance for a date or shift.
     */
    public function dailyPerformance(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        $cashiers = DB::table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.created_by')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', $date)
            ->groupBy('t.created_by')
            ->select(
                'u.id as userId',
                'u.first_name',
                'u.last_name',
                DB::raw('COUNT(*) as transactionCount'),
                DB::raw('SUM(t.final_total) as totalRevenue')
            )->get()->map(function ($item) {
                return [
                    'userId' => (int) $item->userId,
                    'name' => trim($item->first_name . ' ' . $item->last_name),
                    'shiftLabel' => 'N/A',
                    'transactionCount' => (int) $item->transactionCount,
                    'totalRevenue' => (float) $item->totalRevenue,
                    'averageTransactionValue' => $item->transactionCount > 0 ? round($item->totalRevenue / $item->transactionCount, 2) : 0,
                    'voidCount' => 0,
                    'refundCount' => 0,
                    'topProductsSold' => []
                ];
            });

        return $this->success([
            'date' => $date,
            'cashiers' => $cashiers,
            'storeTotals' => [
                'transactionCount' => $cashiers->sum('transactionCount'),
                'totalRevenue' => $cashiers->sum('totalRevenue'),
                'voidCount' => 0,
                'refundTotal' => 0.0
            ]
        ]);
    }

    /**
     * Supplier and procurement cost summary.
     */
    public function purchases(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;
        $from = $request->get('from', Carbon::now()->subMonth()->format('Y-m-d'));
        $to = $request->get('to', Carbon::now()->format('Y-m-d'));

        $diffInDays = Carbon::parse($from)->diffInDays(Carbon::parse($to));
        $prevFrom = Carbon::parse($from)->subDays($diffInDays + 1)->format('Y-m-d');
        $prevTo = Carbon::parse($from)->subDay()->format('Y-m-d');

        $stats = DB::table('transactions')
            ->where('business_id', $business_id)
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->whereDate('transaction_date', '>=', $from)
            ->whereDate('transaction_date', '<=', $to)
            ->select(
                DB::raw('SUM(final_total) as totalCost'),
                DB::raw('COUNT(*) as totalOrders')
            )->first();

        // Previous stats for change calculation
        $prevStats = DB::table('transactions')
            ->where('business_id', $business_id)
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->whereDate('transaction_date', '>=', $prevFrom)
            ->whereDate('transaction_date', '<=', $prevTo)
            ->select(
                DB::raw('SUM(final_total) as totalCost'),
                DB::raw('COUNT(*) as totalOrders')
            )->first();

        $costChangePercent = 0;
        if (($prevStats->totalCost ?? 0) > 0) {
            $costChangePercent = round((($stats->totalCost - $prevStats->totalCost) / $prevStats->totalCost) * 100, 2);
        } elseif (($stats->totalCost ?? 0) > 0) {
            $costChangePercent = 100;
        }

        $ordersChangePercent = 0;
        if (($prevStats->totalOrders ?? 0) > 0) {
            $ordersChangePercent = round((($stats->totalOrders - $prevStats->totalOrders) / $prevStats->totalOrders) * 100, 2);
        } elseif (($stats->totalOrders ?? 0) > 0) {
            $ordersChangePercent = 100;
        }

        // By Supplier
        $bySupplier = DB::table('transactions as t')
            ->join('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->where('t.status', 'received')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('t.contact_id')
            ->select(
                'c.id as supplierId',
                'c.name as supplierName',
                DB::raw('SUM(t.final_total) as totalCost'),
                DB::raw('COUNT(*) as orderCount'),
                DB::raw('MAX(t.transaction_date) as lastOrderDate')
            )->get()->map(function ($item) {
                return [
                    'supplierId' => (int) $item->supplierId,
                    'supplierName' => $item->supplierName,
                    'totalCost' => (float) $item->totalCost,
                    'orderCount' => (int) $item->orderCount,
                    'lastOrderDate' => $item->lastOrderDate
                ];
            });

        // By Category
        $byCategory = DB::table('purchase_lines as pl')
            ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
            ->join('products as p', 'p.id', '=', 'pl.product_id')
            ->leftjoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->where('t.status', 'received')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('c.id')
            ->select(
                DB::raw('COALESCE(c.name, "Uncategorized") as categoryName'),
                DB::raw('SUM(pl.quantity * pl.purchase_price) as totalCost')
            )->get();

        $totalCategoryCost = $byCategory->sum('totalCost');

        $formattedByCategory = $byCategory->map(function ($item) use ($totalCategoryCost) {
            return [
                'categoryName' => $item->categoryName,
                'totalCost' => (float) $item->totalCost,
                'percent' => $totalCategoryCost > 0 ? round(($item->totalCost / $totalCategoryCost) * 100, 2) : 0
            ];
        });

        // Supplier Performance (Fulfillment Rate)
        $supplierPerformance = DB::table('purchase_lines as pl')
            ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
            ->join('contacts as c', 'c.id', '=', 't.contact_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->where('t.status', 'received')
            ->whereDate('t.transaction_date', '>=', $from)
            ->whereDate('t.transaction_date', '<=', $to)
            ->groupBy('t.contact_id')
            ->select(
                'c.id as supplierId',
                'c.name as supplierName',
                DB::raw('SUM(pl.quantity) as qty_received')
            )->get()->map(function ($item) {
                // Since we are filtering by 'received' status, quantity is fulfillment
                // In a more complex scenario, we'd compare against the original Purchase Order
                return [
                    'supplierId' => (int) $item->supplierId,
                    'supplierName' => $item->supplierName,
                    'fulfillmentRate' => 1.0 // Defaulting to 100% for received purchases
                ];
            });

        return $this->success([
            'from' => $from,
            'to' => $to,
            'totalPurchaseCost' => (float) $stats->totalCost,
            'totalPurchaseCostChangePercent' => $costChangePercent,
            'totalOrders' => (int) $stats->totalOrders,
            'totalOrdersChangePercent' => $ordersChangePercent,
            'bySupplier' => $bySupplier,
            'byCategory' => $formattedByCategory,
            'supplierPerformance' => $supplierPerformance
        ]);
    }
}
