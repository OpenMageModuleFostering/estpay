<?php

class Multon_Payment_Block_Info extends Mage_Core_Block_Template
{

    /**
     * Returns array of enabled Multon_Payment
     * gateways
     *
     * @return array
     */
    public function getEnabledGateways()
    {
        $paymentMethods = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array();
        foreach ($paymentMethods as $paymentCode => $paymentModel) {
            if ($paymentModel instanceof Multon_Payment_Model_Abstract) {
                $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
                $formBlockType = $paymentModel->getFormBlockType();
                $formBlockInstance = Mage::getBlockSingleton($formBlockType);
                $methods[] = array(
                    'title' => $paymentTitle,
                    'code' => $paymentCode,
                    'logo' => $formBlockInstance->getMethodLogoUrl()
                );
            }
        }
        return $methods;
    }

}
