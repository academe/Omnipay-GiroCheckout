<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * GiroCheckout Gateway "get transaction information" Request.
 *
 * @link http://api.girocheckout.de/en:tools:transaction_status
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\GiroCheckout\Gateway;

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
