<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Void Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class VoidRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/void';

    // Response is identical to the capture/refund response.

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);
        $data['merchantTxId']   = $this->getTransactionId();

        // GiroCheckout transaction ID from a previous transaction, which
        // the capture or refund is for.

        $data['reference'] = $this->getTransactionReference();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }
}
