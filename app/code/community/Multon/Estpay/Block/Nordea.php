<?php

class Multon_Estpay_Block_Nordea extends Multon_Payment_Block_Abstract
{
    protected $_code = 'multon_nordea';
    protected $_gateway = 'nordea';

    /**
     * Returns payment method logo URL
     *
     * @return string
     */
    public function getMethodLogoUrl()
    {
        return $this->getSkinUrl('images/multon/estpay/nordea_logo_88x31.gif');
    }

    /**
     * Returns fields for Nordea form
     * to be submitted to bank
     *
     * @return array
     */
    public function getFields()
    {

        $fields = array();
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
		$returnUrl = Mage::getUrl('estpay/' . $this->_gateway . '/return', array('_nosid'=>true)).'?';

        $fields['SOLOPMT_VERSION'] = '0003';
        $fields['SOLOPMT_STAMP'] = time();
        $fields['SOLOPMT_RCV_ID'] = Mage::getStoreConfig('payment/' . $this->_code . '/service_provider');

        /* Choose language:
         * 3 = english, 4 = estonian, 6 = latvian, 7 = lithuanian
         */
        switch (Mage::app()->getLocale()->getLocaleCode()) {
            case 'et_EE':
                $language = '4';
                break;
            default:
                $language = '3';
                break;
        }
        $fields['SOLOPMT_LANGUAGE'] = $language;

        $fields['SOLOPMT_AMOUNT'] = number_format($order->getTotalDue(), 2, '.', '');
        $fields['SOLOPMT_REF'] = Mage::helper('multonpay')->calcRef($order->getIncrementId());
        $fields['SOLOPMT_DATE'] = 'EXPRESS';
        $fields['SOLOPMT_MSG'] = __('Invoice number') . ' ' . $order->getIncrementId();
        $fields['SOLOPMT_RETURN'] = $returnUrl;
        $fields['SOLOPMT_CANCEL'] = $returnUrl;
        $fields['SOLOPMT_REJECT'] = $returnUrl;
        $fields['SOLOPMT_CONFIRM'] = 'YES';
        $fields['SOLOPMT_KEYVERS'] = '0001';
        $fields['SOLOPMT_CUR'] = $order->getOrderCurrencyCode();

        $data = $fields['SOLOPMT_VERSION'] . '&' .
                $fields['SOLOPMT_STAMP'] . '&' .
                $fields['SOLOPMT_RCV_ID'] . '&' .
                $fields['SOLOPMT_AMOUNT'] . '&' .
                $fields['SOLOPMT_REF'] . '&' .
                $fields['SOLOPMT_DATE'] . '&' .
                $fields['SOLOPMT_CUR'] . '&' .
                Mage::getStoreConfig('payment/' . $this->_code . '/mac_key') . '&';

        $fields['STRING'] = $data;
        $fields['SOLOPMT_MAC'] = strtoupper(md5($data));

        return $fields;
    }

}
