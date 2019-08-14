<?php

class Multon_Estpay_Block_IPizza extends Multon_Payment_Block_IPizza
{
    protected function getReturnUrl()
    {
        return Mage::getUrl('estpay/' . $this->_gateway . '/return');
    }

    /**
     * Returns payment method logo URL
     *
     * @return string
     */
    public function getMethodLogoUrl()
    {
        return $this->getSkinUrl(
            sprintf(
                'images/multon/estpay/%s_logo_88x31.gif',
                strtolower($this->_gateway)
            )
        );
    }
}