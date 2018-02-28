<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * Get a list of all PaymentPage supporting projects.
 *
 * @link http://api.girocheckout.de/en:girocheckout:paypage:start#project_request
 */

class GetProjectsRequest extends AbstractRequest
{
    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'paypage/projects';

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
     * @return GetProjectsResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new GetProjectsResponse($this, $data);
    }
}
