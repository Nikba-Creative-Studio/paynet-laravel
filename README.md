# Nikba Paynet Laravel Package

This package provides integration with the Paynet payment gateway for Laravel applications.

## Installation

Install the package via Composer:

```bash
composer require nikba/paynet
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Nikba\Paynet\Providers\PaynetServiceProvider" --tag="config"
```

Add the following environment variables to your .env file:

```bash
PAYNET_API_URL=https://api-merchant.test.paynet.md/
PAYNET_MERCHANT_CODE=862491
PAYNET_SECRET_KEY=F822E47B-6F4F-4D9D-92EB-566ED89E3D76
PAYNET_USERNAME=388018
PAYNET_PASSWORD=6olmfsQX
PAYNET_MODE=false
```

## Usage

### Initiate a Payment
Use the Paynet facade to initiate a payment:

```php
use Nikba\Paynet\Facades\Paynet;

$paymentData = [
    'Invoice' => 20160622010101,
    'Currency' => 498,
    'MerchantCode' => 'MerchantCode',
    'Customer' => [
        'Code' => 'CustomerCode',
        'NameFirst' => 'FirstName',
        'NameLast' => 'LastName',
        'PhoneNumber' => 'PhoneNumber',
        'email' => 'customer@example.com',
        'Country' => 'Country',
        'City' => 'City',
        'Address' => 'Address',
    ],
    'ExpiryDate' => '2025-01-01T00:00:00',
    'Services' => [
        [
            'Name' => 'ServiceName',
            'Description' => 'ServiceDescription',
            'amount' => 100,
            'Products' => [
                [
                    'Amount' => 100,
                    'Barcode' => 13243546,
                    'Code' => 'PRODUCT1',
                    'Description' => 'ProductDescription',
                    'GroupId' => '22',
                    'GroupName' => 'GroupName',
                    'LineNo' => 1,
                    'Name' => 'ProductName',
                    'UnitPrice' => 100,
                    'UnitProduct' => 1,
                ],
            ],
        ],
    ],
    'MoneyType' => [
        'Code' => 'PAYNET',
    ],
];

$response = Paynet::sendPayment($paymentData);
```

### Handle Paynet Notifications
Add a route to handle Paynet notifications in your routes/web.php file:

```php
Route::post('/paynet/notification', [\Nikba\Paynet\Http\Controllers\PaynetController::class, 'handleNotification']);
```

Create a controller method to process the notifications:

```php
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
```

## Testing
Install necessary dependencies for testing:
```bash
composer require --dev mockery/mockery
```
Run the tests:
```bash
vendor/bin/phpunit --filter PaynetServiceTest
```