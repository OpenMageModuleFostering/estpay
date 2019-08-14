<?php

/**
 * @package    Eepohs
 * @subpackage Estpay
 */

/**
 * Estpay block for Nordea Bank
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
 * @version 1.3.0.1
 * @author Eepohs OÜ
 * @copyright 2012 Eepohs OÜ http://www.eepohs.com/
 *
 * @package    Eepohs
 * @subpackage Estpay
 * @category   Payment methods
 */
class Eepohs_Estpay_Block_Nordea
    extends Eepohs_Estpay_Block_Abstract
{

    protected $_code = 'eepohs_nordea';
    protected $_gateway = 'nordea';

    public function getFields()
    {

        $fields = array();
        $helper = Mage::helper('estpay');
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        $fields['SOLOPMT_VERSION'] = '0003';
        $fields['SOLOPMT_STAMP'] = time();
        $fields['SOLOPMT_RCV_ID'] =
        Mage::getStoreConfig(
            'payment/' . $this->_code . '/service_provider'
        );

        /* Choose language:
         * 3 = english, 4 = estonian, 6 = latvian, 7 = lithuanian
         */
        switch ( Mage::app()->getLocale()->getLocaleCode() ) {
            case 'et_EE':
                $language = '4';
                break;
            default:
                $language = '3';
                break;
        }
        $fields['SOLOPMT_LANGUAGE'] = $language;

        $fields['SOLOPMT_AMOUNT'] = number_format(
            $order->getBaseGrandTotal(), 2, '.', ''
        );
        $fields['SOLOPMT_REF'] = $helper->calcRef($order->getIncrementId());
        $fields['SOLOPMT_DATE'] = 'EXPRESS';
        $fields['SOLOPMT_MSG'] = __('Invoice number') . ' '
            . $order->getIncrementId();
        $fields['SOLOPMT_RETURN'] =
            Mage::getUrl(
                'estpay/' . $this->_gateway . '/return'
            ) . '?';
        $fields['SOLOPMT_CANCEL'] =
            Mage::getUrl(
                'estpay/' . $this->_gateway . '/return'
            ) . '?';
        $fields['SOLOPMT_REJECT'] =
            Mage::getUrl(
                'estpay/' . $this->_gateway . '/return'
            ) . '?';
        $fields['SOLOPMT_CONFIRM'] = 'YES';
        $fields['SOLOPMT_KEYVERS'] = '0001';
        $fields['SOLOPMT_CUR'] = 'EUR';

        $data =
            $fields['SOLOPMT_VERSION'] . '&' .
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
