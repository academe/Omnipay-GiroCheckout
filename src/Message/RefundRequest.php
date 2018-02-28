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
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/refund';

    /**
     * @param array $data The data so far
     * @return array
     */
    public function getPaydirektData(array $data = [])
    {
        $data['merchantReconciliationReferenceNumber'] = $this->getMerchantReconciliationReferenceNumber();

        return $data;
    }
}
