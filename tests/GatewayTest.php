<?php

namespace Omnipay\GiroCheckout;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testMerchantIdString()
    {
        $this->gateway->initialize([
            'merchantId' => 'ABCDEFG',
        ]);

        $this->gateway->getMerchantId(true);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testProjectIdString()
    {
        $this->gateway->initialize([
            'projectId' => 'ABCDEFG',
        ]);

        $this->gateway->getProjectId(true);
    }
}
