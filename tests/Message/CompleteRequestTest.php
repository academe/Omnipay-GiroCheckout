<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class CompleteRequestTest extends TestCase
{
    protected $requestSuccess;
    protected $requestCancelled;

    public function setUp()
    {
        parent::setUp();

        $options = [
            'projectPassphrase' => 'ZFV9ZXpUKuDM',
        ];

        // A successful payment.

        $httpRequestSuccess = $this->getHttpRequest();

        $this->requestSuccess = new CompleteRequest($this->getHttpClient(), $httpRequestSuccess);

        $this->requestSuccess->initialize($options);

        $httpRequestSuccess->request->replace([
            'gcReference' => 'b52e7ff4-2713-4287-817f-488bfab07956',
            'gcMerchantTxId' => 'TXN-33605725321',
            'gcBackendTxId' => 'pWkqbQ43eyVK0Sh88FCR1i',
            'gcAmount' => '123',
            'gcCurrency' => 'EUR',
            'gcResultPayment' => '4000',
            'gcHash' => 'e7a2d870904632943a40348edb3c8ae6',
        ]);

        // A user-cancelled payment attempt.

        $httpRequestCancelled = clone $httpRequestSuccess;

        $this->requestCancelled = new CompleteRequest($this->getHttpClient(), $httpRequestCancelled);

        $this->requestCancelled->initialize($options);

        $httpRequestCancelled->request->replace([
            'gcReference' => 'b1e33e0c-aa58-40ff-b83b-abc4bfb0c12f',
            'gcMerchantTxId' => 'TXN-94039909842',
            'gcBackendTxId' => '0a3WSyaa4wf1Loy2IRFTsd',
            'gcAmount' => '123',
            'gcCurrency' => 'EUR',
            'gcResultPayment' => '4502',
            'gcHash' => 'f1dba477d0ca7c16b191785bc7fa9044',
        ]);
    }

    public function testValidateHash()
    {
        // Sending the request will validate the hash.
        // An invalid hash will throw an exception.

        $this->requestSuccess->send();

        $this->requestCancelled->send();

        $this->assertTrue(true);
    }
}
