<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class ResponseTest extends TestCase
{
    // Payment made using a PKN and no payment page.

    protected $successData = [
        "reference" => "700a4199-634b-4876-b28a-c8e9b01a3793",
        "referenceParent" => "146a4fd6-db2e-4daf-b119-3002f43a6829",
        "merchantTxId" => "TXN-94459199193",
        "backendTxId" => "020SiesTKoaIE1oEK0cdQH",
        "amount" => "123",
        "currency" => "EUR",
        "resultPayment" => "4000",
        "rc" => 0,
        "msg" => "",
    ];

    // Payment made using a payment page.

    protected $redirectData = [
        "reference" => "700a4199-634b-4876-b28a-c8e9b01a3793",
        "redirect" => "https://example.com/",
        "rc" => 0,
        "msg" => "",
    ];

    protected $failData = [
        "reference" => null,
        "rc" => 5200,
        "msg" => "Transaktion nicht akzeptiert",
    ];

    protected function newResponse($data)
    {
        return new Response($this->getMockRequest(), $data);
    }

    public function testSuccessFlags()
    {
        $successResponse = $this->newResponse($this->successData);

        $this->assertSame(true, $successResponse->isSuccessful());
        $this->assertSame(false, $successResponse->isRedirect());

        $this->assertNull($successResponse->getTransactionId());
        $this->assertSame('700a4199-634b-4876-b28a-c8e9b01a3793', $successResponse->getTransactionReference());
    }

    public function testRedirectFlags()
    {
        $redirectResponse = $this->newResponse($this->redirectData);

        $this->assertSame(false, $redirectResponse->isSuccessful());
        $this->assertSame(true, $redirectResponse->isRedirect());
        $this->assertSame('https://example.com/', $redirectResponse->getRedirectUrl());

        $this->assertNull($redirectResponse->getTransactionId());
        $this->assertSame('700a4199-634b-4876-b28a-c8e9b01a3793', $redirectResponse->getTransactionReference());
    }

    public function testFailFlags()
    {
        $successResponse = $this->newResponse($this->failData);

        $this->assertSame(5200, $successResponse->getCode());
        $this->assertSame("Transaktion nicht akzeptiert", $successResponse->getMessage());

        $this->assertSame(false, $successResponse->isSuccessful());
        $this->assertNull($successResponse->getTransactionId());
        $this->assertNull($successResponse->getTransactionReference());
    }
}
