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
     * TODO: Just handles Credit Card payments initially.
     * All values will be strings; they will be sent as a form encoded request.
     *
     * @return array
     */
    public function getData()
    {
        $data = [
            'merchantId' => $this->getMerchantId(),
            'projectId' => $this->getProjectId(),
            'merchantTxId' => $this->getTransactionId(),
            'amount' => (string)$this->getAmountInteger(),
            'currency' => $this->getCurrency(),
            'purpose' => substr($this->getDescription(), 0, static::PURPOSE_LENGTH),
            'type' => $this->transactionType,
        ];

        if ($this->getValidLanguage()) {
            $data['locale'] = $this->getValidLanguage();
        }

        if ($this->getMobileOptimise() !== null) {
            $data['mobile'] = ! empty($this->getMobileOptimise())
                ? (string)static::MOBILE_OPTIMISE_YES
                : (string)static::MOBILE_OPTIMISE_NO;
        }

        // A pseudo card number (PKN) can be supplied, or a new PKN can be requested,
        // or the transaction can be left as a one-off with no PKN saved.

        $pkn = $this->getCardReference() ?: $this->getToken();

        if ($pkn !== null) {
            $data['pkn'] = $pkn;
        } else {
            // No PKN supplied. Are we asking for a new one?
            if (! empty($this->getRegisterCardReference())) {
                $data['pkn'] = static::PKN_CREATE;
            }
        }

        if ($this->getRecurring() !== null) {
            $data['recurring'] = ! empty($this->getRecurring())
                ? (string)static::MOBILE_RECURRING_YES
                : (string)static::MOBILE_RECURRING_NO;
        }

        // Where to send the user after filling out their CC details, or cancelling.

        $data['urlRedirect'] = $this->getReturnUrl();

        // Back channel notification of the result.
        // The main part of the result will be handed over the front channel too.

        $data['urlNotify'] = $this->getNotifyUrl();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

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

    /**
     * @return mixed
     */
    public function getRegisterCardReference()
    {
        return $this->getParameter('registerCardReference');
    }

    /**
     * @param  mixed $value A value that will later be cast to true/false
     * @return $this
     */
    public function setRegisterCardReference($value)
    {
        return $this->setParameter('registerCardReference', $value);
    }

    /**
     * @return mixed
     */
    public function getRecurring()
    {
        return $this->getParameter('recurring');
    }

    /**
     * @param  mixed $value A value that will later be cast to true/false
     * @return $this
     */
    public function setRecurring($value)
    {
        return $this->setParameter('recurring', $value);
    }
}
