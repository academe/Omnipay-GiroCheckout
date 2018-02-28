<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * Get the Giropay customer details for a transaction.
 *
 * @link http://api.girocheckout.de/en:girocheckout:giropay:start#check_bankstatus
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\GiroCheckout\Gateway;

class GetSenderRequest extends AbstractRequest
{
    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_GIROPAY,
        Gateway::PAYMENT_TYPE_GIROPAY_ID,
    ];

    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'giropay/senderinfo';

    /**
     * @return array
     */
    public function getData()
    {
        $this->validatePaymentType();

        $data = [];
        $data['merchantId'] = $this->getMerchantId(true);
        $data['projectId'] = $this->getProjectId(true);

        $data['reference'] = $this->getTransactionReference();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * Create the response object.
     *
     * @return GetBankStatusResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new GetSenderResponse($this, $data);
    }
}
