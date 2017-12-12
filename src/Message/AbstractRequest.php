<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;

/**
 * GiroCheckout Gateway Abstract Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */
abstract class AbstractRequest extends OmnipayAbstractRequest
{
    /**
     * @var int Maximum length of the `purpose` field.
     */
    const PURPOSE_LENGTH = 27;

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
     * @var int Flag to indicate a recurring payment.
     */
    const MOBILE_RECURRING_YES = 1;
    const MOBILE_RECURRING_NO = 0;

    /**
     * @var array List of supported language strings.
     */
     protected $supportedLanguages = [
        'de',   // German (default)
        'en',   //English
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

        $hashString = implode('', $data);

        return hash_hmac('MD5', $hashString, $this->getProjectPassphrase());
    }
}
