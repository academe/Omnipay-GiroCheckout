<?php

namespace Academe\GiroCheckout\Message;

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
        $this->assertSame('239da494f2e276bb2c91b5a8aef79f42', $data['hash']);
    }

}
