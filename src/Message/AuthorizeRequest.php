<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Authorization Request,
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

class AuthorizeRequest extends AbstractRequest
{
    /**
     * @var int Flag to indicate a recurring payment.
     */
    const RECURRING_YES = 1;
    const RECURRING_NO  = 0;

    /**
     * @var int Flag to indicate a variant on the UI.
     * VARIANT_PAGE is for when a user is present and visits the payment page.
     */
    const VARIANT_PAGE      = 'page';
    const VARIANT_OFFLINE   = 'offline';

    /**
     * @var int 1 to 4
     * 1 = single payment (default)
     * 2 = first payment of a sequence
     * 3 = recurring payment
     * 4 = last payment of a sequence 
     */
    const MANDATE_SEQUENCE_SINGLE       = 1;
    const MANDATE_SEQUENCE_FIRST        = 2;
    const MANDATE_SEQUENCE_RECURRING    = 3;
    const MANDATE_SEQUENCE_LAST         = 4;


    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_AUTH;

    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/start';

    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
        Gateway::PAYMENT_TYPE_MAESTRO,
        Gateway::PAYMENT_TYPE_EPS,
        Gateway::PAYMENT_TYPE_GIROPAY,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
    ];

    /**
     * @var string
     */
    protected $interfaceVariant = self::VARIANT_PAGE;

    /**
     * @param array $data
     * @return array Data with the Giropay fields attempted.
     */
    public function getGiropayData($data = [])
    {
        // TODO: all Giropay fields; all are optional:
        // iban
        // info1Label info1Text
        // info2Label info2Text
        // info3Label info3Text
        // info4Label info4Text
        // info5Label info5Text

        return $data;
    }

    /**
     * @param array $data
     * @return array Data with the Paydirekt fields attempted.
     */
    public function getPaydirektData($data = [])
    {
        // TODO: all Paydirekt fields.

        return $data;
    }

    /**
     * @param array $data
     * @return array Data with the Direct Debit fields attempted.
     */
    public function getDirectDebitData($data = [])
    {
        // If we are using the offline variant, without the payment page to
        // send the user to, then there are some additional mandaory parameters.

        if (! $this->hasPaymentPage()) {
            $iban = $this->getIban();

            $bankCode = $this->getBankCode();
            $bankAccount = $this->getBankAccount();

            $cardReference = $this->getCardReference();

            // Either the iban, the bank details, or a PKN must be provided.

            if ($iban === null && ($bankCode === null || $bankAccount === null) && $cardReference === null) {
                throw new InvalidRequestException(
                    'One of the iban, the bankCode+bankAccount or the cardReference'
                    . ' must be set for offline Direct Debit payments'
                );
            }

            // The cardReference (the PKN) is set elsewhere.

            if ($iban !== null) {
                $data['iban'] = $iban;
            } elseif ($cardReference === null) {
                $data['bankcode'] = $bankCode;
                $data['bankaccount'] = $bankAccount;
            }

            $accountHolder = $this->getAccountHolder();

            // The accountHolder is mandatory.
            // We will check here and raise an error, as the gateway reports less intuitive
            // errors if fieds are missing (normally just "invalid hash").

            if ($accountHolder === null) {
                throw new InvalidRequestException(
                    'The accountHolder must be set for offline Direct Debit payments'
                );
            }

            $data['accountHolder'] = $accountHolder;
        }

        // String(35) ASCII characters
        // Permitted characters: 0–9 A–Z a–z & \ / = + , : ; . _ \ - ! ?

        if ($mandateReference = $this->getMandateReference()) {
            $data['mandateReference'] = $mandateReference;
        }

        // Date when the SEPA mandate was placed.
        // Format "YYYY-MM-DD". We might want to validate the format if a string.

        if ($mandateSignedOn = $this->getMandateSignedOn()) {
            if ($mandateSignedOn instanceof \DateTime) {
                $mandateSignedOn = $mandateSignedOn->format('Y-m-d');
            }

            $data['mandateSignedOn'] = $mandateSignedOn;
        }

        // String(35) ASCII characters
        // Permitted characters: 0–9 A–Z a–z & / = + , : ; . _ - ! ?

        if ($mandateReceiverName = $this->getMandateReceiverName()) {
            $data['mandateReceiverName'] = $mandateReceiverName;
        }

        // Sequence type of the SEPA direct debit.
        // One of static::MANDATE_SEQUENCE_*

        if ($mandateSequence = $this->getMandateSequence()) {
            $mandateSequenceList = Helper::constantList($this, 'MANDATE_SEQUENCE_');

            if (! in_array($mandateSequence, $mandateSequenceList)) {
                throw new InvalidRequestException(sprintf(
                    'mandateSequence must be an integer, one of %s',
                    implode(', ', $mandateSequenceList)
                ));
            }

            $data['mandateSequence'] = $mandateSequence;
        }

        return $data;
    }

    /**
     * All values will be strings; they will be sent as a form encoded request.
     * The data parameters MUST be built in a strict order.
     * Should we moved all validation to here, rather than where the parameters are added?
     *
     * @return array
     */
    public function getData()
    {
        // Construction of the data will depend on the payment type.

        $paymentType = $this->getPaymentType();

        $this->validatePaymentType();

        // First six parameters are mandatory and common to all payment methods.

        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);
        $data['merchantTxId']   = $this->getTransactionId(true);
        $data['amount']         = (string)$this->getAmountInteger();
        $data['currency']       = $this->getCurrency();
        $data['purpose']        = substr($this->getDescription(), 0, static::PURPOSE_LENGTH);

        // EPS and Giropay require a bic

        if (
            $paymentType === Gateway::PAYMENT_TYPE_EPS
            || $paymentType === Gateway::PAYMENT_TYPE_GIROPAY
        ) {
            $data['bic'] = $this->getBic();
        }

        // Giropay has a bunch of optional fields here.

        if ($paymentType === Gateway::PAYMENT_TYPE_GIROPAY) {
            $data = $this->getGiropayData($data);
        }

        // Credit Card, Direct Debit and Maestro have optional type, locale and mobile parameters.

        if ($this->isCreditCard() || $this->isDirectDebit() || $this->isMaestro()) {
            // 'SALE' or 'AUTH', for purchase or authorization.

            $data['type'] = $this->transactionType;

            // The locale can be set only where the user is sent off to the
            // gateway payment form. Offline or repeat payment (or where no valid language is set)
            // leaves the locale unset.

            if ($this->getValidLanguage() && $this->hasPaymentPage()) {
                $data['locale'] = $this->getValidLanguage();
            }

            if ($this->getMobile() !== null && $this->hasPaymentPage()) {
                $data['mobile'] = ! empty($this->getMobile())
                    ? (string)static::MOBILE_OPTIMISE_YES
                    : (string)static::MOBILE_OPTIMISE_NO;
            }
        }

        // Direct Debit has a bunch of optional fields here.

        if ($this->isDirectDebit()) {
            $data = $this->getDirectDebitData($data);
        }

        if ($paymentType === Gateway::PAYMENT_TYPE_PAYDIREKT) {
            // 'SALE' or 'AUTH'.
            $data['type'] = $this->transactionType;
        }

        // The PKN is used by Credit Card and Direct Debit payment types.

        if ($this->isCreditCard() || $this->isDirectDebit()) {
            // A pseudo card number (PKN) can be supplied, or a new PKN can be requested,
            // but not at the same time.
            // Alternatively the transaction can be left as a one-off with no PKN saved.

            // A PKN is required if doing a recurring CC payment.

            $pkn = $this->getCardReference() ?: $this->getToken();

            if ($this->isCreditCard() && ! $this->hasPaymentPage()) {
                if (empty($pkn)) {
                    throw new InvalidRequestException('Missing cardReference for a payment without a payment page.');
                }
            }

            if ($pkn !== null) {
                $data['pkn'] = $pkn;
            } else {
                // No PKN supplied. Are we asking for a new one?
                if (! empty($this->getCreateCard())) {
                    $data['pkn'] = static::PKN_CREATE;
                }
            }
        }

        if ($this->isCreditCard()) {
            if (! $this->hasPaymentPage()) {
                $data['recurring'] = static::RECURRING_YES;
            } else {
                $data['recurring'] = static::RECURRING_NO;
            }
        }

        // Paydirekt has a bunch of fields here.

        if ($paymentType === Gateway::PAYMENT_TYPE_PAYDIREKT) {
            $data = $this->getPaydirektData($data);
        }

        // Where to send the user after filling out their CC details, or cancelling.

        if ($this->hasPaymentPage() || $this->isPayPal()) {
            $data['urlRedirect'] = $this->getReturnUrl();
        }

        // Back channel notification of the result.
        // The main part of the result will be handed over the front channel too.

        $data['urlNotify'] = $this->getNotifyUrl();

        // Add a hash for the data we have constructed.
        $data['hash'] = $this->requestHash($data);

        return $data;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->getParameter('mobile');
    }

    /**
     * @param  mixed $value A value that will later be cast to true/false
     * @return $this
     */
    public function setMobile($value)
    {
        return $this->setParameter('mobile', $value);
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
     * @return mixed
     */
    public function getMandateReference()
    {
        return $this->getParameter('mandateReference');
    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function setMandateReference($value)
    {
        return $this->setParameter('mandateReference', $value);
    }

    /**
     * @return mixed
     */
    public function getMandateSignedOn()
    {
        return $this->getParameter('mandateSignedOn');
    }

    /**
     * @param  string|DateTime $value String format is YYYY-MM-DD
     * @return $this
     */
    public function setMandateSignedOn($value)
    {
        return $this->setParameter('mandateSignedOn', $value);
    }

    /**
     * @return mixed
     */
    public function getMandateReceiverName()
    {
        return $this->getParameter('mandateReceiverName');
    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function setMandateReceiverName($value)
    {
        return $this->setParameter('mandateReceiverName', $value);
    }

    /**
     * @return int
     */
    public function getMandateSequence()
    {
        return $this->getParameter('mandateSequence');
    }

    /**
     * @param  int $value
     * @return $this
     */
    public function setMandateSequence($value)
    {
        return $this->setParameter('mandateSequence', $value);
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->getParameter('iban');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setIban($value)
    {
        return $this->setParameter('iban', $value);
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->getParameter('bankCode');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setBankCode($value)
    {
        return $this->setParameter('bankCode', $value);
    }

    /**
     * @return string
     */
    public function getBankAccount()
    {
        return $this->getParameter('bankAccount');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setBankAccount($value)
    {
        return $this->setParameter('bankAccount', $value);
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->getParameter('accountHolder');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setAccountHolder($value)
    {
        return $this->setParameter('accountHolder', $value);
    }

    /**
     * @return string Absolute endpoint URL.
     */
    public function getEndpoint($path = null)
    {
        if ($this->isCreditCard() || $this->isDirectDebit()) {
            if (! $this->hasPaymentPage()) {
                $path = 'transaction/payment';
            }
        }

        return parent::getEndpoint($path);
    }
}
