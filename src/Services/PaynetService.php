<?php

namespace Nikba\Paynet\Services;

use GuzzleHttp\Client;
use Nikba\Paynet\Exceptions\PaynetException;
use Illuminate\Support\Facades\Log;

class PaynetService
{
    protected $client;
    protected $apiUrl;
    protected $merchantCode;
    protected $username;
    protected $password;
    protected $secretKey;
    protected $mode;
    protected $token;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('paynet.api_url');
        $this->merchantCode = config('paynet.merchant_code');
        $this->username = config('paynet.username');
        $this->password = config('paynet.password');
        $this->secretKey = config('paynet.secret_key');
        $this->mode = config('paynet.mode');
    }

    /**
     * Authenticate and get the access token.
     *
     * @return void
     * @throws PaynetException
     */
    protected function authenticate()
    {
        try {
            $response = $this->client->post("{$this->apiUrl}/auth", [
                'form_params' => [
                    'grant_type' => 'password',
                    'username' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $this->token = $data['access_token'];
        } catch (\Exception $e) {
            Log::error('Paynet authentication failed: ' . $e->getMessage());
            throw new PaynetException('Failed to authenticate with Paynet API.');
        }
    }

    /**
     * Ensure the service has a valid access token.
     *
     * @return void
     * @throws PaynetException
     */
    protected function ensureAuthenticated()
    {
        if (empty($this->token)) {
            $this->authenticate();
        }
    }

    /**
     * Send a payment document to Paynet.
     *
     * @param array $paymentData
     * @return array
     * @throws PaynetException
     */
    public function sendPayment(array $paymentData)
    {
        $this->ensureAuthenticated();
        $signature = $this->generateSignature($paymentData);
        $paymentData['Signature'] = $signature;
        $paymentData['MerchantCode'] = $this->merchantCode;

        try {
            $response = $this->client->post("{$this->apiUrl}/Payments", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $paymentData,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Paynet payment failed: ' . $e->getMessage());
            throw new PaynetException('Failed to send payment to Paynet API.');
        }
    }

    /**
     * Generate a signature for payment data.
     *
     * @param array $data
     * @return string
     */
    protected function generateSignature(array $data)
    {
        ksort($data);
        $stringToSign = implode('', array_values($data)) . $this->secretKey;
        return base64_encode(md5($stringToSign, true));
    }

    /**
     * Validate the notification signature.
     *
     * @param array $data
     * @param string $signature
     * @return bool
     */
    public function validateNotificationSignature(array $data, string $signature)
    {
        $generatedSignature = $this->generateSignature($data);
        return $generatedSignature === $signature;
    }

    /**
     * Handle Paynet notification.
     *
     * @param array $notificationData
     * @return array
     * @throws PaynetException
     */
    public function handleNotification(array $notificationData)
    {
        $signature = $notificationData['Signature'];
        unset($notificationData['Signature']);

        if (!$this->validateNotificationSignature($notificationData, $signature)) {
            throw new PaynetException('Invalid notification signature.');
        }

        // Process the notification data (e.g., update order status in the database)
        // Example:
        // $order = Order::where('invoice', $notificationData['Invoice'])->first();
        // if ($order) {
        //     $order->update(['status' => 'paid']);
        // }

        return $notificationData;
    }
}