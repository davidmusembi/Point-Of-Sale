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
        $query = $this->productUtil->getProductStockDetails($business_id, $filters, 'query');
        $products = $query->paginate($perPage);

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
     * Trending Products Report.
     */
    public function getTrendingProducts(Request $request)
    {
        $user = $request->user();
        if (!$user->can('trending_product_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view trending products report.', null, 403);
        }

        $business_id = $user->business_id;
        $filters = $request->only(['category', 'sub_category', 'brand', 'unit', 'limit', 'location_id', 'product_type', 'start_date', 'end_date']);

        $products = $this->productUtil->getTrendingProducts($business_id, $filters);

        return $this->success($products);
    }

    /**
     * Product Purchase Report.
     */
    public function getProductPurchaseReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view product purchase report.', null, 403);
        }

        $business_id = $user->business_id;

        $variation_id = $request->get('variation_id', null);
        $query = \App\PurchaseLine::join('transactions as t', 'purchase_lines.transaction_id', '=', 't.id')
            ->join('variations as v', 'purchase_lines.variation_id', '=', 'v.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->select(
                'p.name as product_name',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as supplier',
                'c.supplier_business_name',
                't.id as transaction_id',
                't.ref_no',
                't.transaction_date',
                'purchase_lines.purchase_price_inc_tax as unit_purchase_price',
                DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                'purchase_lines.quantity_adjusted',
                'u.short_name as unit',
                DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned - purchase_lines.quantity_adjusted) * purchase_lines.purchase_price_inc_tax) as subtotal')
            )
            ->groupBy('purchase_lines.id');

        if (!empty($variation_id)) {
            $query->where('purchase_lines.variation_id', $variation_id);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $supplier_id = $request->get('supplier_id', null);
        if (!empty($supplier_id)) {
            $query->where('t.contact_id', $supplier_id);
        }

        $brand_id = $request->get('brand_id', null);
        if (!empty($brand_id)) {
            $query->where('p.brand_id', $brand_id);
        }

        $perPage = $request->get('perPage', 20);
        $purchases = $query->paginate($perPage);

        return $this->paginate($purchases);
    }

    /**
     * Product Sell Report.
     */
    public function getProductSellReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view product sell report.', null, 403);
        }

        $business_id = $user->business_id;

        $variation_id = $request->get('variation_id', null);
        $query = \App\TransactionSellLine::join('transactions as t', 'transaction_sell_lines.transaction_id', '=', 't.id')
            ->join('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftjoin('tax_rates', 'transaction_sell_lines.tax_id', '=', 'tax_rates.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->with('transaction.payment_lines')
            ->select(
                'p.name as product_name',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                'c.name as customer',
                'c.supplier_business_name',
                'c.contact_id',
                't.id as transaction_id',
                't.invoice_no',
                't.transaction_date as transaction_date',
                'transaction_sell_lines.unit_price_before_discount as unit_price',
                'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
                'transaction_sell_lines.line_discount_type as discount_type',
                'transaction_sell_lines.line_discount_amount as discount_amount',
                'transaction_sell_lines.item_tax',
                'tax_rates.name as tax',
                'u.short_name as unit',
                'transaction_sell_lines.parent_sell_line_id',
                DB::raw('((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
            )
            ->groupBy('transaction_sell_lines.id');

        if (!empty($variation_id)) {
            $query->where('transaction_sell_lines.variation_id', $variation_id);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('t.transaction_date', '>=', $start_date)
                ->whereDate('t.transaction_date', '<=', $end_date);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $customer_id = $request->get('customer_id', null);
        if (!empty($customer_id)) {
            $query->where('t.contact_id', $customer_id);
        }

        $customer_group_id = $request->get('customer_group_id', null);
        if (!empty($customer_group_id)) {
            $query->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                ->where('CG.id', $customer_group_id);
        }

        $category_id = $request->get('category_id', null);
        if (!empty($category_id)) {
            $query->where('p.category_id', $category_id);
        }

        $brand_id = $request->get('brand_id', null);
        if (!empty($brand_id)) {
            $query->where('p.brand_id', $brand_id);
        }

        $perPage = $request->get('perPage', 20);
        $sells = $query->paginate($perPage);

        return $this->paginate($sells);
    }

    /**
     * Product Sell Grouped Report.
     */
    public function getProductSellGroupedReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view product sell grouped report.', null, 403);
        }

        $business_id = $user->business_id;
        $location_id = $request->get('location_id', null);

        $vld_str = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
        }

        $variation_id = $request->get('variation_id', null);
        $query = \App\TransactionSellLine::join('transactions as t', 'transaction_sell_lines.transaction_id', '=', 't.id')
            ->join('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->select(
                'p.name as product_name',
                'p.enable_stock',
                'p.type as product_type',
                'pv.name as product_variation',
                'v.name as variation_name',
                'v.sub_sku',
                't.id as transaction_id',
                't.transaction_date as transaction_date',
                'transaction_sell_lines.parent_sell_line_id',
                DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"),
                DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as total_qty_sold'),
                'u.short_name as unit',
                DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
            )
            ->groupBy('v.id')
            ->groupBy('formated_date');

        if (!empty($variation_id)) {
            $query->where('transaction_sell_lines.variation_id', $variation_id);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('t.transaction_date', '>=', $start_date)
                ->whereDate('t.transaction_date', '<=', $end_date);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $customer_id = $request->get('customer_id', null);
        if (!empty($customer_id)) {
            $query->where('t.contact_id', $customer_id);
        }

        $customer_group_id = $request->get('customer_group_id', null);
        if (!empty($customer_group_id)) {
            $query->leftjoin('contacts AS c', 't.contact_id', '=', 'c.id')
                ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                ->where('CG.id', $customer_group_id);
        }

        $category_id = $request->get('category_id', null);
        if (!empty($category_id)) {
            $query->where('p.category_id', $category_id);
        }

        $brand_id = $request->get('brand_id', null);
        if (!empty($brand_id)) {
            $query->where('p.brand_id', $brand_id);
        }

        $perPage = $request->get('perPage', 20);
        $groupedSells = $query->paginate($perPage);

        return $this->paginate($groupedSells);
    }

    /**
     * Items Report.
     */
    public function itemsReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view items report.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\TransactionSellLinesPurchaseLines::leftJoin('transaction_sell_lines as SL', 'SL.id', '=', 'transaction_sell_lines_purchase_lines.sell_line_id')
            ->leftJoin('stock_adjustment_lines as SAL', 'SAL.id', '=', 'transaction_sell_lines_purchase_lines.stock_adjustment_line_id')
            ->leftJoin('transactions as sale', 'SL.transaction_id', '=', 'sale.id')
            ->leftJoin('transactions as stock_adjustment', 'SAL.transaction_id', '=', 'stock_adjustment.id')
            ->join('purchase_lines as PL', 'PL.id', '=', 'transaction_sell_lines_purchase_lines.purchase_line_id')
            ->join('transactions as purchase', 'PL.transaction_id', '=', 'purchase.id')
            ->join('business_locations as bl', 'purchase.location_id', '=', 'bl.id')
            ->join('variations as v', 'PL.variation_id', '=', 'v.id')
            ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->join('products as p', 'PL.product_id', '=', 'p.id')
            ->join('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('contacts as suppliers', 'purchase.contact_id', '=', 'suppliers.id')
            ->leftJoin('contacts as customers', 'sale.contact_id', '=', 'customers.id')
            ->where('purchase.business_id', $business_id)
            ->select(
                'v.sub_sku as sku',
                'p.type as product_type',
                'p.name as product_name',
                'v.name as variation_name',
                'pv.name as product_variation',
                'u.short_name as unit',
                'purchase.transaction_date as purchase_date',
                'purchase.ref_no as purchase_ref_no',
                'purchase.type as purchase_type',
                'purchase.id as purchase_id',
                'suppliers.name as supplier',
                'suppliers.supplier_business_name',
                'PL.purchase_price_inc_tax as purchase_price',
                'sale.transaction_date as sell_date',
                'stock_adjustment.transaction_date as stock_adjustment_date',
                'sale.invoice_no as sale_invoice_no',
                'stock_adjustment.ref_no as stock_adjustment_ref_no',
                'customers.name as customer',
                'customers.supplier_business_name as customer_business_name',
                'transaction_sell_lines_purchase_lines.quantity as quantity',
                'SL.unit_price_inc_tax as selling_price',
                'SAL.unit_price as stock_adjustment_price',
                'transaction_sell_lines_purchase_lines.stock_adjustment_line_id',
                'transaction_sell_lines_purchase_lines.sell_line_id',
                'transaction_sell_lines_purchase_lines.purchase_line_id',
                'transaction_sell_lines_purchase_lines.qty_returned',
                'bl.name as location',
                'SL.sell_line_note',
                'PL.lot_number'
            );

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('purchase.location_id', $permitted_locations);
        }

        if (!empty($request->purchase_start) && !empty($request->purchase_end)) {
            $query->whereDate('purchase.transaction_date', '>=', $request->purchase_start)
                ->whereDate('purchase.transaction_date', '<=', $request->purchase_end);
        }

        if (!empty($request->sale_start) && !empty($request->sale_end)) {
            $start = $request->sale_start;
            $end = $request->sale_end;
            $query->where(function ($q) use ($start, $end) {
                $q->where(function ($qr) use ($start, $end) {
                    $qr->whereDate('sale.transaction_date', '>=', $start)
                        ->whereDate('sale.transaction_date', '<=', $end);
                })->orWhere(function ($qr) use ($start, $end) {
                    $qr->whereDate('stock_adjustment.transaction_date', '>=', $start)
                        ->whereDate('stock_adjustment.transaction_date', '<=', $end);
                });
            });
        }

        $supplier_id = $request->get('supplier_id', null);
        if (!empty($supplier_id)) {
            $query->where('suppliers.id', $supplier_id);
        }

        $customer_id = $request->get('customer_id', null);
        if (!empty($customer_id)) {
            $query->where('customers.id', $customer_id);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('purchase.location_id', $location_id);
        }

        $only_mfg_products = $request->get('only_mfg_products', 0);
        if (!empty($only_mfg_products)) {
            $query->where('purchase.type', 'production_purchase');
        }

        $perPage = $request->get('perPage', 20);
        $items = $query->paginate($perPage);

        return $this->paginate($items);
    }

    /**
     * Customer & Supplier Report.
     */
    public function getCustomerSuppliers(Request $request)
    {
        $user = $request->user();
        if (!$user->can('contacts_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view contacts report.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\Contact::where('contacts.business_id', $business_id)
            ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->active()
            ->groupBy('contacts.id')
            ->select(
                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                DB::raw("SUM(IF(t.type = 'ledger_discount' AND sub_type='sell_discount', final_total, 0)) as total_ledger_discount_sell"),
                DB::raw("SUM(IF(t.type = 'ledger_discount' AND sub_type='purchase_discount', final_total, 0)) as total_ledger_discount_purchase"),
                'contacts.supplier_business_name',
                'contacts.name',
                'contacts.id',
                'contacts.type as contact_type'
            );

        $permitted_locations = $this->getPermittedLocations();

        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($request->input('customer_group_id'))) {
            $query->where('contacts.customer_group_id', $request->input('customer_group_id'));
        }

        if (!empty($request->input('location_id'))) {
            $query->where('t.location_id', $request->input('location_id'));
        }

        if (!empty($request->input('contact_id'))) {
            $query->where('t.contact_id', $request->input('contact_id'));
        }

        if (!empty($request->input('contact_type'))) {
            $query->whereIn('contacts.type', [$request->input('contact_type'), 'both']);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereDate('t.transaction_date', '>=', $start_date)
                ->whereDate('t.transaction_date', '<=', $end_date);
        }

        $perPage = $request->get('perPage', 20);
        $contacts = $query->paginate($perPage);

        return $this->paginate($contacts);
    }

    /**
     * Customer Group Report.
     */
    public function getCustomerGroup(Request $request)
    {
        $user = $request->user();
        if (!$user->can('contacts_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view customer group report.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\Transaction::leftjoin('customer_groups AS CG', 'transactions.customer_group_id', '=', 'CG.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->groupBy('transactions.customer_group_id')
            ->select(DB::raw('SUM(final_total) as total_sell'), 'CG.name');

        $group_id = $request->get('customer_group_id', null);
        if (!empty($group_id)) {
            $query->where('transactions.customer_group_id', $group_id);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('transactions.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('transactions.location_id', $location_id);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        $perPage = $request->get('perPage', 20);
        $groups = $query->paginate($perPage);

        return $this->paginate($groups);
    }

    /**
     * Sales Representative Report
     */
    public function getSalesRepresentativeReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('sales_representative.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view sales representative report.', null, 403);
        }

        $business_id = $user->business_id;

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $created_by = $request->get('created_by');

        $filters = $request->only(['expense_for', 'location_id', 'start_date', 'end_date']);
        if (!empty($created_by)) {
            $filters['expense_for'] = $created_by;
        }

        $total_expense = $this->transactionUtil->getExpenseReport($business_id, $filters, 'total');

        $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id, $created_by);

        $transaction_types = ['sell_return'];
        $sell_return_details = $this->transactionUtil->getTransactionTotals(
            $business_id,
            $transaction_types,
            $start_date,
            $end_date,
            $location_id,
            $created_by
        );

        $total_sell_return = !empty($sell_return_details['total_sell_return_exc_tax']) ? $sell_return_details['total_sell_return_exc_tax'] : 0;
        $total_sell = $sell_details['total_sell_exc_tax'] - $total_sell_return;

        $commission = 0;
        if (!empty($created_by)) {
            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $commsn_calculation_type = empty($pos_settings['cmmsn_calculation_type']) || $pos_settings['cmmsn_calculation_type'] == 'invoice_value' ? 'invoice_value' : $pos_settings['cmmsn_calculation_type'];

            $rep = \App\User::find($created_by);
            if ($rep) {
                $commission_percentage = $rep->cmmsn_percent;

                if ($commsn_calculation_type == 'payment_received') {
                    $payment_details = $this->transactionUtil->getTotalPaymentWithCommission($business_id, $start_date, $end_date, $location_id, $created_by);
                    $commission = $commission_percentage * ($payment_details['total_payment_with_commission'] ?? 0) / 100;
                } else {
                    $sell_comm_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, $location_id, $created_by);
                    $commission = $commission_percentage * ($sell_comm_details['total_sales_with_commission'] ?? 0) / 100;
                }
            }
        }

        return $this->success([
            'total_expense' => $total_expense,
            'total_sell_exc_tax' => $sell_details['total_sell_exc_tax'],
            'total_sell_return_exc_tax' => $total_sell_return,
            'total_sell' => $total_sell,
            'total_commission' => $commission,
        ]);
    }

    /**
     * Service Staff Report.
     */
    public function getServiceStaffReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('sales_representative.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view service staff report.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\TransactionSellLine::leftJoin('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->leftJoin('variations as v', 'transaction_sell_lines.variation_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('units as u', 'p.unit_id', '=', 'u.id')
            ->leftJoin('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftJoin('users as ss', 'ss.id', '=', 'transaction_sell_lines.res_service_staff_id')
            ->leftjoin(
                'business_locations AS bl',
                't.location_id',
                '=',
                'bl.id'
            )
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereNotNull('transaction_sell_lines.res_service_staff_id');

        if (!empty($request->service_staff_id)) {
            $query->where('transaction_sell_lines.res_service_staff_id', $request->service_staff_id);
        }

        if ($request->has('location_id')) {
            $location_id = $request->get('location_id');
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }
        }

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
            $query->whereDate('t.transaction_date', '>=', $start)
                ->whereDate('t.transaction_date', '<=', $end);
        }

        $query->select(
            'p.name as product_name',
            'p.type as product_type',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            'u.short_name as unit',
            't.id as transaction_id',
            'bl.name as business_location',
            't.transaction_date',
            't.invoice_no',
            'transaction_sell_lines.quantity',
            'transaction_sell_lines.unit_price_before_discount',
            'transaction_sell_lines.line_discount_type',
            'transaction_sell_lines.line_discount_amount',
            'transaction_sell_lines.item_tax',
            'transaction_sell_lines.unit_price_inc_tax',
            DB::raw('CONCAT(COALESCE(ss.first_name, ""), " ", COALESCE(ss.last_name, "")) as service_staff'),
            DB::raw('(transaction_sell_lines.quantity * transaction_sell_lines.unit_price_inc_tax) as total')
        );

        $perPage = $request->get('perPage', 20);
        $staffReport = $query->paginate($perPage);

        return $this->paginate($staffReport);
    }

    /**
     * Purchase Payment Report.
     */
    public function purchasePaymentReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view purchase payment report.', null, 403);
        }

        $business_id = $user->business_id;

        $supplier_id = $request->get('supplier_id', null);
        $contact_filter1 = !empty($supplier_id) ? "AND t.contact_id=$supplier_id" : '';
        $contact_filter2 = !empty($supplier_id) ? "AND transactions.contact_id=$supplier_id" : '';

        $location_id = $request->get('location_id', null);
        $parent_payment_query_part = empty($location_id) ? 'AND transaction_payments.parent_id IS NULL' : '';

        $query = \App\TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
            $join->on('transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->whereIn('t.type', ['purchase', 'opening_balance']);
        })
            ->where('transaction_payments.business_id', $business_id)
            ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('purchase', 'opening_balance')  $parent_payment_query_part $contact_filter1)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('purchase', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
            })
            ->select(
                DB::raw("IF(transaction_payments.transaction_id IS NULL, 
                            (SELECT c.name FROM transactions as ts
                            JOIN contacts as c ON ts.contact_id=c.id 
                            WHERE ts.id=(
                                    SELECT tps.transaction_id FROM transaction_payments as tps
                                    WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                )
                            ),
                            (SELECT CONCAT(COALESCE(c.supplier_business_name, ''), ' - ', c.name) FROM transactions as ts JOIN
                                contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=t.id 
                            )
                        ) as supplier"),
                'transaction_payments.amount',
                'method',
                'paid_on',
                'transaction_payments.payment_ref_no',
                'transaction_payments.document',
                't.ref_no',
                't.id as transaction_id',
                'cheque_number',
                'card_transaction_number',
                'bank_account_number',
                'transaction_no',
                'transaction_payments.id as payment_id'
            )
            ->groupBy('transaction_payments.id');

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $perPage = $request->get('perPage', 20);
        $payments = $query->paginate($perPage);

        return $this->paginate($payments);
    }

    /**
     * Sell Payment Report.
     */
    public function sellPaymentReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view sell payment report.', null, 403);
        }

        $business_id = $user->business_id;

        $customer_id = $request->get('customer_id', null);
        $contact_filter1 = !empty($customer_id) ? "AND t.contact_id=$customer_id" : '';
        $contact_filter2 = !empty($customer_id) ? "AND transactions.contact_id=$customer_id" : '';

        $location_id = $request->get('location_id', null);
        $parent_payment_query_part = empty($location_id) ? 'AND transaction_payments.parent_id IS NULL' : '';

        $query = \App\TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
            $join->on('transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->whereIn('t.type', ['sell', 'opening_balance']);
        })
            ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
            ->where('transaction_payments.business_id', $business_id)
            ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2, $parent_payment_query_part) {
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type IN ('sell', 'opening_balance') $parent_payment_query_part $contact_filter1)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type IN ('sell', 'opening_balance') AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
            })
            ->select(
                DB::raw("IF(transaction_payments.transaction_id IS NULL, 
                            (SELECT c.name FROM transactions as ts
                            JOIN contacts as c ON ts.contact_id=c.id 
                            WHERE ts.id=(
                                    SELECT tps.transaction_id FROM transaction_payments as tps
                                    WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                )
                            ),
                            (SELECT CONCAT(COALESCE(CONCAT(c.supplier_business_name, ' - '), ''), c.name) FROM transactions as ts JOIN
                                contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=t.id 
                            )
                        ) as customer"),
                'transaction_payments.amount',
                'transaction_payments.is_return',
                'method',
                'paid_on',
                'transaction_payments.payment_ref_no',
                'transaction_payments.document',
                'transaction_payments.transaction_no',
                't.invoice_no',
                't.id as transaction_id',
                'cheque_number',
                'card_transaction_number',
                'bank_account_number',
                'transaction_payments.id as payment_id',
                'CG.name as customer_group'
            )
            ->groupBy('transaction_payments.id');

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        if (!empty($request->get('customer_group_id'))) {
            $query->where('CG.id', $request->get('customer_group_id'));
        }

        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        if (!empty($request->has('commission_agent'))) {
            $query->where('t.commission_agent', $request->get('commission_agent'));
        }

        if (!empty($request->get('payment_types'))) {
            $query->where('transaction_payments.method', $request->get('payment_types'));
        }

        $perPage = $request->get('perPage', 20);
        $payments = $query->paginate($perPage);

        return $this->paginate($payments);
    }

    /**
     * Table Report.
     */
    public function getTableReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('purchase_n_sell_report.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view table report.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\Restaurant\ResTable::leftjoin('transactions AS T', 'T.res_table_id', '=', 'res_tables.id')
            ->where('T.business_id', $business_id)
            ->where('T.type', 'sell')
            ->where('T.status', 'final')
            ->groupBy('res_tables.id')
            ->select(DB::raw('SUM(final_total) as total_sell'), 'res_tables.name as table');

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('T.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('T.location_id', $location_id);
        }

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
        }

        $perPage = $request->get('perPage', 20);
        $tables = $query->paginate($perPage);

        return $this->paginate($tables);
    }

    /**
     * Retrives account balances.
     *
     * @return Obj
     */
    private function getAccountBalance($business_id, $end_date, $account_type = 'others', $location_id = null)
    {
        $query = \App\Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            ->whereNull('AT.deleted_at')
            ->where('business_id', $business_id)
            ->whereDate('AT.operation_date', '<=', $end_date);

        $permitted_locations = $this->getPermittedLocations();
        $account_ids = [];
        if ($permitted_locations != 'all') {
            $locations = \App\BusinessLocation::where('business_id', $business_id)
                ->whereIn('id', $permitted_locations)
                ->get();

            foreach ($locations as $location) {
                if (!empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    foreach ($default_payment_accounts as $key => $account) {
                        if (!empty($account['is_enabled']) && !empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }
                }
            }

            $account_ids = array_unique($account_ids);
        }

        if ($permitted_locations != 'all') {
            $query->whereIn('accounts.id', $account_ids);
        }

        if (!empty($location_id)) {
            $location = \App\BusinessLocation::find($location_id);
            if (!empty($location->default_payment_accounts)) {
                $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                $account_ids = [];
                foreach ($default_payment_accounts as $key => $account) {
                    if (!empty($account['is_enabled']) && !empty($account['account'])) {
                        $account_ids[] = $account['account'];
                    }
                }

                $query->whereIn('accounts.id', $account_ids);
            }
        }

        $account_details = $query->select([
            'name',
            DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance"),
        ])
            ->groupBy('accounts.id')
            ->get()
            ->pluck('balance', 'name');

        return $account_details;
    }

    /**
     * Balance Sheet Report.
     */
    public function balanceSheet(Request $request)
    {
        $user = $request->user();
        if (!$user->can('account.access')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view balance sheet.', null, 403);
        }

        $business_id = $user->business_id;

        $end_date = !empty($request->input('end_date')) ? $this->transactionUtil->uf_date($request->input('end_date')) : \Carbon\Carbon::now()->format('Y-m-d');
        $location_id = !empty($request->input('location_id')) ? $request->input('location_id') : null;

        $purchase_details = $this->transactionUtil->getPurchaseTotals(
            $business_id,
            null,
            $end_date,
            $location_id
        );
        $sell_details = $this->transactionUtil->getSellTotals(
            $business_id,
            null,
            $end_date,
            $location_id
        );

        $transaction_types = ['sell_return'];

        $sell_return_details = $this->transactionUtil->getTransactionTotals(
            $business_id,
            $transaction_types,
            null,
            $end_date,
            $location_id
        );

        $account_details = $this->getAccountBalance($business_id, $end_date, 'others', $location_id);

        $permitted_locations = $this->getPermittedLocations();

        $closing_stock = $this->transactionUtil->getOpeningClosingStock(
            $business_id,
            $end_date,
            $location_id,
            $permitted_locations
        );

        return $this->success([
            'supplier_due' => $purchase_details['purchase_due'],
            'customer_due' => $sell_details['invoice_due'] - $sell_return_details['total_sell_return_inc_tax'],
            'account_balances' => $account_details,
            'closing_stock' => $closing_stock,
            'capital_account_details' => null,
        ]);
    }

    /**
     * Trial Balance Report.
     */
    public function trialBalance(Request $request)
    {
        $user = $request->user();
        if (!$user->can('account.access')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view trial balance.', null, 403);
        }

        $business_id = $user->business_id;

        $end_date = !empty($request->input('end_date')) ? $this->transactionUtil->uf_date($request->input('end_date')) : \Carbon\Carbon::now()->format('Y-m-d');
        $location_id = !empty($request->input('location_id')) ? $request->input('location_id') : null;

        $purchase_details = $this->transactionUtil->getPurchaseTotals(
            $business_id,
            null,
            $end_date,
            $location_id
        );
        $sell_details = $this->transactionUtil->getSellTotals(
            $business_id,
            null,
            $end_date,
            $location_id
        );

        $account_details = $this->getAccountBalance($business_id, $end_date, 'others', $location_id);

        return $this->success([
            'supplier_due' => $purchase_details['purchase_due'],
            'customer_due' => $sell_details['invoice_due'],
            'account_balances' => $account_details,
            'capital_account_details' => null,
        ]);
    }

    /**
     * Payment Account Report.
     */
    public function paymentAccountReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('account.access')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view payment account report.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\TransactionPayment::leftjoin(
            'transactions as T',
            'transaction_payments.transaction_id',
            '=',
            'T.id'
        )
            ->leftjoin('accounts as A', 'transaction_payments.account_id', '=', 'A.id')
            ->where('transaction_payments.business_id', $business_id)
            ->whereNull('transaction_payments.parent_id')
            ->where('transaction_payments.method', '!=', 'advance')
            ->leftjoin('contacts as c', 'transaction_payments.payment_for', '=', 'c.id')
            ->select([
                'paid_on',
                'payment_ref_no',
                'T.ref_no',
                'T.invoice_no',
                'T.type',
                'T.id as transaction_id',
                'A.name as account_name',
                'A.account_number',
                'transaction_payments.id as payment_id',
                'transaction_payments.account_id',
                'c.name as contact_name',
                'c.type as contact_type',
                'transaction_payments.is_advance',
                'transaction_payments.amount',
            ]);

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('T.location_id', $permitted_locations);
        }

        $start_date = !empty($request->input('start_date')) ? $request->input('start_date') : '';
        $end_date = !empty($request->input('end_date')) ? $request->input('end_date') : '';

        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
        }

        $account_id = !empty($request->input('account_id')) ? $request->input('account_id') : '';

        if ($account_id == 'none') {
            $query->whereNull('account_id');
        } elseif (!empty($account_id)) {
            $query->where('account_id', $account_id);
        }

        $perPage = $request->get('perPage', 20);
        $reports = $query->paginate($perPage);

        return $this->paginate($reports);
    }

    /**
     * Activity Log Report.
     */
    public function activityLog(Request $request)
    {
        $user = $request->user();

        $business_id = $user->business_id;

        $activities = \Spatie\Activitylog\Models\Activity::with(['subject'])
            ->leftjoin('users as u', 'u.id', '=', 'activity_log.causer_id')
            ->where('activity_log.business_id', $business_id)
            ->select(
                'activity_log.*',
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by")
            );

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
            $activities->whereDate('activity_log.created_at', '>=', $start)
                ->whereDate('activity_log.created_at', '<=', $end);
        }

        if (!empty($request->user_id)) {
            $activities->where('causer_id', $request->user_id);
        }

        $subject_type = $request->subject_type;
        if (!empty($subject_type)) {
            if ($subject_type == 'contact') {
                $activities->where('subject_type', \App\Contact::class);
            } elseif ($subject_type == 'user') {
                $activities->where('subject_type', \App\User::class);
            } elseif (in_array($subject_type, ['sell', 'purchase', 'sales_order', 'purchase_order', 'sell_return', 'purchase_return', 'sell_transfer', 'expense'])) {
                $activities->where('subject_type', \App\Transaction::class);
                $activities->whereHasMorph('subject', \App\Transaction::class, function ($q) use ($subject_type) {
                    $q->where('type', $subject_type);
                });
            }
        }

        $perPage = $request->get('perPage', 20);
        $logs = $activities->paginate($perPage);

        return $this->paginate($logs);
    }

    /**
     * GST Sales Report.
     */
    public function gstSalesReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('tax_report.view') || empty(config('constants.enable_gst_report_india'))) {
            return $this->error('UNAUTHORIZED', 'Unauthorized action or GST disabled.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\TransactionSellLine::join(
            'transactions as t',
            'transaction_sell_lines.transaction_id',
            '=',
            't.id'
        )
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'transaction_sell_lines.product_id', '=', 'p.id')
            ->leftjoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->leftjoin('tax_rates as tr', 'transaction_sell_lines.tax_id', '=', 'tr.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->select(
                'c.name as customer',
                'c.supplier_business_name',
                'c.contact_id',
                'c.tax_number',
                'cat.short_code',
                't.id as transaction_id',
                't.invoice_no',
                't.transaction_date as transaction_date',
                'transaction_sell_lines.unit_price_before_discount as unit_price',
                'transaction_sell_lines.unit_price as unit_price_after_discount',
                DB::raw('(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as sell_qty'),
                'transaction_sell_lines.line_discount_type as discount_type',
                'transaction_sell_lines.line_discount_amount as discount_amount',
                'transaction_sell_lines.item_tax',
                'tr.amount as tax_percent',
                'tr.is_tax_group',
                'transaction_sell_lines.tax_id',
                'u.short_name as unit',
                'transaction_sell_lines.parent_sell_line_id',
                DB::raw('((transaction_sell_lines.quantity- transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as line_total')
            )
            ->groupBy('transaction_sell_lines.id');

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->where('t.transaction_date', '>=', $start_date)
                ->where('t.transaction_date', '<=', $end_date);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $customer_id = $request->get('customer_id', null);
        if (!empty($customer_id)) {
            $query->where('t.contact_id', $customer_id);
        }

        $perPage = $request->get('perPage', 20);
        $sales = $query->paginate($perPage);

        return $this->paginate($sales);
    }

    /**
     * GST Purchase Report.
     */
    public function gstPurchaseReport(Request $request)
    {
        $user = $request->user();
        if (!$user->can('tax_report.view') || empty(config('constants.enable_gst_report_india'))) {
            return $this->error('UNAUTHORIZED', 'Unauthorized action or GST disabled.', null, 403);
        }

        $business_id = $user->business_id;

        $query = \App\PurchaseLine::join(
            'transactions as t',
            'purchase_lines.transaction_id',
            '=',
            't.id'
        )
            ->join('contacts as c', 't.contact_id', '=', 'c.id')
            ->join('products as p', 'purchase_lines.product_id', '=', 'p.id')
            ->leftjoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->leftjoin('tax_rates as tr', 'purchase_lines.tax_id', '=', 'tr.id')
            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'purchase')
            ->where('t.status', 'received')
            ->select(
                'c.name as supplier',
                'c.supplier_business_name',
                'c.contact_id',
                'c.tax_number',
                'cat.short_code',
                't.id as transaction_id',
                't.ref_no',
                't.transaction_date as transaction_date',
                'purchase_lines.pp_without_discount as unit_price',
                'purchase_lines.purchase_price as unit_price_after_discount',
                DB::raw('(purchase_lines.quantity - purchase_lines.quantity_returned) as purchase_qty'),
                'purchase_lines.discount_percent',
                'purchase_lines.item_tax',
                'tr.amount as tax_percent',
                'tr.is_tax_group',
                'purchase_lines.tax_id',
                'u.short_name as unit',
                DB::raw('((purchase_lines.quantity - purchase_lines.quantity_returned) * purchase_lines.purchase_price_inc_tax) as line_total')
            )
            ->groupBy('purchase_lines.id');

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->where('t.transaction_date', '>=', $start_date)
                ->where('t.transaction_date', '<=', $end_date);
        }

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }

        $supplier_id = $request->get('supplier_id', null);
        if (!empty($supplier_id)) {
            $query->where('t.contact_id', $supplier_id);
        }

        $perPage = $request->get('perPage', 20);
        $purchases = $query->paginate($perPage);

        return $this->paginate($purchases);
    }

    /**
     * Daily cashier performance snapshot.
     */
    public function dailyPerformance(Request $request)
    {
        $user = $request->user();
        if (!$user->can('sales_representative.view') && !$user->can('sell.view')) {
            return $this->error('UNAUTHORIZED', 'Unauthorized to view daily performance.', null, 403);
        }

        $business_id = $user->business_id;
        $date = $request->get('date', \Carbon\Carbon::today()->format('Y-m-d'));

        $query = DB::table('transactions as t')
            ->join('users as u', 'u.id', '=', 't.created_by')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', $date);

        $permitted_locations = $this->getPermittedLocations();
        if ($permitted_locations != 'all') {
            $query->whereIn('t.location_id', $permitted_locations);
        }

        // Get Refund Data
        $refundsQuery = DB::table('transactions as t')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell_return')
            ->whereDate('t.transaction_date', $date);

        if ($permitted_locations != 'all') {
            $refundsQuery->whereIn('t.location_id', $permitted_locations);
        }

        $refundsData = $refundsQuery->groupBy('t.created_by')
            ->select(
                't.created_by',
                DB::raw('COUNT(*) as refundCount'),
                DB::raw('SUM(t.final_total) as refundTotal')
            )->get()->keyBy('created_by');

        // Get Top Products Sold
        $productsQuery = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->join('products as p', 'p.id', '=', 'tsl.product_id')
            ->where('t.business_id', $business_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', $date);

        if ($permitted_locations != 'all') {
            $productsQuery->whereIn('t.location_id', $permitted_locations);
        }

        $topProductsData = $productsQuery->groupBy('t.created_by', 'p.id', 'p.name')
            ->select(
                't.created_by',
                'p.id as product_id',
                'p.name',
                DB::raw('SUM(tsl.quantity - tsl.quantity_returned) as total_quantity'),
                DB::raw('SUM((tsl.quantity - tsl.quantity_returned) * tsl.unit_price_inc_tax) as total_revenue')
            )
            ->having('total_quantity', '>', 0)
            ->get()
            ->groupBy('created_by');

        $cashiers = $query->groupBy('t.created_by', 'u.id', 'u.first_name', 'u.last_name')
            ->select(
                'u.id as userId',
                'u.first_name',
                'u.last_name',
                DB::raw('COUNT(*) as transactionCount'),
                DB::raw('SUM(t.final_total) as totalRevenue')
            )->get()->map(function ($item) use ($refundsData, $topProductsData) {
                $userRefunds = $refundsData->get($item->userId);

                $userProducts = $topProductsData->get($item->userId, collect([]));
                $topProducts = $userProducts->sortByDesc('total_quantity')->take(5)->map(function ($prod) {
                    return [
                        'id' => $prod->product_id,
                        'name' => $prod->name,
                        'quantity' => (float) $prod->total_quantity,
                        'revenue' => (float) $prod->total_revenue
                    ];
                })->values()->toArray();

                return [
                    'userId' => (int) $item->userId,
                    'name' => trim($item->first_name . ' ' . $item->last_name),
                    'shiftLabel' => 'N/A',
                    'transactionCount' => (int) $item->transactionCount,
                    'totalRevenue' => (float) $item->totalRevenue,
                    'averageTransactionValue' => $item->transactionCount > 0 ? round($item->totalRevenue / $item->transactionCount, 2) : 0,
                    'voidCount' => 0,
                    'refundCount' => $userRefunds ? (int) $userRefunds->refundCount : 0,
                    'refundTotal' => $userRefunds ? (float) $userRefunds->refundTotal : 0.0,
                    'topProductsSold' => $topProducts
                ];
            });

        return $this->success([
            'date' => $date,
            'cashiers' => $cashiers,
            'storeTotals' => [
                'transactionCount' => $cashiers->sum('transactionCount'),
                'totalRevenue' => $cashiers->sum('totalRevenue'),
                'voidCount' => 0,
                'refundTotal' => $cashiers->sum('refundTotal')
            ]
        ]);
    }
}
