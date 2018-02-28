<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Abstract Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;
use Omnipay\GiroCheckout\Gateway;

abstract class AbstractRequest extends OmnipayAbstractRequest
{
    /**
     * @var int Maximum length of the `purpose` field.
     */
    const PURPOSE_LENGTH = 27;

    /**
     * @var int Maximum length of the `purpose` field on PaymentPage.
     */
    const PURPOSE_LENGTH_PAYMENTAGE = 20;

    /**
     * @var string Request transaction types.
     */
    // Authorization only.
    const TRANSACTION_TYPE_AUTH = 'AUTH';
    // Authorization+capture (aka purchase).
    const TRANSACTION_TYPE_SALE = 'SALE';

    /**
     * @var int Flag to indicate mobile display optimisation required.
     */
    const MOBILE_OPTIMISE_YES = 1;
    const MOBILE_OPTIMISE_NO = 0;

    /**
     * @var string Value to send as a PKN to indicate a new PKN should be created.
     */
    const PKN_CREATE = 'create';

    /**
     * @var string The request method.
     */
    protected $requestMethod = 'POST';

    /**
     * @var string The base URL for all API endpoints.
     */
    protected $endpointBaseUrl = 'https://payment.girosolution.de/girocheckout/api/v2';

    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = '';

    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [];

    /**
     * These fields are included in the hash generation for requests from
     * the gateway. All other fields are ignored.
     * CHECKME: think about just checking for a parameter name pattern to
     * be more future-proof, e.g. /^gc[A-Z]/
     * The Payment Page payment type has many more query parameters, all
     * matching this pattern, so that seems like a good reason to just use
     * the pattern. However, the order of the fields are defined here, which
     * would handle the query parameters order being mixed up.
     *
     * @var array Query parameters.
     */
    protected $notificationQueryParameters = [
        // Payment page only
        'gcPaymethod',
        'gcType',
        'gcProjectId',
        // All payment types
        'gcReference',
        'gcMerchantTxId',
        'gcBackendTxId',
        'gcAmount',
        'gcCurrency',
        'gcResultPayment',
        // Giropay-ID only
        'gcResultAVS',
        'gcObvName',
        // Payment Page only, if create PKN is enabled
        'gcPkn',
        'gcCardnumber',
        'gcCardExpDate',
        'gcAccountHolder',
        'gcIban',
        // Tamper check for all requests
        'gcHash',
    ];

