<?php

class Multon_Estpay_Model_Estcard extends Multon_Payment_Model_Abstract
{
    protected $_code = 'multon_estcard';
    protected $_formBlockType = 'estpay/estcard';
    protected $_gateway = 'estcard';

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl("estpay/" . $this->_gateway . "/redirect");
    }

    /**
     * Verifies response sent by the bank
     *
     * @param array $params Parameters by bank
     *
     * @return int
     */
    public function verify(array $params = array())
    {

        $merchantId = Mage::getStoreConfig('payment/' . $this->_code . '/merchant_id');

        if (!isset($params['id']) || $params['id'] != $merchantId) {
            Mage::log(sprintf(
                            '%s (%s)@%s: Wrong merchant ID used for return: %s vs %s', __METHOD__, __LINE__,
                            $_SERVER['REMOTE_ADDR'], $params['id'], $merchantId
                    ), null, $this->logFile
            );
            return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }

        $data =
                sprintf("%03s", $params['ver'])
                . sprintf("%-10s", $params['id'])
                . sprintf("%012s", $params['ecuno'])
                . sprintf("%06s", $params['receipt_no'])
                . sprintf("%012s", $params['eamount'])
                . sprintf("%3s", $params['cur'])
                . $params['respcode']
                . $params['datetime']
                . sprintf("%-40s", urldecode($params['msgdata']))
                . sprintf("%-40s", urldecode($params['actiontext']));
        $mac = pack('H*', $params['mac']);

        $key = openssl_pkey_get_public(Mage::getStoreConfig('payment/' . $this->_code . '/bank_certificate'));
        $result = openssl_verify($data, $mac, $key);
        openssl_free_key($key);

        switch ($result) {
            case 1: // ssl verify successful
                if ($params['respcode'] == '000')
                    return Multon_Payment_Helper_Data::_VERIFY_SUCCESS;
                else
				{
					$msg = sprintf('ERROR: (%d) %s', $params['respcode'], urldecode($params['msgdata']));
					Mage::getSingleton('checkout/session')->addError($msg);
                    return Multon_Payment_Helper_Data::_VERIFY_CANCEL;
				}

            case 0: // ssl verify failed
                Mage::log(sprintf('%s (%s)@%s: Verification of signature failed for estcard', __METHOD__, __LINE__, $_SERVER['REMOTE_ADDR']), null, $this->logFile);
				Mage::getSingleton('checkout/session')->addError('Signature verification failed.');

                return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;

            case -1: // ssl verify error
            default:
                $error = '';
                while ($msg = openssl_error_string())
                    $error .= $msg . "\n";
                Mage::log(sprintf('%s (%s)@%s: Verification of signature error for estcard : %s', __METHOD__, __LINE__, $_SERVER['REMOTE_ADDR'], $error), null, $this->logFile);
				Mage::getSingleton('checkout/session')->addError('Signature verification error: ' . $error);

                return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }
    }

}
