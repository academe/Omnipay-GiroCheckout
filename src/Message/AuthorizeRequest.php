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
    const RECURRING_NO = 0;

    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_AUTH;

    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/start';

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
    protected $recurring = self::RECURRING_NO;

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
        // TODO: all Direct Debit fields:
        // mandateReference
        // mandateSignedOn
        // mandateReceiverName
        // mandateSequence

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

        $paymentType = $this->getPaymentType(true);

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

        if (
            $paymentType === Gateway::PAYMENT_TYPE_CREDIT_CARD
            || $paymentType === Gateway::PAYMENT_TYPE_DIRECTDEBIT
            || $paymentType === Gateway::PAYMENT_TYPE_MAESTRO
        ) {
            // 'SALE' or 'AUTH', for purchase or authorization.

            $data['type'] = $this->transactionType;

            // The locale cannot be set for recurring (aka repeat, offline) payments.

            if ($this->recurring !== static::RECURRING_YES && $this->getValidLanguage()) {
                $data['locale'] = $this->getValidLanguage();
            }

            // FIXME: just call this "mobile".
            if ($this->getMobile() !== null) {
                $data['mobile'] = ! empty($this->getMobile())
                    ? (string)static::MOBILE_OPTIMISE_YES
                    : (string)static::MOBILE_OPTIMISE_NO;
            }
        }

        // Direct Debit has a bunch of optional fields here.

        if ($paymentType === Gateway::PAYMENT_TYPE_DIRECTDEBIT) {
            $data = $this->getDirectDebitData($data);
        }

        if ($paymentType === Gateway::PAYMENT_TYPE_PAYDIREKT) {
            // 'SALE' or 'AUTH'.
            $data['type'] = $this->transactionType;
        }

        // The PKN is use by Credit Card and Direct Debit payment types.

        if (
            $paymentType === Gateway::PAYMENT_TYPE_CREDIT_CARD
            || $paymentType === Gateway::PAYMENT_TYPE_DIRECTDEBIT
        ) {
            // A pseudo card number (PKN) can be supplied, or a new PKN can be requested,
            // or the transaction can be left as a one-off with no PKN saved.

            $pkn = $this->getCardReference() ?: $this->getToken();

            // A PKN is required if doing a recurring CC payment.

            if ($paymentType === Gateway::PAYMENT_TYPE_CREDIT_CARD && $this->recurring == static::RECURRING_YES) {
                if (empty($pkn)) {
                    throw new InvalidRequestException('A repeat payment requires a cardReference; none given');
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

        $data['recurring'] = $this->recurring;

        // Paydirekt has a bunch of fields here.

        if ($paymentType === Gateway::PAYMENT_TYPE_PAYDIREKT) {
            $data = $this->getPaydirektData($data);
        }

        // Where to send the user after filling out their CC details, or cancelling.
        // Only supply a redirect (aka return) URL if not doing a recurring payment.

        if ($this->recurring !== static::RECURRING_YES) {
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
}
