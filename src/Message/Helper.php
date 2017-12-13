<?php

namespace Academe\GiroCheckout\Message;

/**
 *
 */

use Omnipay\Common\Message\NotificationInterface;

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
        if ($code == 4000) {
            return NotificationInterface::STATUS_COMPLETED;
        }

        if ($code == 4152) {
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
}
