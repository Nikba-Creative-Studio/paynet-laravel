<?php

namespace Nikba\Paynet\Http\Controllers;

use Illuminate\Http\Request;
use Nikba\Paynet\Facades\Paynet;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaynetController extends Controller
{
    /**
     * Initiate a payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'Invoice' => 'required|numeric',
            'Currency' => 'required|numeric',
            'MerchantCode' => 'required|string',
            'Customer' => 'required|array',
            'ExpiryDate' => 'required|date',
            'Services' => 'required|array',
            // Add other necessary validation rules
        ]);

        try {
            $response = Paynet::sendPayment($validated);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle Paynet notifications.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleNotification(Request $request)
    {
        $notificationData = $request->all();

        try {
            $response = Paynet::handleNotification($notificationData);
            return response()->json(['status' => 'success', 'data' => $response]);
        } catch (\Exception $e) {
            Log::error('Paynet notification failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
