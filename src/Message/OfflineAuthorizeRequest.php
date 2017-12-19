<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Repeat Authorization Request.
 * Performs an offline repeat authorization.
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#recurring_credit_card_payment
 */

use Academe\GiroCheckout\Gateway;

class OfflineAuthorizeRequest extends AuthorizeRequest
{
    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/payment';

    /**
     * @var string
     */
    protected $interfaceVariant = self::VARIANT_OFFLINE;

    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
    ];
}
