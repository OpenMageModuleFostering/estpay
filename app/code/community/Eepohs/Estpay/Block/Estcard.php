<?php

/**
 * Estcard.php
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
 * Estcard form block for Estpay
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
 * @version    Release: 1.4.0
 * @link       http://eepohs.com/
 */
class Eepohs_Estpay_Block_Estcard extends Eepohs_Estpay_Block_Abstract
{

    protected $_code = 'eepohs_estcard';
    protected $_gateway = 'estcard';

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
            sprintf("%012s", (round($order->getBaseGrandTotal(), 2) * 100));
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

    /**
     * Get Estcard method logo URL
     * 
     * @return string
     */
    public function getMethodLogoUrl()
    {
        return $this->getSkinUrl(
                sprintf(
                    'images/eepohs/estpay/%s_logo_120x31.gif',
                    strtolower($this->_gateway)
                )
        );
    }

}
