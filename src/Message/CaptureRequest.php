<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Capture Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class CaptureRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/capture';

    /**
     * @return array
     */
    public function getData()
    {
        // Construction of the data will depend on the payment type.

        $paymentType = $this->getPaymentType(true);

        // First five parameters are mandatory and common to all payment methods.

        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);
        $data['merchantTxId']   = $this->getTransactionId();
        $data['amount']         = (string)$this->getAmountInteger();
        $data['currency']       = $this->getCurrency();

        // NOTE: the online documentation has the purpose and reference swapped
        // around. However, that causes invalid hash errors against the live
        // API. I will assume the documentation is incorrect, at least at the the
        // time this is being written.
        // http://api.girocheckout.de/en:girocheckout:creditcard:start

        if ($purpose = $this->getDescription()) {
            $data['purpose'] = substr($purpose, 0, static::PURPOSE_LENGTH);
        }

        // GiroCheckout transaction ID from a previous transaction, which
        // the capture or refund is for.

        $data['reference'] = $this->getTransactionReference();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * Create the response object.
     *
     * @return CaptureResponse
     */
    protected function createResponse(array $data)
    {
        return $this->response = new CaptureResponse($this, $data);
    }
}
