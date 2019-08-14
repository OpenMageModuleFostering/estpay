<?php

abstract class Multon_Payment_Block_Abstract extends Mage_Payment_Block_Form
{
	protected $_gateway;

    public abstract function getMethodLogoUrl();

    public abstract function getFields();

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
     *
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
     * Checks if quick redirect is enabled and
     * returns javascript block that redirects user
     * to bank without intermediate page
     *
     * @since 1.3.0
     * @return outstr Javascript block
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
