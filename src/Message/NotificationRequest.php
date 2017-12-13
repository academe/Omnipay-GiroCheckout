<?php

namespace Academe\GiroCheckout\Message;

/**
 * Everything the notification request needs is already handled
 * by the CompleteRequest. The gateway delivers them
 * in exactly the same way, though one is a server request and the
 * other is a brosser redirect.
 */

use Omnipay\Common\Exception\InvalidResponseException;

class NotificationRequest extends CompleteRequest
{
    /**
     * @throws InvalidRequestException
     * @param array $data
     * @return CompleteAuthotizeResponse
     */
    public function sendData($data)
    {
        $this->validateNotificationData($data);

        return $this->response = new NotificationResponse($this, $data);
    }
}
