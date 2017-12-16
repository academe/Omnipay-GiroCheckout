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
     * The reason code is the transaction result code, with details about
     * how the transactino went. It is differentiated from the overall response
     * status, "rc".
     *
     * @return int|string The value will be numeric, but may be cast into a string
     */
    public function getReasonCode()
    {
        return $this->getDataItem('resultPayment');
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
