<?php
/**
 * @author Tanel Raja <tanel.raja@multon.ee>
 * @copyright Copyright (c) 2014, Multon (http://multon.ee/)
 */
class Multon_Core_model_Observer {
    
    public function multon_tab($observer)
    {
	$config = $observer->getConfig();
	$config->getNode('tabs')->multon->label = '<img style="margin-top: 4px;" src="' . 
		Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 
		'adminhtml/default/default/images/multon/multon_blue.png" />';
	return $this;
    }
    
}