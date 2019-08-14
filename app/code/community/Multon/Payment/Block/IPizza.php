<?php

abstract class Multon_Payment_Block_IPizza extends Multon_Payment_Block_Abstract
{

    protected abstract function getReturnUrl();

    /**
     * Populates and returns array of fields to be submitted
     * to a bank for payment
     *
     * @return Array
     */
    public function getFields()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        $fields = array();

        $fields['VK_SERVICE'] = '1012';
        $fields['VK_VERSION'] = '008';
        $fields['VK_SND_ID'] = substr(Mage::getStoreConfig('payment/' . $this->_code . '/vk_snd_id'),0,15);
        $fields['VK_STAMP'] = substr($order->getIncrementId(),0,20);
        $fields['VK_AMOUNT'] = number_format($order->getTotalDue(), 2, '.', '');
        $fields['VK_CURR'] = $order->getOrderCurrencyCode();
        $fields['VK_REF'] = '';
        $fields['VK_MSG'] = __('Order number') . ' ' . $order->getIncrementId();
		$fields['VK_ENCODING'] = 'UTF-8';

        $fields = $this->modifyData($fields);

        $fields['VK_RETURN'] = $this->getReturnUrl();
		$fields['VK_CANCEL'] = $this->getReturnUrl();
		$fields['VK_DATETIME'] = Mage::getModel('core/date')->date(DATE_ISO8601); //'Y-m-d\TH:i:sO'

        $fields['VK_MAC'] = $this->signData($this->prepareData($fields));

        switch ( Mage::app()->getLocale()->getLocaleCode() ) {
            case 'et_EE':
                $language = 'EST';
                break;
            case 'lt_LT':
                $language = 'LIT';
                break;
            case 'lv_LV':
                $language = 'LAT';
                break;
            case 'ru_RU':
                $language = 'RUS';
                break;
            default:
                $language = 'ENG';
                break;
        }

        $fields['VK_LANG'] = $language;

        return $fields;
    }

    /**
     * Prepare data package for signing
     *
     * @param array $fields
     * @return string
     */
    protected function prepareData(array $fields)
    {
        return sprintf('%03d%s', mb_strlen($fields['VK_SERVICE'],'UTF-8'), $fields['VK_SERVICE'])
                . sprintf('%03d%s', mb_strlen($fields['VK_VERSION'],'UTF-8'), $fields['VK_VERSION'])
                . sprintf('%03d%s', mb_strlen($fields['VK_SND_ID'],'UTF-8'), $fields['VK_SND_ID'])
                . sprintf('%03d%s', mb_strlen($fields['VK_STAMP'],'UTF-8'), $fields['VK_STAMP'])
                . sprintf('%03d%s', mb_strlen($fields['VK_AMOUNT'],'UTF-8'), $fields['VK_AMOUNT'])
                . sprintf('%03d%s', mb_strlen($fields['VK_CURR'],'UTF-8'), $fields['VK_CURR'])
                . sprintf('%03d%s', mb_strlen($fields['VK_REF'],'UTF-8'), $fields['VK_REF'])
                . sprintf('%03d%s', mb_strlen($fields['VK_MSG'],'UTF-8'), $fields['VK_MSG'])
                . sprintf('%03d%s', mb_strlen($fields['VK_RETURN'],'UTF-8'), $fields['VK_RETURN'])
                . sprintf('%03d%s', mb_strlen($fields['VK_CANCEL'],'UTF-8'), $fields['VK_CANCEL'])
                . sprintf('%03d%s', mb_strlen($fields['VK_DATETIME'],'UTF-8'), $fields['VK_DATETIME'])
			;
    }

    protected function signData($data)
    {
        $signature = null;
        $key = openssl_pkey_get_private(Mage::getStoreConfig('payment/' . $this->_code . '/private_key'), '');
        openssl_sign($data, $signature, $key);
        openssl_free_key($key);

        return base64_encode($signature);
    }

    protected function modifyData(array $data)
    {
        return $data;
    }
}
