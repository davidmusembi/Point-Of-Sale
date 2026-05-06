<?php

namespace App\Http\Controllers\Api\V1;

use App\CashRegister;
use App\Utils\CashRegisterUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CashRegisterController extends BaseController
{
    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    /**
     * Open a cash register for a specific user.
     */
    public function open(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'location_id' => 'required|exists:business_locations,id',
            'initial_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->error('VALIDATION_FAILED', 'The given data was invalid.', $validator->errors(), 422);
        }

        $user_id = $request->input('user_id');
        $auth_user = $request->user();
        $business_id = $auth_user->business_id;

        // Security Check: Only allow opening for self unless user has admin permissions
        if ($auth_user->id != $user_id && !$auth_user->can('all_edit_cash_register')) {
             return $this->error('UNAUTHORIZED', 'Unauthorized to open register for this user.', null, 403);
        }

        // Check if user already has an open register
        $existing_register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();

        if ($existing_register) {
            return $this->error('REGISTER_ALREADY_OPEN', 'User already has an open cash register.', [
                'register_id' => $existing_register->id
            ], 400);
        }

        try {
            $initial_amount = $this->cashRegisterUtil->num_uf($request->input('initial_amount', 0));

            $register = CashRegister::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'status' => 'open',
                'location_id' => $request->input('location_id'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            if ($initial_amount > 0) {
                $register->cash_register_transactions()->create([
                    'amount' => $initial_amount,
                    'pay_method' => 'cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial',
                ]);
            }

            return $this->success([
                'register_id' => $register->id,
                'user_id' => $register->user_id,
                'location_id' => $register->location_id,
                'status' => $register->status,
                'opened_at' => $register->created_at
            ], 'Cash register opened successfully.');

        } catch (\Exception $e) {
            \Log::emergency('API V1 File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            return $this->error('SERVER_ERROR', 'Something went wrong.', null, 500);
        }
    }

    /**
     * Check the status of a user's cash register.
     */
    public function status($user_id)
    {
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();

        if ($register) {
            return $this->success([
                'is_open' => true,
                'register_details' => [
                    'id' => $register->id,
                    'location_id' => $register->location_id,
                    'opened_at' => $register->created_at
                ]
            ]);
        }

        return $this->success([
            'is_open' => false
        ]);
    }
}
