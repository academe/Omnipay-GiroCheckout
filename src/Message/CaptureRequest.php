<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Capture Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class CaptureRequest extends AbstractRequest
{
    /**
     * @var string The values for the Paydirekt capture "final" flag
     * Not a 1/0 like most of the boolean parameters, but a true/false string.
     */
    const PAYDIREKT_FINAL_FLAG_YES = 'true';
    const PAYDIREKT_FINAL_FLAG_NO = 'false';

    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
        Gateway::PAYMENT_TYPE_MAESTRO,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
    ];

    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/capture';

    /**
     * @return array
     */
    public function getData()
    {
        $this->validatePaymentType();

        // First five parameters are mandatory and common to all payment methods.

        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);
        $data['merchantTxId']   = $this->getTransactionId();
        $data['amount']         = (string)$this->getAmountInteger();
        $data['currency']       = $this->getCurrency();

        // NOTE: the online documentation has the purpose and reference swapped
        // around for the Credit Card payment type.
        // However, that causes invalid hash errors against the live API.
        // I will assume the documentation is incorrect, at least at the the
        // time this is being written.
        // http://api.girocheckout.de/en:girocheckout:creditcard:start
        // The "purpose" is mandatory for Paydirekt, but optional for other supported
        // payment types.

        $purpose = $this->getDescription();

        if ($purpose !== null || $this->isPaydirekt()) {
            // Even though the documentation shows the purpose as optional for Direct Debit
            // payment types, it actually causes a hash validation error if included.

            if ($this->isDirectDebit()) {
                $data['purpose'] = substr($purpose, 0, static::PURPOSE_LENGTH);
            }
        }

        // GiroCheckout transaction ID from a previous transaction, which
        // the capture or refund is for.

        $data['reference'] = $this->getTransactionReference();

        if ($this->isPaydirekt()) {
            $merchantReconciliationReferenceNumber = $this->getMerchantReconciliationReferenceNumber();

            if ($merchantReconciliationReferenceNumber) {
                $data['merchantReconciliationReferenceNumber'] = $merchantReconciliationReferenceNumber;
            }

            $data['final'] = (bool)$this->getFinal()
                ? static::PAYDIREKT_FINAL_FLAG_YES
                : static::PAYDIREKT_FINAL_FLAG_NO;
        }

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

    /**
     * @return string
     */
    public function getFinal()
    {
        return $this->getParameter('merchantReconciliationReferenceNumber');
    }

    /**
     * @param string $value That will be cast to boolean
     * @return $this
     */
    public function setFinal($value)
    {
        return $this->setParameter('final', $value);
    }
}
