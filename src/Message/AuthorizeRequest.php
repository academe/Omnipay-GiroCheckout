<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Authorization Request,
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\GiroCheckout\Gateway;
use Omnipay\Common\ItemBag;

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
     * PHYSICAL: All items in the cart are physical (default)
     * DIGITAL: All items in the cart are digital.
     * MIXED: The cart contains both physical and digital items.
     * ANONYMOUS_DONATION: An anonymous donation (no address data required).
     * AUTHORITIES_PAYMENT: A payment operation to the authorities (e-Government, no address data required).
     * @var string Paydirekt cart type
     */
    const SHOPPING_CART_TYPE_PHYSICAL               = 'PHYSICAL';
    const SHOPPING_CART_TYPE_DIGITAL                = 'DIGITAL';
    const SHOPPING_CART_TYPE_MIXED                  = 'MIXED';
    const SHOPPING_CART_TYPE_ANONYMOUS_DONATION     = 'ANONYMOUS_DONATION';
    const SHOPPING_CART_TYPE_AUTHORITIES_PAYMENT    = 'AUTHORITIES_PAYMENT';

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
        Gateway::PAYMENT_TYPE_GIROPAY,
        Gateway::PAYMENT_TYPE_EPS,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
        Gateway::PAYMENT_TYPE_PAYMENTPAGE,
    ];

    /**
     * @var int Flag to indicate whether a PaymentPage payment is in test mode or not.
     */
    const TEST_YES = '1';
    const TEST_NO  = '0';

    /**
     * @param array $data
     * @return array Data with the Giropay fields appended.
     */
    public function getGiropayData($data = [])
    {
        if ($this->getBic() === null) {
                throw new InvalidRequestException(
                    'The BIC is mandatory but missing for Giropay initialisation'
                );
        }

        $data['bic'] = $this->getBic();

        if ($iban = $this->getIban()) {
            $data['iban'] = $this->getIban();
        }

        // There are five optional label and text fields.

        for ($i = 1; $i <= 5; $i++) {
            $labelMethod = "getInfo${i}Label";
            $textMethod = "getInfo${i}Text";

            $label = $this->$labelMethod();
            $text = $this->$textMethod();

            if ($label !== null && $text !== null) {
                $data["info${i}Label"] = $label;
                $data["info${i}Text"] = $text;
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array Data with the Paydirekt fields appended.
     */
    public function getPaydirektData($data = [])
    {
        // TOOD: validate against constants.
        if ($shoppingCartType = $this->getShoppingCartType()) {
            $data['shoppingCartType'] = $shoppingCartType;
        }

        // TODO: validate max 20 characters.
        if ($customerId = $this->getCustomerId()) {
            $data['customerId'] = $customerId;
        }

        // TODO: shippingAmount - can we get this from the cart? Probably not.

        if ($card = $this->getCard()) {
            if ($shippingFirstName = $card->getShippingFirstName()) {
                $data['shippingAddresseFirstName'] = $shippingFirstName;
            }

            if ($shippingLastName = $card->getShippingLastName()) {
                $data['shippingAddresseLastName'] = $shippingLastName;
            }

            if ($shippingCompany = $card->getShippingCompany()) {
                $data['shippingCompany'] = $shippingCompany;
            }

            if ($shippingAddress2 = $card->getShippingAddress2()) {
                $data['shippingAdditionalAddressInformation'] = $shippingAddress2;
            }

            if ($shippingAddress1 = $card->getShippingAddress1()) {
                $data['shippingStreet'] = $shippingAddress1;
            }

            // TODO: shippingStreetNumber
            // The street number would need to go into the requesr as custom parameters
            // cannot be added to the card, so its value here is questionable.

            if ($shippingPostcode = $card->getShippingPostcode()) {
                $data['shippingZipCode'] = $shippingPostcode;
            }

            if ($shippingCity = $card->getShippingCity()) {
                $data['shippingCity'] = $shippingCity;
            }

            // TODO: validate an ISO 3166-1 2-character code
            if ($shippingCountry = $card->getShippingCountry()) {
                $data['shippingCountry'] = $shippingCountry;
            }

            if ($email = $card->getEmail()) {
                $data['shippingEmail'] = $email;
            }
        }

        // Only for `purchase`. The transaction type here is a convenient proxy.
        if ($merchantReconciliationReferenceNumber = $this->getMerchantReconciliationReferenceNumber()) {
            if ($this->transactionType == self::TRANSACTION_TYPE_SALE) {
                $data['merchantReconciliationReferenceNumber'] = $merchantReconciliationReferenceNumber;
            }
        }

        // TODO: orderAmount probably from the cart, not the card.
        // This is the total amount minus any shipping.

        // TODO: validate this is set (this is mandatory).
        $orderId = $this->getOrderId();
        $data['orderId'] = $orderId;

        $items = $this->getItems();
        if ($items) {
            $data['cart'] = json_encode($this->getCartData($items));
        }

        if ($invoiceId = $this->getInvoiceId()) {
            $data['invoiceId'] = $invoiceId;
        }

        if ($customerMail = $this->getCustomerMail()) {
            $data['customerMail'] = $customerMail;
        }

        // TODO: validate this is an integer and is in range.
        if ($minimumAge = $this->getMinimumAge()) {
            $data['minimumAge'] = $minimumAge;
        }

        return $data;
    }

    /**
     * Build the cart data.
     * Note: Omnipay 2.x does not define the format of the item price, so this
     * method interprets it according to the base type or whether a decimal
     * point is found. It's horrible and Omnipay 3.x fixes this.
     * Note: ean is not supported at this time.
     *
     * @param ItemBag $items
     * @return array format required for Paydirekt
     */
    public function getCartData(ItemBag $items)
    {
        $data = [];

        foreach ($items as $item) {
            $itemAmount = $item->getPrice();

            if (is_int($itemAmount)) {
                // 123
                $grossAmount = $itemAmount;
            } elseif (is_float($itemAmount)) {
                // 1.23
                $grossAmount = (int)($itemAmount * 100); // Horible hack!
            } elseif (is_string($itemAmount) && strpos($itemAmount, '.') !== false) {
                // '1.23'
                $grossAmount = (int)((float)$itemAmount * 100); // Horible hack!
            } elseif (is_string($itemAmount)) {
                // '123'
                $grossAmount = (int)$itemAmount;
            }

            $data[] = [
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'grossAmount' => $grossAmount,
            ];
        }

        return $data;
    }

    /**
     * @param array $data
     * @return array Data with the Direct Debit fields appended.
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
     * @param array $data
     * @return array Data with the Payment Page fields appended.
     */
    public function getPaymentPageData($data = [])
    {
        // Possible Payment methods
        //Comma Seperated list, at time of writing could be: 1,2,6,7,8,11,12,14,23,27.
        //(11/01/18)

        if ($payMethods = $this->getPayMethods()) {
            $data['paymethods'] = $payMethods;
        }

        //Comma Seperated list of the project IDs, whose payment methods are to be available on the page.

        if ($payProjects = $this->getPayProjects()) {
            $data['payprojects'] = $payProjects;
        }

        //String (70) Characters

        if ($organization = $this->getOrganization()) {
            $data['organization'] = $organization;
        }

        //if fixedvalues has values then freeamount is ignored.
        if ($freeAmount = $this->getFreeAmount()) {
            $data['freeamount'] = $freeAmount;
        }

        if ($fixedValues = $this->getFixedValues()) {
            $data['fixedvalues'] = $fixedValues;

        } else {
            if ($this->hasFreeAmount()) {

                if ($minAmount = $this->getMinAmount()) {
                    $data['minamount'] = $minAmount;
                } else {
                    $data['minamount'] = 100;
                }

                if ($maxAmount = $this->getMaxAmount()) {
                    $data['maxamount'] = $maxAmount;
                }
            }
        }

        //orderId is only used when payment method is paydirekt

        if ($orderId = $this->getOrderId()) {
            $data['orderid'] = $orderId;
        }

        if (isset($data['pagetype'])) {
            if ($data['pagetype'] == 2 && $projectList = $this->getProjectList()) {
                $data['projectlist'] = $projectList;
            }
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

        // First six parameters are mandatory and common to all payment methods
        // (except Giropay-ID).

        $data = [];
        $data['merchantId']     = $this->getMerchantId(true);
        $data['projectId']      = $this->getProjectId(true);
        $data['merchantTxId']   = $this->getTransactionId(true);

        if (! $this->isGiropayId()) {
            $data['amount']         = (string)$this->getAmountInteger();
            $data['currency']       = $this->getCurrency();

            //PaymentPage has a different length for purpose
            if ($this->isPaymentPage()) {
                if ($purpose = $this->getPurpose()) {
                    $data['purpose'] = $purpose;
                } else {
                    $data['purpose'] = trim(substr($this->getDescription(), 0, static::PURPOSE_LENGTH_PAYMENTAGE));
                }
            } else {
                $data['purpose'] = substr($this->getDescription(), 0, static::PURPOSE_LENGTH);
            }
        }

        // EPS and Giropay require a bic

        if ($paymentType === Gateway::PAYMENT_TYPE_EPS) {
            $data['bic'] = $this->getBic();
        }

        // Giropay has a bunch of optional fields here.

        if ($paymentType === Gateway::PAYMENT_TYPE_GIROPAY) {
            $data = $this->getGiropayData($data);
        }

        //PaymentPage has its own optional fields here.

        if ($this->isPaymentPage()) {

            if ($description = $this->getDescription()) {
                if (strlen($description) > 20) {
                    $data['description'] = $description;
                }
            }

            if ($pageType = $this->getPageType()) {
                $data['pagetype']    = $pageType;
            }
            
            if ($expiryDate = $this->getExpiryDate()) {
                $data['expirydate']  = $expiryDate;
            }
            
        }

        //Credit Card, Direct Debit and Maestro have optional type, locale and mobile parameters.
        //PaymentPage has type and locale.

        if ($this->isCreditCard() || $this->isDirectDebit() || $this->isMaestro() || $this->isPaymentPage()) {
            // 'SALE' or 'AUTH', for purchase or authorization.

            $data['type'] = $this->transactionType;

            // The locale can be set only where the user is sent off to the
            // gateway payment form. Offline or repeat payment (or where no valid language is set)
            // leaves the locale unset.

            if ($this->getValidLanguage() && $this->hasPaymentPage()) {
                $data['locale'] = $this->getValidLanguage();
            }

            if ($this->getMobile() !== null && $this->hasPaymentPage() && !$this->isPaymentPage()) {
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

        //PaymentPage has a bunch of optional fields here.

        if ($this->isPaymentPage()) {
            $data = $this->getPaymentPageData($data);
        }


        // The PKN is used by Credit Card and Direct Debit payment types.

        if ($this->isCreditCard() || $this->isDirectDebit() || $this->isPaymentPage()) {
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

        if ($this->isPaymentPage()) {
            $test = $this->getTestMode();
            $data['test'] = (bool)$test ? static::TEST_YES : static::TEST_NO;

        }

        if ($this->isCreditCard()) {
            if (! $this->hasPaymentPage()) {
                $data['recurring'] = static::RECURRING_YES;
            } else {
                $recurring = $this->getRecurring();

                if ($recurring !== null) {
                    $data['recurring'] = (bool)$recurring ? static::RECURRING_YES : static::RECURRING_NO;
                }
            }
        }

        // Paydirekt has a bunch of fields here.

        if ($this->isPaydirekt()) {
            $data = $this->getPaydirektData($data);
        }

        //PAYMENT PAGE URL STUFF
        if ($this->isPaymentPage()) {
            if ($successUrl = $this->getReturnUrl()) {
                $data['successUrl'] = $successUrl;
            }

            if ($backUrl = $this->getCancelUrl()) {
                $data['backUrl'] = $backUrl;
            }

            if ($failUrl = ($this->getFailUrl() ?: $this->getReturnUrl())) {
                $data['failUrl'] = $failUrl;
            }

            if ($notifyUrl = $this->getNotifyUrl()) {
                $data['notifyUrl'] = $notifyUrl;
            }
        } else {

            // Where to send the user after filling out their CC details, or cancelling.

            if ($this->hasPaymentPage() || $this->isPayPal()) {
                $data['urlRedirect'] = $this->getReturnUrl();
            }

            // Back channel notification of the result.
            // The main part of the result will be handed over the front channel too.

            if (! $this->isPaymentPage()) {
                $data['urlNotify'] = $this->getNotifyUrl();
            }
        }

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

    // Following getInfoNLabel and getInfoNText methods are for Giropay only.
    // TODO: allow all to be set at once through more structured data.

    /**
     * @return string For Giropay
     */
    public function getInfo1Label()
    {
        return $this->getParameter('info1Label');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo1Label($value)
    {
        return $this->setParameter('info1Label', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo1Text()
    {
        return $this->getParameter('info1Text');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo1Text($value)
    {
        return $this->setParameter('info1Text', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo2Label()
    {
        return $this->getParameter('info2Label');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo2Label($value)
    {
        return $this->setParameter('infoLabel', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo2Text()
    {
        return $this->getParameter('info2Text');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo2Text($value)
    {
        return $this->setParameter('info2Text', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo3Label()
    {
        return $this->getParameter('info3Label');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo3Label($value)
    {
        return $this->setParameter('info3Label', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo3Text()
    {
        return $this->getParameter('info3Text');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo3Text($value)
    {
        return $this->setParameter('info3Text', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo4Label()
    {
        return $this->getParameter('info4Label');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo4Label($value)
    {
        return $this->setParameter('info4Label', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo4Text()
    {
        return $this->getParameter('info4Text');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo4Text($value)
    {
        return $this->setParameter('info4Text', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo5Label()
    {
        return $this->getParameter('info5Label');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo5Label($value)
    {
        return $this->setParameter('info5Label', $value);
    }

    /**
     * @return string For Giropay
     */
    public function getInfo5Text()
    {
        return $this->getParameter('info5Text');
    }

    /**
     * @param  string $value for Giropay
     * @return $this
     */
    public function setInfo5Text($value)
    {
        return $this->setParameter('info5Text', $value);
    }

    /**
     * @return string For Paydirekt
     */
    public function getShoppingCartType()
    {
        return $this->getParameter('shoppingCartType');
    }

    /**
     * @param  string $value for Paydirekt one of static::SHOPPING_CART_TYPE_*
     * @return $this
     */
    public function setShoppingCartType($value)
    {
        return $this->setParameter('shoppingCartType', $value);
    }

    /**
     * @return string For Paydirekt
     */
    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    /**
     * @param  string $value for Paydirekt
     * @return $this
     */
    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }

    /**
     * @return string For Paydirekt
     */
    public function getOrderId()
    {
        return $this->getParameter('orderId');
    }

    /**
     * @param  string $value for Paydirekt
     * @return $this
     */
    public function setOrderId($value)
    {
        return $this->setParameter('orderId', $value);
    }

    /**
     * @return string For Paydirekt
     */
    public function getInvoiceId()
    {
        return $this->getParameter('invoiceId');
    }

    /**
     * @param  string $value for Paydirekt
     * @return $this
     */
    public function setInvoiceId($value)
    {
        return $this->setParameter('invoiceId', $value);
    }

    /**
     * @return string For Paydirekt
     */
    public function getCustomerMail()
    {
        return $this->getParameter('customerMail');
    }

    /**
     * @param  string $value for Paydirekt
     * @return $this
     */
    public function setCustomerMail($value)
    {
        return $this->setParameter('customerMail', $value);
    }

    /**
     * @return int For Paydirekt
     */
    public function getMinimumAge()
    {
        return $this->getParameter('minimumAge');
    }

    /**
     * @param int $value for Paydirekt
     * @return $this
     */
    public function setMinimumAge($value)
    {
        return $this->setParameter('minimumAge', $value);
    }

    /**
     * @return int For PaymentPage
     */
    public function getPageType()
    {
        return $this->getParameter('pagetype');
    }

    /**
     * @param int $value for PaymentPage
     * @return $this
     */
    public function setPageType($value)
    {
        return $this->setParameter('pagetype', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getExpiryDate()
    {
        return $this->getParameter('expirydate');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setExpiryDate($value)
    {
        return $this->setParameter('expirydate', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getPayMethods()
    {
        return $this->getParameter('paymethods');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setPayMethods($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return $this->setParameter('paymethods', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getPayProjects()
    {
        return $this->getParameter('payprojects');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setPayProjects($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return $this->setParameter('payprojects', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getOrganization()
    {
        return $this->getParameter('organization');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setOrganization($value)
    {
        return $this->setParameter('organization', $value);
    }

    /**
     * @return int For PaymentPage
     */
    public function getFreeAmount()
    {
        return $this->getParameter('freeamount');
    }

    /**
     * @param int $value for PaymentPage
     * @return $this
     */
    public function setFreeAmount($value)
    {
        return $this->setParameter('freeamount', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getFixedValues()
    {
        return $this->getParameter('fixedvalues');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setFixedValues($value)
    {

        if (is_array($value)) {
            $value = json_encode(array_values($value));
        }

        return $this->setParameter('fixedvalues', $value);
    }

    /**
     * @return int For PaymentPage
     */
    public function getMinAmount()
    {
        return $this->getParameter('minamount');
    }

    /**
     * @param int $value for PaymentPage
     * @return $this
     */
    public function setMinAmount($value)
    {
        return $this->setParameter('minamount', $value);
    }

    /**
     * @return int For PaymentPage
     */
    public function getMaxAmount()
    {
        return $this->getParameter('maxamount');
    }

    /**
     * @param int $value for PaymentPage
     * @return $this
     */
    public function setMaxAmount($value)
    {
        return $this->setParameter('maxamount', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getProjectList()
    {
        return $this->getParameter('projectlist');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setProjectList($value)
    {
        if (is_array($value)) {
            $value = json_encode(array_values($value));
        }

        return $this->setParameter('projectlist', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getFailUrl()
    {
        return $this->getParameter('failUrl');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setFailUrl($value)
    {
        return $this->setParameter('failUrl', $value);
    }

    /**
     * @return string For PaymentPage
     */
    public function getPurpose()
    {
        return $this->getParameter('purpose');
    }

    /**
     * @param string $value for PaymentPage
     * @return $this
     */
    public function setPurpose($value)
    {
        return $this->setParameter('purpose', $value);
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
        } elseif ($this->isPaymentPage()) {
            $path = 'paypage/init';
        }

        return parent::getEndpoint($path);
    }
}
