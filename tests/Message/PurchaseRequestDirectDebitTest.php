<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class PurchaseRequestDirectDebitTest extends TestCase
{
    /**
     * @var Gateway
     */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize([
            'paymentType' => 'DirectDebit',
            'merchantId' => '12345678',
            'projectId' => 654321,
            'transactionId' => 'trans-id-123',
            'amount' => '1.23',
            'currency' => 'EUR',
            'description' => 'A lovely test authorisation',
            'language' => 'en',
            'mobile' => true,
            'mandateReceiverName' => 'Recipient Name',
        ]);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testMerchantIdString()
    {
        $this->request->setMerchantId('ABCDEFG');
        $this->request->getMerchantId(true);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testProjectIdString()
    {
        $this->request->setProjectId('ABCDEFG');
        $this->request->getProjectId(true);
    }

    public function testPurposeTruncate()
    {
        // 100 character description in.
        $this->request->setDescription(str_repeat('X', 100));

        $data = $this->request->getData();

        // 27 character description out.
        $this->assertSame(str_repeat('X', 27), $data['purpose']);
    }

    public function testLanguages()
    {
        foreach(['en', 'EN', 'en-GB', 'en_GB'] as $locale) {
            $this->request->setLanguage($locale);

            $data = $this->request->getData();

            $this->assertSame(
                'en',
                isset($data['locale']) ? $data['locale'] : 'NOT SET',
                sprintf('Locale "%s" does not translate to language "en"', $locale)
            );
        }
    }

    public function testCreateCard()
    {
        $this->request->setCreateCard(true);
        $data = $this->request->getData();

        $this->assertSame('create', $data['pkn']);

        $this->request->setCreateCard(false);
        $data = $this->request->getData();

        $this->assertArrayNotHasKey('pkn', $data);

        $this->request->setCardReference('1234567812345678');
        $data = $this->request->getData();

        $this->assertSame('1234567812345678', $data['pkn']);

        // If the card reference is set, then asking for a new card reference
        // to be created, will have no effect.

        $this->request->setCreateCard(true);
        $data = $this->request->getData();

        $this->assertSame('1234567812345678', $data['pkn']);

        //var_dump($data);
    }

    public function testHash()
    {
        // This hash will change if the initializartion data changes.
        $data = $this->request->getData();
        $this->assertSame('e567960bbd640a33d78f33437f4f8669', $data['hash']);

        $data = [
            'merchantId' => '1234567',
            'projectId' => '1234',
            'parameter1' => 'Wert1',
            'parameter2' => 'Wert2',
        ];

        $this->request->setProjectPassphrase('secret');

        // Note: the example in the docs here:
        // http://api.girocheckout.de/en:girocheckout:general:start#hash_generation
        // give the following hash: '4233d4d15a75d651d60ebabe99b3d846'
        // However, it is not clear if that has is correct, as the following line shows:
        // var_dump(hash_hmac('MD5', '12345671234Wert1Wert2', 'secret'));
        // Gives '184d3f805959fc9fff2d07ccec1d1022'

        $this->assertSame('184d3f805959fc9fff2d07ccec1d1022', $this->request->requestHash($data));
    }
}
