<?php

namespace Omnipay\GiroCheckout\Message;

class CreateCardRequest extends AuthorizeRequest
{
    public function getData()
    {
        $this->setCreateCard(true);

        return parent::getData();
    }
}
