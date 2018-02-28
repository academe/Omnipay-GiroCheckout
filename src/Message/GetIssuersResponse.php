<?php

namespace Omnipay\GiroCheckout\Message;

/**
 *
 */

class GetIssuersResponse extends GetBankStatusResponse
{
    /**
     * Return the raw issuers array: names of the banks, indexed by BIC.
     * An enhancement may be to return a collection, with means to filter
     * in various ways.
     *
     * @return array
     */
    public function getIssuerArray()
    {
        return $this->getDataItem('issuer', []);
    }
}
