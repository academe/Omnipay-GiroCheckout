<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class CaptureResponseTest extends TestCase
{
    protected $captureSuccessData = [
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

    protected $captureFailData = [
        "reference" => null,
        "referenceParent" => null,
        "merchantTxId" => null,
        "backendTxId" => null,
        "amount" => null,
        "currency" => null,
        "resultPayment" => null,
        "rc" => 5200,
        "msg" => "Transaktion nicht akzeptiert",
    ];

    protected function newResponse($data)
    {
        return new CaptureResponse($this->getMockRequest(), $data);
    }

    public function testSuccessFlags()
    {
        $successResponse = $this->newResponse($this->captureSuccessData);

        $this->assertSame(true, $successResponse->isSuccessful());
        $this->assertSame(false, $successResponse->isRedirect());
        $this->assertSame('TXN-94459199193', $successResponse->getTransactionId());
        $this->assertSame('700a4199-634b-4876-b28a-c8e9b01a3793', $successResponse->getTransactionReference());
        $this->assertSame('020SiesTKoaIE1oEK0cdQH', $successResponse->getBackendTxId());

        // NOTE: The parent transaction reference is NOT documented for capture and refund, but it for
        // void. I am going to assume it is a mistake in the documentation, since the API DOES retunr it.

        $this->assertSame('146a4fd6-db2e-4daf-b119-3002f43a6829', $successResponse->getParentTransactionReference());
    }

    public function testFailFlags()
    {
        $successResponse = $this->newResponse($this->captureFailData);

        $this->assertSame(5200, $successResponse->getCode());
        $this->assertSame("Transaktion nicht akzeptiert", $successResponse->getMessage());

        $this->assertSame(false, $successResponse->isSuccessful());
        $this->assertNull($successResponse->getTransactionId());
        $this->assertNull($successResponse->getTransactionReference());
    }

    public function testParentTransactionReference()
    {
        $successResponse = $this->newResponse($this->captureFailData);

        // NOTE: The parent transaction reference is NOT documented for capture and refund, but it for
        // void. I am going to assume it is a mistake in the documentation, since the API DOES retunr it.

        $this->assertNull($successResponse->getParentTransactionReference());
    }
}
