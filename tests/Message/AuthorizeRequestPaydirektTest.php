<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class AuthorizeRequestPaydirektTest extends TestCase
{
    /**
     * @var Gateway
     */
    protected $request;

    protected function getBlankRequest()
    {
        return new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function setUp()
    {
        parent::setUp();

        $this->request = $this->getBlankRequest();

        $card = [
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'shippingCompany' => 'shippingCompany',
            'shippingAddress1' => 'shippingAddress1',
            'shippingAddress2' => 'shippingAddress2',
            'shippingCity' => 'shippingCity',
            'shippingPostcode' => '123456',
            'shippingCountry' => 'DE',
            'email' => 'example@example.com',
        ];

        $items = [
            ['name' => 'Item 1', 'quantity' => 1, 'price' => '1.23'],
            ['name' => 'Item 2', 'quantity' => 4, 'price' => 999],
        ];

        $this->request->initialize([
            'paymentType' => 'Paydirekt',
            'merchantId' => 12345678,
            'projectId' => 654321,
            'transactionId' => 'trans-id-123',
            'amount' => '9.99',
            'currency' => 'EUR',
            // These are ignored:
            'description' => 'A lovely test authorisation',
            'language' => 'en',
            'mobile' => true,
            // Only used by Paydirekt
            'card' => $card,
            'items' => $items,
            'orderId' => 'ORD-123',
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
        $data = $this->request->getData();

        $this->assertNull(
            isset($data['locale']) ? $data['locale'] : null
        );
    }

    public function testCardFields()
    {
        $data = $this->request->getData();

        // Fields delivered only by the card object.

        $this->assertSame('firstName', $data['shippingAddresseFirstName']);
        $this->assertSame('lastName', $data['shippingAddresseLastName']);
        $this->assertSame('shippingCompany', $data['shippingCompany']);
        $this->assertSame('shippingAddress2', $data['shippingAdditionalAddressInformation']);
        $this->assertSame('shippingAddress1', $data['shippingStreet']);
        $this->assertSame('123456', $data['shippingZipCode']);
        $this->assertSame('shippingCity', $data['shippingCity']);
        $this->assertSame('DE', $data['shippingCountry']);
        $this->assertSame('example@example.com', $data['shippingEmail']);
        $this->assertSame('ORD-123', $data['orderId']);
    }

    public function testHash()
    {
        $data = $this->request->setRecurring(false);

        // This hash will change if the initializartion data changes.
        $data = $this->request->getData();
        $this->assertSame('784f77c35510c17b990d0f641f970ce8', $data['hash']);

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
