<?php

class Multon_Estpay_Block_Lhv extends Multon_Estpay_Block_IPizza
{

    protected $_code = 'multon_lhv';
    protected $_gateway = 'lhv';

    /**
     * Returns LHV logo URL under base/default
     * theme skin
     *
     * @return string image URL
     */
    public function getMethodLogoUrl()
    {
        return $this->getSkinUrl('images/multon/estpay/lhv_logo_88x31.png');
    }

}
