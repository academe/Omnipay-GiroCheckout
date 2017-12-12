<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * GiroCheckout Gateway Authorization Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class AuthorizeRequest extends AbstractRequest
{
    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_AUTH;

    /**
     * @return array
     */
    public function getData()
    {
        $data = [
            'merchantId' => $this->getMerchantId(),
            'projectId' => $this->getProjectId(),
            'merchantTxId' => $this->getTransactionId(),
            'amount' => $this->getAmountInteger(),
            'currency' => $this->getCurrency(),
            'purpose' => substr($this->getDescription(), 0, static::PURPOSE_LENGTH),
            'type' => $this->transactionType,
        ];

        if ($this->getValidLanguage()) {
            $data['locale'] = $this->getValidLanguage();
        }

        if ($this->getMobileOptimise() !== null) {
            $data['mobile'] = ! empty($this->getMobileOptimise())
                ? static::MOBILE_OPTIMISE_YES
                : static::MOBILE_OPTIMISE_NO;
        }

        // pkn - blank = one-off; "create" = create a new PKN; "{pkn}" = use this PKN.
        //    set/get Token and set/get CardReference
        // recurring
        // urlRedirect
        // urlNotify
        // hash

        return $data;
    }

    public function sendData($data)
    {
    }

    /**
     * @return mixed
     */
    public function getMobileOptimise()
    {
        return $this->getParameter('mobileOptimise');
    }

    /**
     * @param  mixed $value A value that will later be cast to true/false
     * @return $this
     */
    public function setMobileOptimise($value)
    {
        return $this->setParameter('mobileOptimise', $value);
    }
}
