<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Repeat Authorization Request.
 * Performs an offline repeat authorization.
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#recurring_credit_card_payment
 */
class RepeatAuthorizeRequest extends AuthorizeRequest
{
    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/payment';

    /**
     * @var string
     */
    protected $recurring = self::RECURRING_YES;
}
