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
 * Form block for Estpay methods that use iPizza standard
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
class Eepohs_Estpay_Block_IPizza extends Eepohs_Estpay_Block_Abstract
{

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

        $fields['VK_SERVICE'] = '1002';
        $fields['VK_VERSION'] = '008';
        $fields['VK_SND_ID'] = Mage::getStoreConfig('payment/' . $this->_code . '/vk_snd_id');
        $fields['VK_REF'] = '';
        $fields['VK_RETURN'] = Mage::getUrl('estpay/' . $this->_gateway . '/return');

        switch ( Mage::app()->getLocale()->getLocaleCode() ) {
            case 'et_EE':
                $language = 'EST';
                break;
            case 'ru_RU':
                $language = 'RUS';
                break;
            default:
                $language = 'ENG';
                break;
        }

        $fields['VK_LANG'] = $language;
        $fields['VK_STAMP'] = $order->getIncrementId();

        $fields['VK_AMOUNT'] = number_format(
            $order->getBaseGrandTotal(), 2, '.', ''
        );
        $fields['VK_CURR'] = 'EUR';
        $fields['VK_MSG'] =
            __('Order number') . ': ' . $order->getIncrementId();

        $data =
            sprintf('%03d%s', strlen($fields['VK_SERVICE']), $fields['VK_SERVICE'])
            . sprintf('%03d%s', strlen($fields['VK_VERSION']), $fields['VK_VERSION'])
            . sprintf('%03d%s', strlen($fields['VK_SND_ID']), $fields['VK_SND_ID'])
            . sprintf('%03d%s', strlen($fields['VK_STAMP']), $fields['VK_STAMP'])
            . sprintf('%03d%s', strlen($fields['VK_AMOUNT']), $fields['VK_AMOUNT'])
            . sprintf('%03d%s', strlen($fields['VK_CURR']), $fields['VK_CURR'])
            . sprintf('%03d%s', strlen($fields['VK_REF']), $fields['VK_REF'])
            . sprintf('%03d%s', strlen($fields['VK_MSG']), $fields['VK_MSG']);

        $key = openssl_pkey_get_private(
            Mage::getStoreConfig('payment/' . $this->_code . '/private_key'), ''
        );
        $signature = null;
        openssl_sign($data, $signature, $key);
        $fields['VK_MAC'] = base64_encode($signature);
        openssl_free_key($key);

        return $fields;
    }

}