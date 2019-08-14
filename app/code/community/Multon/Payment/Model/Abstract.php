<?php

abstract class Multon_Payment_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{
	protected $_canAuthorize = true;
	protected $_isGateway = true;
	protected $_canUseCheckout = true;
	protected $logFile = 'payment.log';

	/**
	 * Order Id to create invoice for
	 * @var string
	 */
	protected $_orderId;

	public abstract function getOrderPlaceRedirectUrl();

	/**
	 * Returns false.
	 *
	 * @return URL
	 */
	public function getCheckoutRedirectUrl()
	{
		return;
	}

	/**
	 * This method creates invoice for current order
	 */
	public function createInvoice()
	{
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());

		if (!$this->isLocked($this->getOrderId()))
		{
			if ($order->canInvoice())
			{

				if ($this->createLock($this->getOrderId()))
				{

					$invoice = $order->prepareInvoice();
					$invoice->pay()->register();
					$invoice->save();

					$order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
					$order->save();

					/* Release lock file right after creating invoice */
					$this->releaseLock($this->getOrderId());

					/* Send invoice */
					if (Mage::getStoreConfig('payment/' . $this->_code . '/invoice_confirmation') == '1')
					{
						$invoice->sendEmail(true, '');
					}

					Mage::register('current_invoice', $invoice);
				}
			} else
			{
				$this->log('Failed to create invoice for order ' . $this->getOrderId() . '. Reason: invoice already created', __METHOD__, __LINE__);
			}
		} else
		{
			$this->log('Failed to create invoice for order ' . $this->getOrderId() . '. Reason: order locked', __METHOD__, __LINE__);
		}
	}

	/**
	 *
	 * @param string $orderId
	 * @return string
	 */
	private function getLockfilePath($orderId)
	{
		return Mage::getBaseDir('var') . DS . 'locks' . DS . 'order_' . $orderId . '.lock';
	}

	/**
	 * Checks if given invoice is locked, i.e if it has
	 * a file in var/locks folder
	 *
	 * @param string $orderId
	 */
	public function isLocked($orderId)
	{
		return file_exists($this->getLockfilePath($orderId));
	}

	/**
	 * Locks order, i.e creates a lock file
	 * in var/locks folder
	 * @param string $orderId
	 */
	public function createLock($orderId)
	{
		$path = $this->getLockfilePath($orderId);
		if (!touch($this->getLockfilePath($orderId)))
		{
			$this->log('Failed to create lockfile ' . $path, __METHOD__, __LINE__);
			return false;
		}
		$this->log('Created lockfile ' . $path, __METHOD__, __LINE__);
		return true;
	}

	/**
	 * Releases lock for order, i.e deletes
	 * lock file from var/locks folder
	 *
	 * @param string $orderId
	 */
	public function releaseLock($orderId)
	{
		$path = $this->getLockfilePath($orderId);
		if (!unlink($path))
		{
			$this->log('Failed to delete lockfile ' . $path, __METHOD__, __LINE__);
			return false;
		}
		$this->log('Deleted lockfile ' . $path, __METHOD__, __LINE__);
		return true;
	}

	/**
	 * Abstract method to be overloaded by implementing classes.
	 * This is used to verify response from bank
	 *
	 * @return int
	 */
	public abstract function verify(array $params = array());

	protected function log($t, $m, $l)
	{
		Mage::log(sprintf('%s(%s)@%s: %s', $m, $l, $_SERVER['REMOTE_ADDR'], $t), null, $this->logFile);
	}

}
