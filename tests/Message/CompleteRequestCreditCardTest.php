<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Tests\TestCase;

class CompleteRequestCreditCardTest extends TestCase
{
    protected $requestSuccess;
    protected $requestCancelled;
    protected $requestInvalid;

    protected $httpRequestSuccess;

    public function setUp()
    {
        parent::setUp();

        $options = [
            'projectPassphrase' => 'ZFV9ZXpUKuDM',
            'language' => 'de',
        ];

        // A successful payment.

        $httpRequestSuccess = $this->getHttpRequest();
        $this->httpRequestSuccess = $httpRequestSuccess;

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

        // An invalid card.

        $httpRequestInvalid = clone $httpRequestSuccess;

        $this->requestInvalid = new CompleteRequest($this->getHttpClient(), $httpRequestInvalid);

        $this->requestInvalid->initialize($options);

        $httpRequestInvalid->request->replace([
            'gcReference' => 'b1e33e0c-aa58-40ff-b83b-abc4bfb0c12f',
            'gcMerchantTxId' => 'TXN-94039909842',
            'gcBackendTxId' => '0a3WSyaa4wf1Loy2IRFTsd',
            'gcAmount' => '123',
            'gcCurrency' => 'EUR',
            'gcResultPayment' => '404',
            'gcHash' => '67c53ce69c728bcca341d6446877d148',
        ]);
    }

    public function testValidateHash()
    {
        // Sending the request will validate the hash.
        // An invalid hash will throw an exception.

        $this->requestSuccess->send();

        $this->requestCancelled->send();

        $this->requestInvalid->send();

        $this->assertTrue(true);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testInvalidHash()
    {
        $this->httpRequestSuccess->request->add([
            'gcReference' => 'b52e7ff4-2713-4287-817' . 'e' . '-488bfab07956',
        ]);

        $this->requestSuccess->send();
    }

    public function testStatuses()
    {
        $this->assertSame(true, $this->requestSuccess->isSuccessful());
        $this->assertSame(true, $this->requestSuccess->send()->isSuccessful());
        $this->assertSame(false, $this->requestSuccess->isCancelled());
        $this->assertSame(false, $this->requestSuccess->send()->isCancelled());
        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->requestSuccess->getTransactionStatus());
        $this->assertSame(NotificationInterface::STATUS_COMPLETED, $this->requestSuccess->send()->getTransactionStatus());

        $this->assertSame(false, $this->requestCancelled->isSuccessful());
        $this->assertSame(false, $this->requestCancelled->send()->isSuccessful());
        $this->assertSame(true, $this->requestCancelled->isCancelled());
        $this->assertSame(true, $this->requestCancelled->send()->isCancelled());
        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->requestCancelled->getTransactionStatus());
        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->requestCancelled->send()->getTransactionStatus());

        $this->assertSame(false, $this->requestInvalid->isSuccessful());
        $this->assertSame(false, $this->requestInvalid->send()->isSuccessful());
        $this->assertSame(false, $this->requestInvalid->isCancelled());
        $this->assertSame(false, $this->requestInvalid->send()->isCancelled());
        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->requestInvalid->getTransactionStatus());
        $this->assertSame(NotificationInterface::STATUS_FAILED, $this->requestInvalid->send()->getTransactionStatus());
    }

    public function testMessageEn()
    {
        $this->requestSuccess->setLanguage('en');;
        $this->assertSame('Transaction successful', $this->requestSuccess->send()->getMessage());

        $this->requestCancelled->setLanguage('en');;
        $this->assertSame('User aborted', $this->requestCancelled->send()->getMessage());
    }

    public function testMessageDe()
    {
        $this->requestSuccess->setLanguage('de');;
        $this->assertSame('Transaktion erfolgreich', $this->requestSuccess->send()->getMessage());

        $this->requestCancelled->setLanguage('de');;
        $this->assertSame('Abbruch durch Benutzer', $this->requestCancelled->send()->getMessage());
    }
}
