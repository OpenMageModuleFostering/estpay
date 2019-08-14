<?php

/**
 * @package    Eepohs
 * @subpackage Estpay
 */

/**
 * Estpay Model for Nordea Bank
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
 * @license http://opensource.org/licenses/bsd-license.php
 * @version 1.3.1
 * @author Eepohs OÜ
 * @copyright 2012 Eepohs OÜ http://www.eepohs.com/
 *
 * @package    Eepohs
 * @subpackage Estpay
 * @category   Payment methods
 */
class Eepohs_Estpay_Model_Nordea extends Eepohs_Estpay_Model_Abstract
{

    protected $_code = 'eepohs_nordea';
    protected $_formBlockType = 'estpay/nordea';
    protected $_gateway = 'nordea';

    /**
     * Verifies response from Nordea
     *
     * @param array $params
     *
     * @return boolean
     */
    public function verify(array $params = array())
    {

        // No Express payment return data
        if ( !isset($params['SOLOPMT_RETURN_PAID']) )
            return false;

        $data =
            $params['SOLOPMT_RETURN_VERSION'] . '&' .
            $params['SOLOPMT_RETURN_STAMP'] . '&' .
            $params['SOLOPMT_RETURN_REF'] . '&' .
            $params['SOLOPMT_RETURN_PAID'] . '&' .
            Mage::getStoreConfig('payment/' . $this->_code . '/mac_key') . '&';

        // Invalid MAC code
        if (
            $params['SOLOPMT_RETURN_MAC']
            != strtoupper(md5($data))
        ) {
            Mage::log(
                sprintf(
                    "%s (%s)@%s: (Nordea) Invalid MAC code",
                    __METHOD__,
                    __LINE__,
                    $_SERVER['REMOTE_ADDR']
                )
            );
            return false;
        }

        $session = Mage::getSingleton('checkout/session');

        $helper = Mage::helper('estpay');
        // Reference number doesn't match.
        if (
            $helper->calcRef($session->getLastRealOrderId())
            != $params['SOLOPMT_RETURN_REF']
        ) {
            Mage::log(
                sprintf(
                    "%s (%s)@%s: (Nordea): Reference number doesn't match
                (potential tampering attempt).",
                    __METHOD__,
                    __LINE__,
                    $_SERVER['REMOTE_ADDR']
                )
            );
            return false;
        }

        return true;
    }

}
