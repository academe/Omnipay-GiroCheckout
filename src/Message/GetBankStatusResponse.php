<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * At the moment this just handles the CC initialisation response.
 * It will likely be refactored to a number of more focused response
 * messages.
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\GiroCheckout\Gateway;

class GetBankStatusResponse extends Response
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_SUCCESS;
    }

    /**
     * @return string The BIC.
     */
    public function getBic()
    {
        return $this->getDataItem('bic');
    }

    /**
     * @return string The Bank name.
     */
    public function getBankName()
    {
        return $this->getDataItem('bankname');
    }

    /**
     * @return bool True means Giropay is supported.
     */
    public function hasGiropay()
    {
        return $this->getDataItem('giropay', 0) ? true : false;
    }

    /**
     * @return bool True means Giropay ID is supported.
     */
    public function hasGiropayId()
    {
        return $this->getDataItem('giropayid', 0) ? true : false;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->getDataItem('accountholder');
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->getDataItem('iban');
    }
}
