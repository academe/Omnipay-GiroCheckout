<?php

namespace Academe\GiroCheckout;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\AbstractGateway;

/**
 * GiroCheckout Gateway
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
class Gateway extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Girocheckout';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
            'merchantId' => 0,
            'projectId' => 0,
            'projectPassphrase' => '',
            'language' => 'de',
        ];
    }

    /**
     * @return integer
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param  integer $value
     * @return $this
     */
    public function setMerchantId($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidRequestException('merchantId must be numeric');
        }

        return $this->setParameter('merchantId', $value);
    }

    // Config settera and getters:

    /**
     * @return integer
     */
    public function getProjectId()
    {
        return $this->getParameter('projectId');
    }

    /**
     * @param  integer $value
     * @return $this
     */
    public function setProjectId($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidRequestException('projectId must be numeric');
        }

        return $this->setParameter('projectId', $value);
    }

    /**
     * @return string
     */
    public function getProjectPassphrase()
    {
        return $this->getParameter('projectPassphrase');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setProjectPassphrase($value)
    {
        return $this->setParameter('projectPassphrase', $value);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    // Messages:

    /**
     * @param  array $parameters
     * @return Message\AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest(Message\AuthorizeRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest(Message\PurchaseRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\CompletePurchaseRequest
     */
    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest(Message\CompleteAuthorizeRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest(Message\CompleteAuthorizeRequest::class, $parameters);
    }

    //////////

    /**
     * @param  array $parameters
     * @return Message\FetchIssuersRequest
     */
    public function xxxfetchIssuers(array $parameters = array())
    {
        return $this->createRequest(Message\FetchIssuersRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\FetchPaymentMethodsRequest
     */
    public function xxxfetchPaymentMethods(array $parameters = array())
    {
        return $this->createRequest(Message\FetchPaymentMethodsRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\FetchTransactionRequest
     */
    public function xxxfetchTransaction(array $parameters = array())
    {
        return $this->createRequest(Message\FetchTransactionRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\RefundRequest
     */
    public function xxxrefund(array $parameters = array())
    {
        return $this->createRequest(Message\RefundRequest::class, $parameters);
    }

    //////////

    /**
     * @param  array $parameters
     * @return Message\CreateCustomerRequest
     */
    public function xxxcreateCustomer(array $parameters = array())
    {
        return $this->createRequest(Message\CreateCustomerRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\UpdateCustomerRequest
     */
    public function xxxupdateCustomer(array $parameters = array())
    {
        return $this->createRequest(Message\UpdateCustomerRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\FetchCustomerRequest
     */
    public function xxxfetchCustomer(array $parameters = array())
    {
        return $this->createRequest(Message\FetchCustomerRequest::class, $parameters);
    }
}
