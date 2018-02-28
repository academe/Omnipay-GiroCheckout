<?php

namespace Omnipay\GiroCheckout;

/**
 * GiroCheckout Gateway
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\AbstractGateway;
use Omnipay\GiroCheckout\Helper;

class Gateway extends AbstractGateway
{
    /**
     * @var string
     */
    const PAYMENT_TYPE_CREDIT_CARD  = 'CreditCard';
    const PAYMENT_TYPE_PAYPAL       = 'PayPal';
    const PAYMENT_TYPE_DIRECTDEBIT  = 'DirectDebit';
    const PAYMENT_TYPE_GIROPAY      = 'Giropay';
    const PAYMENT_TYPE_GIROPAY_ID   = 'Giropay-ID';
    const PAYMENT_TYPE_PAYDIREKT    = 'Paydirekt';
    const PAYMENT_TYPE_PAYMENTPAGE  = 'PaymentPage';

    const PAYMENT_TYPE_MAESTRO      = 'Maestro';
    const PAYMENT_TYPE_IDEAL        = 'iDEAL';
    const PAYMENT_TYPE_EPS          = 'eps';

    /**
     * @var int Just a few of the payment result codes we explicity check for.
     * See http://api.girocheckout.de/en:girocheckout:resultcodes#result_codes_payment
     */
    const RESULT_PAYMENT_SUCCESS        = 4000;
    const RESULT_PAYMENT_PAYPAL_PENDING = 4152;
    const RESULT_PAYMENT_CANCELLED      = 4502;
    const RESULT_PAYMENT_REJECTED       = 4900;

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
            'paymentPage' => true,
            'createCard' => false,
            'paymentType' => static::PAYMENT_TYPE_CREDIT_CARD,
        ];
    }

    /**
     * @param bool $assertValidation True to assert validation rules on the value
     * @return integer
     */
    public function getMerchantId($assertValidation = false)
    {
        $merchantId = $this->getParameter('merchantId');

        if ($assertValidation && ! is_numeric($merchantId)) {
            throw new InvalidRequestException('merchantId must be numeric');
        }

        return $merchantId;
    }

    /**
     * @param  integer $value
     * @return $this
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    // Config settera and getters:

    /**
     * @param bool $assertValidation True to assert validation rules on the value
     * @return integer
     */
    public function getProjectId($assertValidation = false)
    {
        $projectId = $this->getParameter('projectId');

        if ($assertValidation && ! is_numeric($projectId)) {
            throw new InvalidRequestException('projectId must be numeric');
        }

        return $projectId;
    }

    /**
     * @param  integer $value
     * @return $this
     */
    public function setProjectId($value)
    {
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

    /**
     * @return string
     */
    public function getPaymentPage()
    {
        return $this->getParameter('paymentPage');
    }

    /**
     * @param  mixed $value Will be cast to a boolean.
     * @return $this
     */
    public function setPaymentPage($value)
    {
        return $this->setParameter('paymentPage', $value);
    }

    /**
     * @return mixed
     */
    public function getCreateCard()
    {
        return $this->getParameter('createCard');
    }

    /**
     * @param  mixed $value A value that will later be cast to true/false
     * @return $this
     */
    public function setCreateCard($value)
    {
        return $this->setParameter('createCard', $value);
    }

    /**
     * @param bool $assertValidation True to assert validation rules on the value
     * @return string
     */
    public function getPaymentType($assertValidation = false)
    {
        $paymentType = $this->getParameter('paymentType');

        if ($assertValidation) {
            $paymentTypes = Helper::constantList($this, 'PAYMENT_TYPE_');

            if (! in_array($paymentType, $paymentTypes)) {
                throw new InvalidRequestException(sprintf(
                    'paymentType must be one of: %s; %s given',
                    implode(', ', $paymentTypes),
                    $paymentType
                ));
            }
        }

        return $paymentType;
    }

    /**
     * @param  string $value once of self::PAYMENT_TYPE_*
     * @return $this
     */
    public function setPaymentType($value)
    {
        return $this->setParameter('paymentType', $value);
    }

    // Messages:

    /**
     * @param  array $parameters
     * @return Message\AuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest(Message\AuthorizeRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(Message\PurchaseRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\CompletePurchaseRequest
     */
    public function completeAuthorize(array $parameters = [])
    {
        return $this->createRequest(Message\CompleteRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest(Message\CompleteRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\NotificationRequest
     */
    public function acceptNotification(array $parameters = [])
    {
        return $this->createRequest(Message\NotificationRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\CaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(Message\CaptureRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\RefundRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest(Message\RefundRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\VoidRequest
     */
    public function void(array $parameters = [])
    {
        return $this->createRequest(Message\VoidRequest::class, $parameters);
    }

    /**
     * Omnipay Common recognises create/update/delete card.
     * We cannot do any of those as distinct actions with this gateway, but getCard
     * fits that pattern.
     *
     * CHECKME: Can we support createCard/deleteCard using a zero-amount transaction?
     * That's a trick some gateways use.
     *
     * @param  array $parameters
     * @return Message\GetCardRequest
     */
    public function getCard(array $parameters = [])
    {
        return $this->createRequest(Message\GetCardRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\GetBankStatusRequest
     */
    public function getBankStatus(array $parameters = [])
    {
        return $this->createRequest(Message\GetBankStatusRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\GetIssuersRequest
     */
    public function getIssuers(array $parameters = [])
    {
        return $this->createRequest(Message\GetIssuersRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\GetProjectsRequest
     */
    public function getProjects(array $parameters = [])
    {
        return $this->createRequest(Message\GetProjectsRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\GetSenderRequest
     */
    public function getSender(array $parameters = [])
    {
        return $this->createRequest(Message\GetSenderRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return Message\GetTransactionRequest
     */
    public function getTransaction(array $parameters = [])
    {
        return $this->createRequest(Message\GetTransactionRequest::class, $parameters);
    }
}
