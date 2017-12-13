<?php

namespace Academe\GiroCheckout\Message;

/**
 ^
 */

use Omnipay\Common\Message\NotificationInterface;

class CompleteAuthorizeResponse extends AbstractResponse implements NotificationInterface
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

    /**
     * @return string The result code translated into a message, where known.
     */
    public function getMessage()
    {
        // During testimg, the mock request will not have our language method,
        // so we catch it.

        $request = $this->getRequest();

        if (method_exists($request, 'getValidLanguage')) {
            $lang = $this->getRequest()->getValidLanguage();
        } else {
            $lang = 'en';
        }

        return Helper::getMessage($this->getCode(), $lang);
    }

    /**
     * @return string
     */
    public function getTransactionStatus()
    {
        return Helper::getTransactionStatus($this->getCode());
    }
}
