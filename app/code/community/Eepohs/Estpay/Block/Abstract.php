<?php

/**
 * @package    Eepohs
 * @subpackage Estpay
 */

/**
 * Abstract block for Estpay payment methods (different banks)
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
class Eepohs_Estpay_Block_Abstract
    extends Mage_Payment_Block_Form
{

    /**
     * Returns payment gateway URL
     *
     * @return string Gateway URL
     */
    public function getGatewayUrl()
    {
        return Mage::getStoreConfig('payment/' . $this->_code . '/gateway_url');
    }

    /**
     * Adds payment mehtod logotypes after method name
     * @return string
     */
    public function getMethodLabelAfterHtml()
    {
        $blockHtml = sprintf(
            '<img src="%1$s"
                title="%2$s"
                alt="%2$s"
                class="payment-method-logo"/>',
            $this->getMethodLogoUrl(), ucfirst($this->_gateway)
        );
        return $blockHtml;
    }

    /**
     * Returns payment method logo URL
     *
     * @return string
     */
    public function getMethodLogoUrl()
    {
        return $this->getSkinUrl(
            sprintf(
                'images/eepohs/estpay/%s_logo_88x31.gif',
                strtolower($this->_gateway)
            )
        );
    }

    /**
     * Checks if quick redirect is enabled and
     * returns javascript block that redirects user
     * to bank without intermediate page
     *
     * @since 2.0.0
     * @return outstr javascript block
     */
    public function getQuickRedirectScript()
    {
        $outstr = '';
        if (
            Mage::getStoreConfig('payment/' . $this->_code . '/quick_redirect')
        ) {
            $outstr = '<script type="text/javascript"><!--
                if($("GatewayForm")){$("GatewayForm").submit();}
                //--></script>';
        }
        return $outstr;
    }

}