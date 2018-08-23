<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * Response helper methods.
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\AbstractResponse as OmnipayAbstractResponse;
use Omnipay\GiroCheckout\Gateway;
use Omnipay\Omnipay;

abstract class AbstractResponse extends OmnipayAbstractResponse
{
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
     * Get the card reference, if one was requested.
     * We don't know what was originally requeste, so we just go to
     * the gateway to ask for a reference.
     */
    public function getCardReference()
    {
        if (! $this->isSuccessful()) {
            // The transaction was not successful, so there won't be a
            // card reference to fetch.

            return null;
        }

        // Build a new request.

        $gateway = Omnipay::create('\\' . Gateway::class);

        $gateway->setMerchantId($this->request->getMerchantId(true));
        $gateway->setProjectId($this->request->getProjectId(true));
        $gateway->setProjectId($this->request->getProjectId(true));
        $gateway->setProjectPassphrase($this->request->getProjectPassphrase());
        $gateway->setPaymentType($this->request->getPaymentType());

        $request = $gateway->getCard();

        $request->setTransactionReference($this->getTransactionReference());

        $response = $request->send();

        if ($response->isSuccessful()) {
            return $response->getCardReference();
        }
    }
}
