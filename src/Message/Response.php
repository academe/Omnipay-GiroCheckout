<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * At the moment this just handles the CC initialisation response.
 * It will likely be refactored to a number of more focused response
 * messages.
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\GiroCheckout\Gateway;

class Response extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * @var int The response code to indicate the requested action was successful.
     */
    const RESPONSE_CODE_SUCCESS = 0;

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_SUCCESS
            && $this->getReasonCode() == Gateway::RESULT_PAYMENT_SUCCESS
            && ! $this->isRedirect();
    }

    /**
     * @return string Numeric code as a string.
     */
    public function getCode()
    {
        return $this->getDataItem('rc');
    }

    /**
     * @return string Numeric payment result code as a string.
     */
    public function getReasonCode()
    {
        return $this->getDataItem('resultPayment');
    }

    /**
     * @return string Message in the event of any kind of error.
     */
    public function getMessage()
    {
        $msg = $this->getDataItem('msg');

        if (! empty($msg)) {
            return $msg;
        }

        // A few of the APIs do not return a message, but still return
        // the code that can be mapped onto a message.

        $reasonCode = $this->getReasonCode();

        if (! empty($reasonCode)) {
            return Helper::getMessage($reasonCode);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->getDataItem('reference');
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getDataItem('redirect') ?: $this->getDataItem('url');
    }

    /**
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * @return array
     */
    public function getRedirectData()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        return $this->getCode() == static::RESPONSE_CODE_SUCCESS
            && !empty($this->getRedirectUrl());
    }

    /**
     * For Direct Debit payment types.
     *
     * @param string $mask Alternative masking character.
     * @return string
     */
    public function getIbanMasked($mask = '*')
    {
        $iban = $this->getDataItem('iban', '');

        if ($mask !== '*') {
            $iban = str_replace('*', $mask, $iban);
        }

        return $iban;
    }

    /**
     * For Direct Debit payment types.
     *
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->getDataItem('holder');
    }

    /**
     * For Direct Debit payment types.
     *
     * @return string
     */
    public function getBankCode()
    {
        return $this->getDataItem('bankcode');
    }

    /**
     * For Direct Debit payment types.
     *
     * @return string
     */
    public function getBankAccount()
    {
        return $this->getDataItem('bankaccount');
    }
}
