<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * Response helper methods.
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\AbstractResponse as OmnipayAbstractResponse;

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
}
