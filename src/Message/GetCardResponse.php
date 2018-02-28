<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * Handles the GetCard response.
 */

use Omnipay\GiroCheckout\Gateway;

class GetCardResponse extends Response
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == static::RESPONSE_CODE_SUCCESS;
    }

    /**
     * Use this as the cardReference when making an offline or repeat payment.
     * @return string The PKN, or saved card reference
     */
    public function getCardReference()
    {
        return $this->getDataItem('pkn');
    }

    /**
     * Typical masked card format: 411111******1111
     *
     * @param string $mask Alternative masking character.
     * @return string
     */
    public function getNumberMasked($mask = '*')
    {
        $card = $this->getDataItem('cardnumber');

        if ($mask !== '*') {
            $card = str_replace('*', $mask, $card);
        }

        return $card;
    }

    /**
     * @return string The card expiry month, with NO leading zeros.
     */
    public function getExpiryMonth()
    {
        return $this->getDataItem('expiremonth');
    }

    /**
     * @return string The card expiry year, four digits.
     */
    public function getExpiryYear()
    {
        return $this->getDataItem('expireyear');
    }
}
