<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class AuthorizeRequestPaymentPageTest extends TestCase
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
            'paymentType' => 'PaymentPage',
            'merchantId' => 12345678,
            'projectId' => 654321,
            'transactionId' => 'TEST-123-5',
            'amount' => '1.23',
            'currency' => 'EUR',

            //PaymentPage specific
            'description' => 'Purpose is to test, to test things.',
            'testmode' => TRUE
            
        ]);
    }

    public function testPayMethods()
    {
        $testData = "1,2,5,16,18";
        $arrayTestData  = array(1,2,5,16,18);

        //Testing paymethods entered as a comma seperated string

        $this->request->setPayMethods($testData);
        $data = $this->request->getPayMethods();
        $this->assertSame($testData, $data);

        //Testing paymethods entered as an array

        $this->request->setPayMethods($arrayTestData); 
        $data = $this->request->getPayMethods();
        $this->assertSame($testData, $data);
        
    }

    public function testMinimum() 
    {
        $testData = [
            'merchantId' => 12345678,
            'projectId' => 654321,
            'merchantTxId' => 'TEST-123-5',
            'amount' => '123',
            'currency' => 'EUR',
            'purpose' => 'Purpose is to test,',
            'description' => 'Purpose is to test, to test things.',
            'type' => 'AUTH',
            'test' => '1'
        ];

        $testData['hash'] = $this->request->requestHash($testData);
        
        $data = $this->request->getData();
        $this->assertSame($testData, $data);

    }

    public function testJSONencodeFixedValues()
    {
        $testData = '["10000","20000","50050"]';
        $array = ['10000','20000','50050'];

        $this->request->setFixedValues($array);
        $data = $this->request->getFixedValues();

        $this->assertSame($testData, $data);
    }
    
    //test if MIN, MAX are ignored if fixed values contains values
    public function testFreeAmountIsIgnored()
    {
        $this->request->setFreeAmount(1);
        $this->request->setFixedValues('["250", "500", "1000"]');
        $this->request->setMinAmount(200);
        $this->request->setMaxAmount(9000);

        $data = $this->request->getData();
        $this->assertArrayNotHasKey('minamount', $data);
        $this->assertArrayNotHasKey('maxamount', $data);
    }

    //test if MIN, MAX are included if fixed values does not exist
    public function testFreeAmountNotIgnored()
    {
        $this->request->setFreeAmount(1);
        $this->request->setMinAmount(200);
        $this->request->setMaxAmount(9000);

        $data = $this->request->getData();
        $this->assertArrayHasKey('minamount', $data);
        $this->assertArrayHasKey('maxamount', $data);
    }
}
