<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * Get a list of all Giropay supporting banks.
 *
 * @link http://api.girocheckout.de/en:girocheckout:giropay:start#giropay_issuer_bank_request
 */

//use Omnipay\Common\Exception\InvalidResponseException;
//use Omnipay\Common\Exception\InvalidRequestException;
//use Omnipay\GiroCheckout\Gateway;

class GetIssuersRequest extends GetBankStatusRequest
{
    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'giropay/issuer';

    /**
     * @return array
     */
    public function getData()
    {
        $this->validatePaymentType();

        $data = [];
        $data['merchantId'] = $this->getMerchantId(true);
        $data['projectId'] = $this->getProjectId(true);

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * Create the response object.
     *
     * @return GetIssuersResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new GetIssuersResponse($this, $data);
    }
}
