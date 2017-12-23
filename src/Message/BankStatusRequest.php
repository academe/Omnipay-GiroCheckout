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

class BankStatusRequest extends AbstractRequest
{
    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'giropay/bankstatus';

    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_GIROPAY,
    ];

    /**
     * @return array
     */
    public function getData()
    {
        $this->validatePaymentType();

        $data = [];
        $data['merchantId'] = $this->getMerchantId(true);
        $data['projectId'] = $this->getProjectId(true);

        $data['bic'] = $this->getBic();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * Create the response object.
     *
     * @return GetCardResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new BankStatusResponse($this, $data);
    }
}
