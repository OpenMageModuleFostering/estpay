<?php

class Multon_Estpay_Block_Estcard extends Multon_Payment_Block_Abstract
{

    protected $_code = 'multon_estcard';
    protected $_gateway = 'estcard';

    /**
     * Returns payment method logo URL
     *
     * @return string
     */
    public function getMethodLogoUrl()
    {
        return $this->getSkinUrl('images/multon/estpay/estcard_logo_120x31.gif');
    }

    /**
     * Populates and returns array for form that
     * will be submitted to Estcard
     *
     * @return array
     */
    public function getFields()
    {

        $fields = array();
        //NB! NETS does not support any field for reference ID
        //it's needed to rely on session in case of Estcard/NETS
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        $fields['action'] = 'gaf';
        $fields['ver'] = '004'; // Old version was 002
        $fields['id'] =
            Mage::getStoreConfig('payment/' . $this->_code . '/merchant_id');
        $fields['ecuno'] = sprintf('%012s', $order->getIncrementId());
        $fields['eamount'] =
            sprintf("%012s", (round($order->getTotalDue(), 2) * 100));
        $fields['cur'] = $order->getOrderCurrencyCode();
        $fields['datetime'] = date("YmdHis");

        switch ( Mage::app()->getLocale()->getLocaleCode() ) {
            case 'et_EE':
                $language = 'et';
                break;
            case 'ru_RU':
                $language = 'ru';
                break;
            case 'fi_FI':
                $language = 'fi';
                break;
            case 'de_DE':
                $language = 'de';
                break;
            default:
                $language = 'en';
                break;
        }
        $fields['lang'] = $language;

        // gaf004 related stuff
        $fields['charEncoding'] = 'ISO-8859-1';
        // $fields['charEncoding'] = 'UTF-8';

        $fields['feedBackUrl'] = Mage::getUrl(
                'estpay/' . $this->_gateway . '/return', array('_nosid' => true)
        );
        $fields['delivery'] = 'T';
        // Hardcoded for test purposes T = Physical delivery,
        // S = Electronic delivery

        $data =
            $fields['ver']
            . sprintf("%-10s", $fields['id'])
            . $fields['ecuno']
            . $fields['eamount']
            . $fields['cur']
            . $fields['datetime']
            . sprintf("%-128s", $fields['feedBackUrl'])
            . $fields['delivery'];

        $mac = sha1($data);
        $key = openssl_pkey_get_private(
            Mage::getStoreConfig('payment/' . $this->_code . '/private_key')
        );
        openssl_sign($data, $mac, $key);
        $fields['mac'] = bin2hex($mac);
        openssl_free_key($key);

        return $fields;
    }

}
