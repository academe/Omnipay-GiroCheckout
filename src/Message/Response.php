<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\AbstractResponse;

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
        // Not yet successfully complete for an authorization initialisation.
        // CHECKME: except maybe when using a PCN?
        return false;
    }

    /**
     * Get a data item, or a default if not present.
     *
     * @param  string $name    The key for the field.
     * @param  mixed $default  The value to return if the data item is not found at all, or is null.
     * @return mixed           The value of the field, often a string, but could be case to anything..
     */
    protected function getDataItem($name, $default = null)
    {
        $data = $this->getData();
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * @return string Numeric code as a string.
     */
    public function getCode()
    {
        return $this->getDataItem('rc');
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
        return $this->getCode() == static::RESPONSE_CODE_INITIALISE_SUCCESS && !empty($this->getRedirectUrl());
    }
}
