<?php

use SAML2\Compat\AbstractContainer;

class itaContainer extends AbstractContainer
{

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generateId()
    {
        return uniqid();
    }

    /**
     * {@inheritdoc}
     */
    public function debugMessage($message, $type)
    {        
    }

    /**
     * {@inheritdoc}
     */
    public function redirect($url, $data = array())
    {        
    }

    /**
     * {@inheritdoc}
     */
    public function postRedirect($url, $data = array())
    {    
    }
}
?>