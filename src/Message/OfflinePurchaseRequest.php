<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Repeat Authorization Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#recurring_credit_card_payment
 */

use Academe\GiroCheckout\Gateway;

class OfflinePurchaseRequest extends PurchaseRequest
{
    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_SALE;

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
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
    ];
}