    /**
     * @var array List of supported language strings.
     */
     protected $supportedLanguages = [
        'de',   // German (default)
        'en',   // English
        'es',   // Spanish
        'fr',   // French
        'it',   // Italian
        'ja',   // Japanese
        'pt',   // Portuguese
        'nl',   // Dutch
        'cs',   // Czech
        'sv',   // Swedish
        'da',   // Danish
        'pl',   // Polish
        'spde', // German donation
        'spen', // English donation
        'de_DE_stadtn', // German communes
    ];

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
    public function getBic()
    {
        return $this->getParameter('bic');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setBic($value)
    {
        return $this->setParameter('bic', $value);
    }

    /**
     * @return string
     */
    public function getMerchantReconciliationReferenceNumber()
    {
        return $this->getParameter('merchantReconciliationReferenceNumber');
    }

    /**
     * @param string $value for Paydirekt
     * @return $this
     */
    public function setMerchantReconciliationReferenceNumber($value)
    {
        return $this->setParameter('merchantReconciliationReferenceNumber', $value);
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
     * If not set at all, then default it to true, mainly for testing.
     * @return bool The value of parameter paymentPage as a boolean
     */
    public function hasPaymentPage()
    {
        return $this->getPaymentPage() === null ? true : (bool)$this->getPaymentPage();
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        $paymentType = $this->getParameter('paymentType');

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
     * Get the language string, in one of the supported formats.
     * Returns an empty string if no valid format could be found.
     *
     * @return string
     */
    public function getValidLanguage()
    {
        $lang = $this->getLanguage();

        // No language set.
        if (! $lang) {
            return '';
        }

        // Exact match language set.
        if (in_array($lang, $this->supportedLanguages)) {
            return $lang;
        }

        $lcLang = strtolower($lang);
        list($prefixLang) = preg_split('/[-_]+/', $lang);
        $prefixLang = strtolower($prefixLang);

        foreach ($this->supportedLanguages as $supportedLanguage) {
            $lcSupportedLanguage = strtolower($supportedLanguage);

            // Match with wrong case, or full "en-GB" format provided.
            if ($lcSupportedLanguage === $lcLang || $lcSupportedLanguage === $prefixLang) {
                return $supportedLanguage;
            }
        }

        return '';
    }

    /**
     * @param array $data
     * @return string
     */
    public function requestHash(array $data)
    {
        unset($data['hash']);
        unset($data['gcHash']);

        $hashString = implode('', $data);

        return hash_hmac('MD5', $hashString, $this->getProjectPassphrase());
    }

    /**
     * @param array $data
     * @return string
     */
    public function responseHash($responseBody)
    {
        return hash_hmac('MD5', $responseBody, $this->getProjectPassphrase());
    }

    /**
     * Retieve all parameters used as notification parameters,
     * and in the correct order for hash validation. Additional
     * parameters, some of which may be supplied by the merchant
     * site, are ignored.
     *
     * @return array
     */
    protected function getNotificationData()
    {
        $data = [];

        foreach ($this->notificationQueryParameters as $queryParameter) {
            $data[$queryParameter] = $this->httpRequest->get($queryParameter);
        }

        return $data;
    }

    /**
     * Validates the hash of an incoming notification request (either as a back-channel
     * notificatioon or a front-end user redirect back to the merchant site).
     * An exception will be thrown if the hash does not validate.
     *
     * @throws InvalidRequestException
     * @param array $data
     * @return null
     */
    public function validateNotificationData($data)
    {
        $gcHash = isset($data['gcHash']) ? $data['gcHash'] : '';
        $queryHash = $this->requestHash($data);

        // Check for tampering.

        if ($gcHash !== $queryHash) {
            throw new InvalidRequestException(sprintf(
                'The request hash "%s" does not validate with the query "%s"; may have been tampered',
                $gcHash,
                $queryHash
            ));
        }
    }

    /**
     * @throws InvalidResponseException
     * @param array $data
     * @return Response
     */
    public function sendData($data)
    {
        // Content-Type: application/x-www-form-urlencoded
        // Request must be UTF-8 encoded

        $httpRequest = $this->httpClient->createRequest(
            $this->requestMethod,
            $this->getEndpoint()
        );

        foreach ($data as $name => $value) {
            $httpRequest->setPostField($name, $value);
        }

        $httpResponse = $httpRequest->send();

        // A valid response is one in which the hash that has been sent does
        // not tie up with the message body.

        $headerHash = (string)$httpResponse->getHeader('hash');
        $bodyContent = (string)$httpResponse->getBody();
        $bodyHash = $this->responseHash($bodyContent);

        $validResponse = ($bodyHash === $headerHash);

        if (! $validResponse) {
            // The response may have been tampered with; we cannot trust it.
            throw new InvalidResponseException(sprintf(
                'The response hash "%s" does not validate with the body "%s"; may have been tampered',
                $headerHash,
                $bodyHash
            ));
        }

        return $this->createResponse($httpResponse->json());
    }

    /**
     * Create the resoonse object.
     *
     * @return Response
     */
    protected function createResponse(array $data)
    {
        return $this->response = new Response($this, $data);
    }

    /**
     * @return string Absolute endpoint URL.
     */
    public function getEndpoint($path = null)
    {
        if ($path !== null) {
            return implode('/', [$this->endpointBaseUrl, $path]);
        }

        return implode('/', [$this->endpointBaseUrl, $this->endpointPath]);
    }

    /**
     * @return bool true if processing a credit card.
     */
    public function isCreditCard()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_CREDIT_CARD;
    }

    /**
     * @return bool true if processing a direct debit transaction.
     */
    public function isDirectDebit()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_DIRECTDEBIT;
    }

    /**
     * @return bool true if processing by PayPal.
     */
    public function isPayPal()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_PAYPAL;
    }

    /**
     * @return bool true if processing by Giropay.
     */
    public function isGiropay()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_GIROPAY;
    }

    /**
     * @return bool true if processing by Giropay-ID.
     */
    public function isGiropayId()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_GIROPAY_ID;
    }

    /**
     * @return bool true if processing a maestro transaction.
     */
    public function isMaestro()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_MAESTRO;
    }

    /**
     * @return bool true if processing a paydirekt transaction.
     */
    public function isPaydirekt()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_PAYDIREKT;
    }

    /**
     * @return bool true if processing a paydirekt transaction.
     */
    public function isPaymentPage()
    {
        return $this->getPaymentType() === Gateway::PAYMENT_TYPE_PAYMENTPAGE;
    }

    /**
     * If not set at all, then default it to false
     * @return bool The value of freeAmount as bool.
     */
    public function hasFreeAmount()
    {
        if ($this->getFreeAmount() == 1) {
            return true;
        } elseif ($this->getFreeAmount() == 0 || null) {
            return false;
        }
    }

    /**
     * Check whether this message supports the payment type chosen.
     * 
     * @return bool true
     * @throws InvalidRequestException
     */
    public function validatePaymentType()
    {
        $paymentType = $this->getPaymentType();

        $paymentTypes = Helper::constantList(Gateway::class, 'PAYMENT_TYPE_');

        if (! in_array($paymentType, $paymentTypes)) {
            throw new InvalidRequestException(sprintf(
                'paymentType must be one of: %s; %s given',
                implode(', ', $paymentTypes),
                $paymentType
            ));
        }

        if (
            $this->supportedPaymentTypes !== []
            && ! in_array($paymentType, $this->supportedPaymentTypes)
        ) {
            throw new InvalidRequestException(sprintf(
                'This message does not support payment type "%s"; payment types supported: %s',
                $paymentType,
                implode(', ', $this->supportedPaymentTypes)
            ));
        }
    }
}
