<?php
namespace Integrated\Traits;


trait InteractsWithCake
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
            $this->makeRequest($this->_response->getHeaderLine('Location'));
        }

        return $this;
    }

    /**
     * Check whether the response is a redirect
     *
     * @return bool
     */
    protected function isRedirect()
    {
        return $this->_response->getStatusCode() == 302;
    }
}