<?php

namespace Academe\GiroCheckout\Message;

/**
 ^
 */

use Omnipay\Common\Message\RedirectResponseInterface;

class CompleteAuthotizeResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == 4000;
    }

    /**
     * @return bool True if the user aborted the process
     */
    public function isCancelled()
    {
        return $this->getCode() == 4502;
    }

    /**
     *
     */
    public function getCode()
    {
        return $this->getDataItem('gcResultPayment');
    }

    /**
     * @return string The gateway-generated transaction reference.
     */
    public function getTransactionReference()
    {
        return $this->getDataItem('gcReference');
    }

    /**
     * @param string The original merchant transaction ID
     */
    public function getTransactionId()
    {
        return $this->getDataItem('gcMerchantTxId');
    }

    /**
     * @return string
     */
    public function getBackendTransactionId()
    {
        return $this->getDataItem('gcBackendTxId');
    }

    /**
     * @return int The amount in minor units
     */
    public function getAmountInteger()
    {
        return (int)$this->getDataItem('gcAmount', 0);
    }

    /**
     * @return string ISO currenct code
     */
    public function getCurrency()
    {
        return $this->getDataItem('gcCurrency');
    }
}
