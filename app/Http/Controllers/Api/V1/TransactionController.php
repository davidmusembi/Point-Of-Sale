<?php

namespace App\Http\Controllers\Api\V1;

use App\BusinessLocation;
use App\Transaction;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends BaseController
{
    protected $transactionUtil;
    protected $productUtil;
    protected $contactUtil;

    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil, ContactUtil $contactUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
        $this->contactUtil = $contactUtil;
    }

    /**
     * List transactions.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;

        $query = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->with(['sales_person', 'sell_lines.variations.product', 'payment_lines']);

        // Apply location filter
        if ($request->has('locationId')) {
            if (!$user->can_access_this_location($request->locationId)) {
                return $this->error('FORBIDDEN', 'Access to this location is not allowed', null, 403);
            }
            $query->where('location_id', $request->locationId);
        } else {
            $this->applyLocationFilter($query);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('perPage', 20);
        $transactions = $query->latest('transaction_date')->paginate($perPage);

        $data = collect($transactions->items())->map(function ($transaction) {
            return $this->formatTransaction($transaction);
        });

        return $this->paginate($transactions, $data);
    }

    /**
     * Complete a transaction.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $business_id = $user->business_id;

        $validator = Validator::make($request->all(), [
            'totalAmount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.productId' => 'required',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.unitPrice' => 'nullable|numeric',
            'items.*.discountPercent' => 'nullable|numeric|between:0,100',
            'payments' => 'required|array',
            'payments.*.method' => 'required|string',
            'payments.*.amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_ERROR', 'Validation failed', $validator->errors(), 422);
        }

        // Get default location
        $location = BusinessLocation::where('business_id', $business_id)->first();
        if (!$location) {
             return $this->error('SERVER_ERROR', 'No business location found', null, 500);
        }

        try {
            DB::beginTransaction();

            $input = $request->all();
            
            // Map API items to system products
            $products = [];
            $calculated_total = 0;
            
            foreach ($input['items'] as $item) {
                $variation = Variation::with('product')->find($item['productId']);
                if (!$variation) {
                    throw new \Exception("Product variation ID {$item['productId']} not found");
                }

                $unit_price = $item['unitPrice'] ?? $variation->default_sell_price;
                $discount_percent = $item['discountPercent'] ?? 0;
                
                $price_after_discount = $unit_price * (1 - ($discount_percent / 100));
                $line_total = $price_after_discount * $item['quantity'];
                
                $products[] = [
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price_after_discount,
                    'unit_price_before_discount' => $unit_price,
                    'line_discount_amount' => ($unit_price * ($discount_percent / 100)),
                    'line_discount_type' => 'fixed',
                    'item_tax' => 0,
                    'tax_id' => null,
                    'unit_price_inc_tax' => $price_after_discount,
                    'enable_stock' => $variation->product->enable_stock
                ];
                
                $calculated_total += $line_total;
            }

            // Verify totalAmount matches calculated total (allow small delta for rounding)
            if (abs($calculated_total - $input['totalAmount']) > 0.01) {
                throw new \Exception("Calculated total ($calculated_total) does not match provided totalAmount ({$input['totalAmount']})");
            }

            // Verify sum of payments matches totalAmount
            $total_payments = collect($input['payments'])->sum('amount');
            if (abs($total_payments - $input['totalAmount']) > 0.01) {
                throw new \Exception("Sum of payments ($total_payments) does not match totalAmount ({$input['totalAmount']})");
            }

            $final_total = $calculated_total;

            $invoice_total = [
                'total_before_tax' => $final_total,
                'tax' => 0,
            ];

            // Get or create walk-in customer
            $customer = $this->contactUtil->getWalkInCustomer($business_id);
            
            $transaction_data = [
                'location_id' => $location->id,
                'status' => 'final',
                'contact_id' => $customer['id'],
                'transaction_date' => now()->toDateTimeString(),
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'final_total' => $final_total,
                'business_id' => $business_id,
                'created_by' => $user->id,
                'type' => 'sell',
                'payment_status' => 'paid'
            ];

            $transaction = $this->transactionUtil->createSellTransaction($business_id, $transaction_data, $invoice_total, $user->id, false);

            $this->transactionUtil->createOrUpdateSellLines($transaction, $products, $location->id, false, null, [], false);

            // Add payments
            $payment_data = [];
            foreach ($input['payments'] as $payment) {
                $payment_data[] = [
                    'amount' => $payment['amount'],
                    'method' => strtolower($payment['method']),
                    'paid_on' => now()->toDateTimeString(),
                    'business_id' => $business_id
                ];
            }
            
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $payment_data, $business_id, $user->id, false);
            
            // Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            DB::commit();

            return $this->success($this->formatTransaction($transaction->load(['sales_person', 'sell_lines.variations.product', 'payment_lines'])), null, null, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('SERVER_ERROR', $e->getMessage(), null, 500);
        }
    }

    /**
     * Get transaction detail.
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $transaction = Transaction::where('business_id', $user->business_id)
            ->with(['sales_person', 'sell_lines.variations.product', 'payment_lines'])
            ->find($id);

        if (!$transaction) {
            return $this->error('NOT_FOUND', 'Transaction not found', null, 404);
        }

        return $this->success($this->formatTransaction($transaction));
    }

    /**
     * Format transaction according to spec.
     */
    private function formatTransaction($transaction)
    {
        return [
            'id' => (int)$transaction->id,
            'referenceNo' => $transaction->invoice_no,
            'cashier' => [
                'id' => (int)$transaction->created_by,
                'name' => trim(($transaction->sales_person->first_name ?? '') . ' ' . ($transaction->sales_person->last_name ?? ''))
            ],
            'status' => strtoupper($transaction->status),
            'totalAmount' => (float)$transaction->final_total,
            'itemCount' => $transaction->sell_lines->count(),
            'paymentMethods' => $transaction->payment_lines->pluck('method')->map(fn($m) => strtoupper($m))->unique()->values(),
            'items' => $transaction->sell_lines->map(function($line) {
                return [
                    'id' => (int)$line->id,
                    'productId' => (int)$line->variation_id,
                    'productName' => $line->variations->product->name ?? 'N/A',
                    'quantity' => (float)$line->quantity,
                    'unitPrice' => (float)$line->unit_price,
                    'subtotal' => (float)($line->quantity * $line->unit_price)
                ];
            }),
            'createdAt' => $transaction->created_at->toIso8601String()
        ];
    }
}
