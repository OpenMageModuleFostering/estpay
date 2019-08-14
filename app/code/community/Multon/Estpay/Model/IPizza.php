<?php

abstract class Multon_Estpay_Model_IPizza extends Multon_Payment_Model_IPizza
{

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl("estpay/" . $this->_gateway . "/redirect");
    }

}
