<?php

namespace Academe\GiroCheckout\Message;

/**
 * At the moment this just handles the CC complete request.
 * Use this to capture query data returned from the remote gateway
 * with the user.
 * An exception will be thrown if the hash does not validate on attempting
 * to send() this request. The data can still be logged using getData()
 */

use Omnipay\Common\Exception\InvalidRequestException;

class CompleteAuthorizeRequest extends AbstractRequest
{
    /**
     * @var array Query parameters.
     */
    protected $queryParameters = [
        'gcReference',
        'gcMerchantTxId',
        'gcBackendTxId',
        'gcAmount',
        'gcCurrency',
        'gcResultPayment',
        'gcHash',
    ];

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];

        foreach($this->queryParameters as $queryParameter) {
            $data[$queryParameter] = $this->httpRequest->get($queryParameter);
        }

        return $data;
    }

    /**
     * @throws InvalidRequestException
     * @param array $data
     * @return CompleteAuthotizeResponse
     */
    public function sendData($data)
    {
        $gcHash = isset($data['gcHash']) ? $data['gcHash'] : '';
        $queryHash = $this->requestHash($data);

        // Check for tampering.

        if ($gcHash !== $queryHash) {
            throw new InvalidRequestException(sprintf(
                'The request hash "%s" does not validate with the query "%s"; may have been tampered',
                $gcHash,
                $queryHash
            ));
        }

        return $this->response = new CompleteAuthotizeResponse($this, $data);
    }
}
