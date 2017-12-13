<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidResponseException;
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
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/start';

    /**
     * TODO: Just handles Credit Card payments initially; other payment types to be supported.
     * All values will be strings; they will be sent as a form encoded request.
     *
     * @return array
     */
    public function getData()
    {
        $data = $this->getBaseTransactionData();

        $data['type'] = $this->transactionType;

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

        $data = $this->getTransactionURLData($data);

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
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
