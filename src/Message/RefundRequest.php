<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

/**
 * GiroCheckout Gateway Refund Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class RefundRequest extends CaptureRequest
{
    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/refund';

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
