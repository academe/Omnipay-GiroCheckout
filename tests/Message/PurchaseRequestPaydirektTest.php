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
        $this->assertSame('4b200eaaf1de408e44c9a4309b5d24ee', $data['hash']);
    }
}
