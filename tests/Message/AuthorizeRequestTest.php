<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class AuthorizeRequestTest extends TestCase
{
    /**
     * @var Gateway
     */
    protected $request;

    public function setUp()
    {
        parent::setUp();

        $this->request = new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize([
            'merchantId' => 12345678,
            'projectId' => 654321,
            'transactionId' => 'trans-id-123',
            'amount' => '1.23',
            'currency' => 'EUR',
            'description' => 'A lovely test authorisation',
            'language' => 'en',
            'mobileOptimise' => true,
        ]);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testMerchantIdString()
    {
        $this->request->initialize([
            'merchantId' => 'ABCDEFG',
        ]);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testProjectIdString()
    {
        $this->request->initialize([
            'projectId' => 'ABCDEFG',
        ]);
    }

    public function testPurposeTruncate()
    {
        // 100 character description in.
        $this->request->initialize([
            'description' => str_repeat('X', 100),
        ]);

        $data = $this->request->getData();

        // 27 character description out.
        $this->assertSame(str_repeat('X', 27), $data['purpose']);
    }

    public function testLanguages()
    {
        foreach(['en', 'EN', 'en-GB', 'en_GB'] as $locale) {
            $this->request->initialize([
                'language' => $locale,
            ]);

            $data = $this->request->getData();

            $this->assertSame(
                'en',
                isset($data['locale']) ? $data['locale'] : 'NOT SET',
                sprintf('Locale "%s" does not translate to language "en"', $locale)
            );
        }
    }

    public function testX()
    {
        $data = $this->request->getData();
        //var_dump($data);
    }
}
