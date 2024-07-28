<?php

namespace Nikba\Paynet\Tests\Unit;

use Nikba\Paynet\Services\PaynetService;
use Nikba\Paynet\Exceptions\PaynetException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;

class PaynetServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->clientMock = Mockery::mock(Client::class);
        $this->service = new PaynetService($this->clientMock);
    }

    protected function tearDown(): void
    {
        Mockery::close(); 
        parent::tearDown();
    }

    public function testAuthenticate()
    {
        $response = new Response(200, [], json_encode(['access_token' => 'test_token']));
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('https://api-merchant.test.paynet.md/auth', Mockery::type('array'))
            ->andReturn($response);

        $this->service->authenticate();
        $this->assertEquals('test_token', $this->service->token);
    }

    public function testSendPayment()
    {
        $this->service->token = 'test_token';
        $paymentData = [
            'Invoice' => 123456,
            'Currency' => 498,
            'MerchantCode' => '862491',
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
        $signature = $this->service->generateSignature($paymentData);
        $paymentData['Signature'] = $signature;

        $response = new Response(200, [], json_encode(['status' => 'success']));
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('https://api-merchant.test.paynet.md/api/Payments', Mockery::on(function ($arg) use ($paymentData) {
                return $arg['json'] === $paymentData;
            }))
            ->andReturn($response);

        $result = $this->service->sendPayment($paymentData);
        $this->assertEquals(['status' => 'success'], $result);
    }

    public function testHandleNotification()
    {
        $notificationData = [
            'Invoice' => 123456,
            'Currency' => 498,
            'MerchantCode' => '862491',
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
        $signature = $this->service->generateSignature($notificationData);
        $notificationData['Signature'] = $signature;

        $result = $this->service->handleNotification($notificationData);
        $this->assertEquals($notificationData, $result);
    }
}
