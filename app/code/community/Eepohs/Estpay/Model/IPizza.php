<?php

/**
 * IPizza.php
 *
 * PHP version 5
 *
 * @category   Magento
 * @package    Eepohs
 * @subpackage Estpay
 * @author     Eepohs OÜ <info@eepohs.com>
 * @license    http://opensource.org/licenses/bsd-license.php BSDL
 * @link       http://eepohs.com/
 */

/**
 * Base model for all Estpay payment methods that use iPizza standard
 *
 * PLEASE READ THIS SOFTWARE LICENSE AGREEMENT ("LICENSE") CAREFULLY
 * BEFORE USING THE SOFTWARE. BY USING THE SOFTWARE, YOU ARE AGREEING
 * TO BE BOUND BY THE TERMS OF THIS LICENSE.
 * IF YOU DO NOT AGREE TO THE TERMS OF THIS LICENSE, DO NOT USE THE SOFTWARE.
 *
 * Copyright (c) 2012, Eepohs OÜ
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Community
 * @package    Eepohs
 * @subpackage Estpay
 * @author     Eepohs OÜ <info@eepohs.com>
 * @copyright  2012 Eepohs OÜ
 * @license    http://opensource.org/licenses/bsd-license.php BSDL
 * @version    Release: 1.3.2.3
 * @link       http://eepohs.com/
 */
class Eepohs_Estpay_Model_IPizza extends Eepohs_Estpay_Model_Abstract
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
            return Eepohs_Estpay_Helper_Data::_VERIFY_CORRUPT;

        $test_success = false;
        $data = null;

        switch ($params['VK_SERVICE']) {
            case '1101': // success
                $test_success = true;
                break;
            case '1901': // fail
                break;
            default:
                Mage::log(sprintf('%s (%s)@%s: IPizza return service is not 1101/1901: %s', __METHOD__, __LINE__,
                                $_SERVER['REMOTE_ADDR'], $params['VK_SERVICE']), null, $this->logFile);
                return Eepohs_Estpay_Helper_Data::_VERIFY_CORRUPT;
        }

        $vkSndId = Mage::getStoreConfig('payment/' . $this->_code . '/vk_snd_id');

        if (!isset($params['VK_REC_ID']) || $params['VK_REC_ID'] != $vkSndId) {
            Mage::log(sprintf('%s (%s)@%s: Wrong merchant ID used for return: %s vs %s', __METHOD__, __LINE__,
                            $_SERVER['REMOTE_ADDR'], $params['VK_REC_ID'], $vkSndId
                    ), null, $this->logFile
            );
            return Eepohs_Estpay_Helper_Data::_VERIFY_CORRUPT;
        }

        if ($test_success) {
            $data = sprintf('%03d%s', strlen($params['VK_SERVICE']), $params['VK_SERVICE'])
                    . sprintf('%03d%s', strlen($params['VK_VERSION']), $params['VK_VERSION'])
                    . sprintf('%03d%s', strlen($params['VK_SND_ID']), $params['VK_SND_ID'])
                    . sprintf('%03d%s', strlen($params['VK_REC_ID']), $params['VK_REC_ID'])
                    . sprintf('%03d%s', strlen($params['VK_STAMP']), $params['VK_STAMP'])
                    . sprintf('%03d%s', strlen($params['VK_T_NO']), $params['VK_T_NO'])
                    . sprintf('%03d%s', strlen($params['VK_AMOUNT']), $params['VK_AMOUNT'])
                    . sprintf('%03d%s', strlen($params['VK_CURR']), $params['VK_CURR'])
                    . sprintf('%03d%s', strlen($params['VK_REC_ACC']), $params['VK_REC_ACC'])
                    . sprintf('%03d%s', strlen($params['VK_REC_NAME']), $params['VK_REC_NAME'])
                    . sprintf('%03d%s', strlen($params['VK_SND_ACC']), $params['VK_SND_ACC'])
                    . sprintf('%03d%s', strlen($params['VK_SND_NAME']), $params['VK_SND_NAME'])
                    . sprintf('%03d%s', strlen($params['VK_REF']), $params['VK_REF'])
                    . sprintf('%03d%s', strlen($params['VK_MSG']), $params['VK_MSG'])
                    . sprintf('%03d%s', strlen($params['VK_T_DATE']), $params['VK_T_DATE']);
        }
        else {
            $data = sprintf('%03d%s', strlen($params['VK_SERVICE']), $params['VK_SERVICE'])
                    . sprintf('%03d%s', strlen($params['VK_VERSION']), $params['VK_VERSION'])
                    . sprintf('%03d%s', strlen($params['VK_SND_ID']), $params['VK_SND_ID'])
                    . sprintf('%03d%s', strlen($params['VK_REC_ID']), $params['VK_REC_ID'])
                    . sprintf('%03d%s', strlen($params['VK_STAMP']), $params['VK_STAMP'])
                    . sprintf('%03d%s', strlen($params['VK_REF']), $params['VK_REF'])
                    . sprintf('%03d%s', strlen($params['VK_MSG']), $params['VK_MSG']);
        }


        $key = openssl_pkey_get_public(Mage::getStoreConfig('payment/' . $this->_code . '/bank_certificate'));
        $result = openssl_verify($data, base64_decode($params['VK_MAC']), $key);
        openssl_free_key($key);

        switch ($result) {
            case 1: // ssl verify successful
                if ($test_success)
                    return Eepohs_Estpay_Helper_Data::_VERIFY_SUCCESS;
                else
                    return Eepohs_Estpay_Helper_Data::_VERIFY_CANCEL;

            case 0: // ssl verify failed
                Mage::log(sprintf(
                                '%s (%s)@%s: Verification of signature failed for %s', __METHOD__, __LINE__,
                                $_SERVER['REMOTE_ADDR'], $params['VK_SND_ID']
                        ), null, $this->logFile);

                return Eepohs_Estpay_Helper_Data::_VERIFY_CORRUPT;

            case -1: // ssl verify error
            default:
                $error = '';
                while ($msg = openssl_error_string())
                    $error .= $msg . "\n";
                Mage::log(sprintf(
                                '%s (%s)@%s: Verification of signature error for %s : %s', __METHOD__, __LINE__,
                                $_SERVER['REMOTE_ADDR'], $params['VK_SND_ID'], $error
                        ), null, $this->logFile);

                return Eepohs_Estpay_Helper_Data::_VERIFY_CORRUPT;
        }
    }

    /**
     * Checks if private and public keys exist
     * If they don't then method is not enabled
     *
     * @return Eepohs_Estpay_Model_Abstract
     */
    public function validate()
    {
        $key = openssl_pkey_get_public(
                Mage::getStoreConfig(
                        'payment/' . $this->_code . '/bank_certificate'
                )
        );
        if ($key === false) {
            Mage::log(
                    sprintf(
                            '%s (%s): Public key not found for %s', __METHOD__, __LINE__, $this->_code
                    ), null, $this->logFile
            );
            Mage::throwException(
                    $this->_getHelper()->__(
                            'Public key for ' . $this->_code . ' not set'
                    )
            );
        }
        return parent::validate();
    }

}
