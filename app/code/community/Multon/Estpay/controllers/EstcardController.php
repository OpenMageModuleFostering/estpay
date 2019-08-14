<?php

class Multon_Estpay_EstcardController extends Multon_Payment_Controller_Abstract
{

    protected $_model = 'estpay/estcard';
    protected $_code = 'multon_estcard';
	protected $orderNoField = 'ecuno';

}

