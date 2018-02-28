<?php

namespace Omnipay\GiroCheckout\Message;

/**
 *
 */

use Omnipay\Common\Message\NotificationInterface;
use Omnipay\GiroCheckout\Gateway;

class CompleteResponse extends AbstractResponse implements NotificationInterface
{
    /**
     * @var int
     * @link http://api.girocheckout.de/en:girocheckout:resultcodes#altersverifikation
     */
    // Age verification successful
    const AGE_VERIFICATIOB_SUCCESS = 4020;
    // Age verification not possible
    const AGE_VERIFICATIOB_NP = 4021;
    // Age verification unsuccessful
    const AGE_VERIFICATIOB_FAIL = 4022;

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == Gateway::RESULT_PAYMENT_SUCCESS;
    }

    /**
     * @return bool True if the user aborted the process
     */
    public function isCancelled()
    {
        return $this->getCode() == Gateway::RESULT_PAYMENT_CANCELLED;
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
     * @link http://api.girocheckout.de/en:girocheckout:resultcodes#altersverifikation
     * @return string age verification result codes for Giropay-ID
     */
    public function getResultAvs()
    {
        return $this->getDataItem('gcResultAVS');
    }

    /**
     * @link http://api.girocheckout.de/en:girocheckout:giropay:start#notification_about_the_payment_result
     * @return string Optional adjustable field, which includes the name of the person who has
     *  to be verified (giropay-ID)
     */
    public function getObvName()
    {
        return $this->getDataItem('gcObvName');
    }

    /**
     * @return bool
     */
    public function isAgeVerificartionSuccessful()
    {
        return $this->getResultAvs() == static::AGE_VERIFICATIOB_SUCCESS;
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
