<?php

class Multon_Payment_Model_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    function __construct()
    {
        parent::__construct();
    }
    /**
     * Retrieve payment method model object
     * Don't die on missing payment method
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethodInstance()
    {
        try {
            return parent::getMethodInstance();
        }
        catch (Mage_Core_Exception $e) {
            return new Multon_Payment_Model_Missing_Payment($this->getMethod());
        }
    }

}
