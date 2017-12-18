<?php

namespace Academe\GiroCheckout\Message;

/**
 * At the moment this just handles the CC initialisation response.
 * It will likely be refactored to a number of more focused response
 * messages.
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Academe\GiroCheckout\Gateway;

class Response extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * @var int The response code to indicate a CC has been successfuly initialised.
     */
    const RESPONSE_CODE_INITIALISE_SUCCESS = 0;

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_INITIALISE_SUCCESS
            && $this->getReasonCode() == Gateway::RESULT_PAYMENT_SUCCESS
            && empty($this->getRedirectUrl());
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
        return $this->getDataItem('msg');
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
        return $this->getDataItem('redirect');
    }

    /**
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * CHECKME: should this be an empty array?
     * @return null
     */
    public function getRedirectData()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        return $this->getCode() == static::RESPONSE_CODE_INITIALISE_SUCCESS
            && !empty($this->getRedirectUrl());
    }

    // TODO: for CC capture/refund: merchantTxid, amount, currency
    // The transaction can then be a success if resultPayment == 4000 and code == 0
    // Maybe extend to a child class.
}
