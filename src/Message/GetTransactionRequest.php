<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway "query pseudo card information" Request,
 * Gets the cardReference and other details from a previous transaction.
 * The original transaction must have requested for the PKN to be saved
 * using `['createCard' => true]`
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#pseudo_card_numbers_pkn
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

class GetTransactionRequest extends AbstractRequest
{
    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
        Gateway::PAYMENT_TYPE_MAESTRO,
        Gateway::PAYMENT_TYPE_EPS,
        Gateway::PAYMENT_TYPE_GIROPAY,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
    ];

    /**
     * @var string Same endpoint for all payment types.
     */
    protected $endpointPath = 'transaction/status';

    /**
     * @return array
     */
    public function getData()
    {
        $this->validatePaymentType();

        $data = [];
        $data['merchantId'] = $this->getMerchantId(true);
        $data['projectId'] = $this->getProjectId(true);

        // Reference the transaction we are fetching details for.

        $data['reference'] = $this->getTransactionReference();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }
}
