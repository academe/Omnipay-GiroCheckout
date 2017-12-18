<?php

namespace Academe\GiroCheckout\Message;

//use Omnipay\Common\Exception\InvalidResponseException;
//use Omnipay\Common\Exception\InvalidRequestException;
//use Academe\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Repeat Authorization Request.
 * Performs an offline repeat authorization.
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#recurring_credit_card_payment
 */
class RepeatAuthorizeRequest extends AuthorizeRequest
{
    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/payment';

    /**
     * @var string
     */
    protected $recurring = self::RECURRING_YES;
}
