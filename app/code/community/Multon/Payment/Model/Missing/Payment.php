<?php

class Multon_Payment_Model_Missing_Payment extends Mage_Payment_Model_Method_Abstract
{
    private $name = 'NO_PAY';

    function __construct($name)
    {
        $this->name = $name;
    }

    function isGateway()
    {
        return false;
    }

    function getCode()
    {
        return $this->name;
    }

    function getTitle()
    {
        return 'Unavailable method: ' . $this->name;
    }

}
