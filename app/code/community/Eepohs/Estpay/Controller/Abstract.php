<?php

/**
 * Abstract.php
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
 * Abstract controller for Estpay that all controllers will inherit from
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
class Eepohs_Estpay_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    /**
     *
     * @var specifies log file name for Estpay
     */
    protected $logFile = 'estpay.log';

    /**
     *
     * @var Specifies model to be used to verify response from bank
     */
    protected $_model;

    /**
     * This action redirects user to bank for payment
     *
     * @return void
     */
    public function redirectAction()
    {

        /* Send order confirmation */
        if (Mage::getStoreConfig('payment/' . $this->_code . '/order_confirmation') == '1') {
            try {
                $order = Mage::getModel('sales/order');
                $order->load(
                        Mage::getSingleton('checkout/session')->getLastOrderId()
                );
                $order->sendNewOrderEmail();
                $order->save();
            } catch (Exception $e) {
                Mage::log(
                        sprintf('%s(%s): %s', __METHOD__, __LINE__, print_r($e->getMessage(), true)
                        ), null, $this->logFile
                );
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * This is return action handler for Estpay
     * payment method
     * It verifies signature and creates invoice.
     * In case of verification failure it cancels the order
     *
     * @return void
     */
    public function returnAction()
    {

        Mage::log(
                sprintf(
                        '%s(%s)@%s: %s', __METHOD__, __LINE__, $_SERVER['REMOTE_ADDR'],
                        print_r($this->getRequest()->getParams(), true)
                ), null, $this->logFile
        );
        $session = Mage::getSingleton('checkout/session');
        $orderId = $session->getLastRealOrderId();
        if (!$orderId) {
            $orderId = $this->getRequest()->getParam('VK_STAMP');
        }
        if (!$orderId) {
            $this->_redirect('checkout/onepage/failure');
            return;
        }
        $model = Mage::getModel($this->_model);
        $model->setOrderId($orderId);
        $verify = $model->verify($this->getRequest()->getParams());
        switch ($verify) {
            case Eepohs_Estpay_Helper_Data::_VERIFY_SUCCESS:
                $model->createInvoice();
                $this->_redirect('checkout/onepage/success');
                break;
            case Eepohs_Estpay_Helper_Data::_VERIFY_CANCEL:
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                $order->cancel()->save();
                $this->_redirect('checkout/onepage/failure');
                break;
            case Eepohs_Estpay_Helper_Data::_VERIFY_CORRUPT:
            default:
                break;
        }
    }

}
