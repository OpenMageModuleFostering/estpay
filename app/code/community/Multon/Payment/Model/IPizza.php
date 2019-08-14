<?php

abstract class Multon_Payment_Model_IPizza extends Multon_Payment_Model_Abstract
{

    /**
     * Verifies response sent by bank by checking validity
     * of banks signature using corresponding public key to bank's private key
     *
     * @param array $params Response sent by a bank
     *
     * @return int
     */
    public function verify(array $params = array())
    {
        if (!isset($params['VK_SERVICE']))
            return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;

        $test_success = false;

        switch ($params['VK_SERVICE']) {
            case '1111': // success
                $test_success = true;
                break;
            case '1911': // fail
                break;
            default:
                Mage::log(sprintf('%s (%s)@%s: IPizza return service is not 1111/1911: %s', __METHOD__, __LINE__,
                                $_SERVER['REMOTE_ADDR'], $params['VK_SERVICE']), null, $this->logFile);
				Mage::getSingleton('checkout/session')->addError('Wrong service code: ' . $params['VK_SERVICE']);

				return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }

        $vkSndId = Mage::getStoreConfig('payment/' . $this->_code . '/vk_snd_id');

        if (!isset($params['VK_REC_ID']) || $params['VK_REC_ID'] != $vkSndId) {
            Mage::log(sprintf('%s (%s)@%s: Wrong merchant ID used for return: %s vs %s', __METHOD__, __LINE__,
                            $_SERVER['REMOTE_ADDR'], $params['VK_REC_ID'], $vkSndId
                    ), null, $this->logFile
            );
			Mage::getSingleton('checkout/session')->addError('Internal error.');
            return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }

        $result = $this->verifyData($this->prepareData($params, $test_success), $params['VK_MAC']);

        switch ($result) {
            case 1: // ssl verify successful
                if ($test_success)
                    return Multon_Payment_Helper_Data::_VERIFY_SUCCESS;
                else
                    return Multon_Payment_Helper_Data::_VERIFY_CANCEL;

            case 0: // ssl verify failed
                Mage::log(sprintf(
                                '%s (%s)@%s: Verification of signature failed for %s', __METHOD__, __LINE__,
                                $_SERVER['REMOTE_ADDR'], $params['VK_SND_ID']
                        ), null, $this->logFile);
				Mage::getSingleton('checkout/session')->addError('Signature verification failed.');

                return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;

            case -1: // ssl verify error
            default:
                $error = '';
                while ($msg = openssl_error_string())
                    $error .= $msg . "\n";
                Mage::log(sprintf(
                                '%s (%s)@%s: Verification of signature error for %s : %s', __METHOD__, __LINE__,
                                $_SERVER['REMOTE_ADDR'], $params['VK_SND_ID'], $error
                        ), null, $this->logFile);
				Mage::getSingleton('checkout/session')->addError('Signature verification error: ' . $error);

                return Multon_Payment_Helper_Data::_VERIFY_CORRUPT;
        }
    }

    /**
     * Prepare data package for signature verification
     *
     * @param array $params
     * @param boolean $test_success
     * @return string
     */
    protected function prepareData(array $params, $test_success)
    {
        $data = sprintf('%03d%s', mb_strlen($params['VK_SERVICE'],'UTF-8'), $params['VK_SERVICE'])
                . sprintf('%03d%s', mb_strlen($params['VK_VERSION'],'UTF-8'), $params['VK_VERSION'])
                . sprintf('%03d%s', mb_strlen($params['VK_SND_ID'],'UTF-8'), $params['VK_SND_ID'])
                . sprintf('%03d%s', mb_strlen($params['VK_REC_ID'],'UTF-8'), $params['VK_REC_ID'])
                . sprintf('%03d%s', mb_strlen($params['VK_STAMP'],'UTF-8'), $params['VK_STAMP']);

        if ($test_success) {
            $data .= sprintf('%03d%s', mb_strlen($params['VK_T_NO'],'UTF-8'), $params['VK_T_NO'])
                    . sprintf('%03d%s', mb_strlen($params['VK_AMOUNT'],'UTF-8'), $params['VK_AMOUNT'])
                    . sprintf('%03d%s', mb_strlen($params['VK_CURR'],'UTF-8'), $params['VK_CURR'])
                    . sprintf('%03d%s', mb_strlen($params['VK_REC_ACC'],'UTF-8'), $params['VK_REC_ACC']) // SEB LV & SEB LT just VK_ACC ?
                    . sprintf('%03d%s', mb_strlen($params['VK_REC_NAME'],'UTF-8'), $params['VK_REC_NAME'])
                    . sprintf('%03d%s', mb_strlen($params['VK_SND_ACC'],'UTF-8'), $params['VK_SND_ACC'])
                    . sprintf('%03d%s', mb_strlen($params['VK_SND_NAME'],'UTF-8'), $params['VK_SND_NAME'])
                    . sprintf('%03d%s', mb_strlen($params['VK_REF'],'UTF-8'), $params['VK_REF'])
                    . sprintf('%03d%s', mb_strlen($params['VK_MSG'],'UTF-8'), $params['VK_MSG'])
                    . sprintf('%03d%s', mb_strlen($params['VK_T_DATETIME'],'UTF-8'), $params['VK_T_DATETIME']);
        }
        else {
            $data .= sprintf('%03d%s', mb_strlen($params['VK_REF'],'UTF-8'), $params['VK_REF'])
                    . sprintf('%03d%s', mb_strlen($params['VK_MSG'],'UTF-8'), $params['VK_MSG']);
        }

        return $data;
    }

    protected function verifyData($data, $mac)
    {
        $key = openssl_pkey_get_public(Mage::getStoreConfig('payment/' . $this->_code . '/bank_certificate'));
        $result = openssl_verify($data, base64_decode($mac), $key);
        openssl_free_key($key);

        return $result;
    }

    /**
     * Checks if private and public keys exist
     * If they don't then method is not enabled
     *
     * @return Multon_Payment_Model_Abstract
     */
    public function validate()
    {
        $key = openssl_pkey_get_public(Mage::getStoreConfig('payment/' . $this->_code . '/bank_certificate'));
        if ($key === false) {
            Mage::log(sprintf('%s (%s): Public key not found for %s', __METHOD__, __LINE__, $this->_code), null, $this->logFile);
            Mage::throwException($this->_getHelper()->__('Public key for ' . $this->_code . ' not set'));
        }
        return parent::validate();
    }

}
