<?php

namespace Omnipay\GiroCheckout\Message;

/**
 * At the moment this just handles the CC complete request.
 * Use this to capture query data returned from the remote gateway
 * with the user.
 * An exception will be thrown if the hash does not validate on attempting
 * to send() this request. The data can still be logged using getData()
 */

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\GiroCheckout\Gateway;

class CompleteRequest extends AbstractRequest implements NotificationInterface
{
    /**
     * @return array
     */
    public function getData()
    {
        return $this->getNotificationData();
    }

    /**
     * @throws InvalidRequestException
     * @param array $data
     * @return CompleteResponse
     */
    public function sendData($data)
    {
        $this->validateNotificationData($data);

        return $this->response = new CompleteResponse($this, $data);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->getCode() == Gateway::RESULT_PAYMENT_SUCCESS;
    }

    /**
     * @return bool True if the user aborted the process
     */
    public function isCancelled()
    {
        return $this->getCode() == Gateway::RESULT_PAYMENT_CANCELLED;
    }

    /**
     * @var string containing a numeric result code
     */
    public function getCode()
    {
        $data = $this->getData();
        return isset($data['gcResultPayment']) ? $data['gcResultPayment'] : '';
    }

    /**
     * There are no messages sent with the notification data. However, we could
     * lookup the result code to get the message published here:
     * http://api.girocheckout.de/en:girocheckout:resultcodes#result_codes_payment
     */
    public function getMessage()
    {
        return Helper::getMessage($this->getCode(), $this->getValidLanguage());
    }

    /**
     * @return string
     */
    public function getTransactionStatus()
    {
        return Helper::getTransactionStatus($this->getCode());
    }
}
