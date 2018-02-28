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

        $gcResultPayment = !empty($data['gcResultPayment']) ? $data['gcResultPayment'] : null;
        $success = ($gcResultPayment == Gateway::RESULT_PAYMENT_SUCCESS);

        $transactionReference = !empty($data['gcReference']) ? $data['gcReference'] : null;
        $pkn = !empty($data['gcPkn']) ? $data['gcPkn'] : null;

        // Here, if we don't have the pkn details, then try to fetch them.
        // It is disabled for now, as it fails the tests due to an invalid
        // hash, through it does work in production. Perhaps tis can be
        // turned on explicitly with the "createCard" option, until we can
        // revisit the tests.

        if (false && $success && empty($pkn) && !empty($transactionReference)) {
            // Create a new gateway since we don't have access to the current
            // gateway from here.

            $gateway = \Omnipay\Omnipay::create('GiroCheckout');
            $gateway->initialize($this->getParameters());
            $getCardRequest = $gateway->getCard([
                'transactionReference' => $data['gcReference'],
            ]);
            $response = $getCardRequest->send();

            $cardData = $response->getData();

            // Map card fields to fields that the Payment Page already uses.

            if (! empty($cardData['pkn'])) {
                $data['gcPkn'] = $cardData['pkn'];
            }

            if (! empty($cardData['cardnumber'])) {
                $data['gcCardnumber'] = $cardData['cardnumber'];
            }

            if (! empty($cardData['expireyear']) && ! empty($cardData['expiremonth'])) {
                $data['gcCardExpDate'] = $cardData['expiremonth'] . '/' . $data['expireyear'];
            }
        }

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
