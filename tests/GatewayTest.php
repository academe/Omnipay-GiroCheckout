<?php

namespace Academe\GiroCheckout;

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
     * Override the core setter/getter test.
     * Use a random number or string, depending on the datatype of the default,
     * to prevent triggering validation rules.
     */
    public function testDefaultParametersHaveMatchingMethods()
    {
        $settings = $this->gateway->getDefaultParameters();
        foreach ($settings as $key => $default) {
            $getter = 'get'.ucfirst($this->camelCase($key));
            $setter = 'set'.ucfirst($this->camelCase($key));

            if (is_numeric($default)) {
                $value = rand(1000000000, 9999999999);
            } elseif ($key == 'paymentType') {
                $value = 'DirectDebit';
            } else {
                $value = uniqid();
            }

            $this->assertTrue(method_exists($this->gateway, $getter), "Gateway must implement $getter()");
            $this->assertTrue(method_exists($this->gateway, $setter), "Gateway must implement $setter()");

            // setter must return instance
            $this->assertSame($this->gateway, $this->gateway->$setter($value));
            $this->assertSame($value, $this->gateway->$getter());
        }
    }

    /**
     * Override the core setter/getter test.
     * Use a random number or string, depending on the datatype of the default,
     * to prevent triggering validation rules.
     */
    public function testAuthorizeParameters()
    {
        if ($this->gateway->supportsAuthorize()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));

                if (is_numeric($default)) {
                    $value = rand(1000000000, 9999999999);
                } elseif ($key == 'paymentType') {
                    $value = 'DirectDebit';
                } else {
                    $value = uniqid();
                }

                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->authorize();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    /**
     * Override the core setter/getter test.
     * Use a random number or string, depending on the datatype of the default,
     * to prevent triggering validation rules.
     */
    public function testPurchaseParameters()
    {
        if ($this->gateway->supportsPurchase()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));

                if (is_numeric($default)) {
                    $value = rand(1000000000, 9999999999);
                } elseif ($key == 'paymentType') {
                    $value = 'DirectDebit';
                } else {
                    $value = uniqid();
                }

                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->purchase();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    /**
     * Override the core setter/getter test.
     * Use a random number or string, depending on the datatype of the default,
     * to prevent triggering validation rules.
     */
    public function testCompleteAuthorizeParameters()
    {
        if ($this->gateway->supportsCompleteAuthorize()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));

                if (is_numeric($default)) {
                    $value = rand(1000000000, 9999999999);
                } elseif ($key == 'paymentType') {
                    $value = 'DirectDebit';
                } else {
                    $value = uniqid();
                }

                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->completeAuthorize();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    /**
     * Override the core setter/getter test.
     * Use a random number or string, depending on the datatype of the default,
     * to prevent triggering validation rules.
     */
    public function testCompletePurchaseParameters()
    {
        if ($this->gateway->supportsCompletePurchase()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));

                if (is_numeric($default)) {
                    $value = rand(1000000000, 9999999999);
                } elseif ($key == 'paymentType') {
                    $value = 'DirectDebit';
                } else {
                    $value = uniqid();
                }

                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->completePurchase();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testMerchantIdString()
    {
        $this->gateway->initialize([
            'merchantId' => 'ABCDEFG',
        ]);
    }

    /**
     * @expectedException Omnipay\Common\Exception\InvalidRequestException
     */
    public function testProjectIdString()
    {
        $this->gateway->initialize([
            'projectId' => 'ABCDEFG',
        ]);
    }
}
