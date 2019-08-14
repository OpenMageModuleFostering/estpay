<?php

class Multon_Estpay_Model_Nordea extends Multon_Payment_Model_Abstract
{
    protected $_code = 'multon_nordea';
    protected $_formBlockType = 'estpay/nordea';
    protected $_gateway = 'nordea';

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl("estpay/" . $this->_gateway . "/redirect");
    }

    /**
     * Verifies response from Nordea
     *
     * @param array $params Response sent by bank and to be verified
     *
     * @return int
     */
    public function verify(array $params = array())
    {
        $test_success = false;

        // Not present if cancelled or rejected
        if (isset($params['SOLOPMT_RETURN_PAID']))
            $test_success = true;

        $data =
                $params['SOLOPMT_RETURN_VERSION'] . '&' .
                $params['SOLOPMT_RETURN_STAMP'] . '&' .
                $params['SOLOPMT_RETURN_REF'] . '&' .
                ($test_success ? $params['SOLOPMT_RETURN_PAID'] : '') . '&' . // empty string still used for md5 calculation
                Mage::getStoreConfig('payment/' . $this->_code . '/mac_key') . '&';

        // Invalid MAC code
        if ($params['SOLOPMT_RETURN_MAC'] != strtoupper(md5($data))) {
            Mage::log(sprintf("%s (%s)@%s: (Nordea) Invalid MAC code", __METHOD__, __LINE__, $_SERVER['REMOTE_ADDR']), null, $this->logFile);
			Mage::getSingleton('checkout/session')->addError('Invalid MAC code.');
            return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }

        $session = Mage::getSingleton('checkout/session');

        // Reference number doesn't match.
        if (Mage::helper('multonpay')->calcRef($session->getLastRealOrderId()) != $params['SOLOPMT_RETURN_REF']) {
            Mage::log(
                    sprintf("%s (%s)@%s: (Nordea): Reference number doesn't match (potential tampering attempt).",
                            __METHOD__, __LINE__, $_SERVER['REMOTE_ADDR']
                    ), null, $this->logFile
            );
			Mage::getSingleton('checkout/session')->addError('Reference number error.');
            return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }

        if ($test_success)
            return Multon_Payment_Helper_Data::_VERIFY_SUCCESS;
        else
            return Multon_Payment_Helper_Data::_VERIFY_CANCEL;
    }

}
