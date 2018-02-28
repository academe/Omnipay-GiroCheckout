<?php

namespace Omnipay\GiroCheckout\Message;

/**
 *
 */

use Omnipay\Common\Message\NotificationInterface;
use Omnipay\GiroCheckout\Gateway;
use ReflectionClass;

class Helper
{
    /**
     * Translate a payment status to transaction status code.
     *
     * @param string|integer $code The numeric code, likely in a string
     * @return string
     */
    public static function getTransactionStatus($code)
    {
        if ($code == Gateway::RESULT_PAYMENT_SUCCESS) {
            return NotificationInterface::STATUS_COMPLETED;
        }

        if ($code == Gateway::RESULT_PAYMENT_PAYPAL_PENDING) {
            return NotificationInterface::STATUS_PENDING;
        }

        return NotificationInterface::STATUS_FAILED;
    }

    /**
     * Translate a payment status to a message, where possible.
     *
     * @param string|integer $code The numeric code, likely in a string
     * @return string
     */
    public static function getMessage($code, $lang = 'en')
    {
        $messages = json_decode(file_get_contents(__DIR__ . '/../../data/messages.json'), true);

        if ($lang !== 'de') {
            $lang = 'en';
        }

        foreach ($messages as $message) {
            if ($message['code'] == $code) {
                return $message['message-' . $lang];
            }
        }

        return '';
    }

   /**
     * Get an array of constants in an object or class, with an optional prefix.
     * @param null $prefix
     * @return array
     */
    public static function constantList($classOrObject, $prefix = null)
    {
        $reflection = new ReflectionClass($classOrObject);
        $constants = $reflection->getConstants();

        if (isset($prefix)) {
            $result = [];
            $prefix = strtoupper($prefix);
            foreach ($constants as $key => $value) {
                if (strpos($key, $prefix) === 0) {
                    $result[$key] = $value;
                }
            }
            return $result;
        } else {
            return $constants;
        }
    }
}
