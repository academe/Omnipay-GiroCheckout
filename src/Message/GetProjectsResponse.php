<?php

namespace Omnipay\GiroCheckout\Message;

/**
 *
 */

class GetProjectsResponse extends Response
{
    /**
     * Return the possible projects array: ids of the projects, the project names,
     * the number of corresponding payment methods and the mode (TEST or LIVE).
     *
     * @return array
     */
    public function getProjects()
    {
        return $this->getDataItem('projects', []);
    }
}
