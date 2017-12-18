<?php

namespace Academe\GiroCheckout\Message;

/**
 * Handles the Capture, Refund and Void responses.
 */

use Academe\GiroCheckout\Gateway;

class CaptureResponse extends Response
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_INITIALISE_SUCCESS
            && $this->getReasonCode() == Gateway::RESULT_PAYMENT_SUCCESS;
    }

    /**
     * @return string Unique transaction id of the merchant
     */
    public function getTransactionId()
    {
        return $this->getDataItem('merchantTxId');
    }

    /**
     * @return string GiroCheckout transaction ID of the original base transaction
     */
    public function getParentTransactionReference()
    {
        return $this->getDataItem('referenceParent');
    }
}
