<?php

namespace App\Http\Controllers\Api\V1;

use App\Business;
use App\BusinessLocation;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    /**
     * Retrieve all business and system settings.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $business = $user->business;
        
        if (!$business) {
            return $this->error('NOT_FOUND', 'Business settings not found', null, 404);
        }

        // Load currency details
        $business->load('currency');
        
        // Get the first location for address and phone details
        $location = BusinessLocation::where('business_id', $business->id)->first();

        $data = [
            'store' => [
                'name' => $business->name,
                'address' => $location ? strip_tags($location->location_address) : 'N/A',
                'phone' => $location->mobile ?? 'N/A',
                'email' => $location->email ?? 'N/A',
                'logoUrl' => !empty($business->logo) ? asset('uploads/business_logos/' . $business->logo) : null,
            ],
            'regional' => [
                'currency' => $business->currency->code ?? 'USD',
                'currencySymbol' => $business->currency->symbol ?? '$',
                'timezone' => $business->time_zone ?? 'UTC',
                'dateFormat' => $business->date_format ?? 'd-m-Y',
                'taxPercent' => 0.0 // Default or derived from business level if available
            ],
            'paymentMethods' => [
                'cash' => true,
                'card' => true,
                'bankTransfer' => true
            ],
            'security' => [
                'autoLockSeconds' => 1800,
                'twoFactorRequired' => false,
                'maxLoginAttempts' => 5,
                'sessionTimeoutMinutes' => 30
            ],
            'notifications' => [
                'lowStockAlerts' => (bool)$business->enable_product_expiry,
                'lowStockThresholdOverride' => null,
                'shiftSummaryOnClose' => true
            ],
            'hardware' => [
                'receiptPrinterIp' => $location->printer_ip ?? '127.0.0.1',
                'receiptPrinterPort' => $location->printer_port ?? 9100,
                'barcodeScanner' => 'USB'
            ]
        ];

        return $this->success($data);
    }
}
