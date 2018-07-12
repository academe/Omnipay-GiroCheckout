<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class PurchaseRequestPaydirektTest extends AuthorizeRequestPaydirektTest
{
    protected function getBlankRequest()
    {
        return new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testHash()
    {
        $data = $this->request->setRecurring(false);

        // This hash will change if the initializartion data changes.
        $data = $this->request->getData();
        $this->assertSame('152d3e9f968ffa14436fa09c8e97e67a', $data['hash']);
    }

}
