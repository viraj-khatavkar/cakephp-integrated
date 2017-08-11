<?php

namespace Integrated\Traits;


/**
 * Trait LegacyInteractionWithCake
 *
 * @deprecated
 * @package Integrated\Traits
 */
trait LegacyInteractionWithCake
{
    /**
     * Follow 302 redirections.
     *
     * @return $this
     */
    protected function followRedirects()
    {
        while ($this->isRedirect()) {
            $this->session($this->_requestSession->read());
            $this->makeRequest($this->_response->location());
        }

        return $this;
    }

    protected function isRedirect()
    {
        return $this->_response->statusCode() == 302;
    }

    /**
     * Returns the string representation of body for legacy purposes.
     *
     * @return string
     */
    protected function _getBodyAsString()
    {
        return (string)$this->_response->body();
    }

}