<?php

namespace Omnipay\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Refund Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class RefundRequest extends CaptureRequest
{
    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
        Gateway::PAYMENT_TYPE_MAESTRO,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
        Gateway::PAYMENT_TYPE_BLUECODE,
    ];

    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/refund';

    /**
     * Override the method here, to exclude `final` property from `capture` request, because
     * it is invalid in `refund` requests.
     *
     * @param array $data The data so far
     * @return array
     */
    public function getPaydirektData(array $data = [])
    {
        $merchantReconciliationReferenceNumber = $this->getMerchantReconciliationReferenceNumber();

        if ($merchantReconciliationReferenceNumber) {
            $data['merchantReconciliationReferenceNumber'] = $merchantReconciliationReferenceNumber;
        }

        return $data;
    }
}
