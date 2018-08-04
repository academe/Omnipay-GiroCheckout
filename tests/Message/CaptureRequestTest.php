<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;
use Money\Money;

class CaptureRequestTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->request = $this->getBlankRequest();
    }

    protected function getBlankRequest()
    {
        return new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testPaydirektWithDescription()
    {
        $this->request->initialize([
            'paymentType' => 'Paydirekt',
            'merchantId' => 12345678,
            'projectId' => 654321,
            'transactionId' => 'trans-id-123',
            'amount' => '1.23',
            'currency' => 'EUR',
            'description' => 'A lovely test authorisation',
            'transactionReference' => 'Original-Reference',
        ]);

        $data = $this->request->getData();

        $this->assertArrayHasKey('purpose', $data);
        $this->assertSame('A lovely test authorisation', $data['purpose']);
    }

    public function testPaydirektWithoutDescription()
    {
        $this->request->initialize([
            'paymentType' => 'Paydirekt',
            'merchantId' => 12345678,
            'projectId' => 654321,
            'transactionId' => 'trans-id-123',
            'amount' => '1.23',
            'currency' => 'EUR',
            //'description' => 'A lovely test authorisation',
            'transactionReference' => 'Original-Reference',
        ]);

        $data = $this->request->getData();

        $this->assertArrayHasKey('purpose', $data);
        $this->assertSame('', $data['purpose']);
    }

    /**
     * A long description is truncated to the maximum length permitted.
     */
    public function testPaydirektWithLongDescription()
    {
        $this->request->initialize([
            'paymentType' => 'Paydirekt',
            'merchantId' => 12345678,
            'projectId' => 654321,
            'transactionId' => 'trans-id-123',
            'amount' => '1.23',
            'currency' => 'EUR',
            'description' => str_repeat('An authorisation-', 5),
            'transactionReference' => 'Original-Reference',
        ]);

        $data = $this->request->getData();

        $this->assertArrayHasKey('purpose', $data);
        $this->assertSame('An authorisation-An authori', $data['purpose']);
    }
}
