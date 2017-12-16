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
     *
     */
    public function getData()
    {
        // Construction of the data will depend on the payment type.

        $paymentType = $this->getPaymentType(true);

        // First five parameters are mandatory and common to all payment methods.

        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);
        $data['merchantTxId']   = $this->getTransactionId(true);
        $data['amount']         = (string)$this->getAmountInteger();
        $data['currency']       = $this->getCurrency();

        // GiroCheckout transaction ID from a previous transaction, which
        // the capture or refund is for.

        $data['reference']      = $this->getTransactionReference();

        if ($purpose = $this->getDescription()) {
            $data['purpose'] = substr($purpose, 0, static::PURPOSE_LENGTH);
        }

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }
}
