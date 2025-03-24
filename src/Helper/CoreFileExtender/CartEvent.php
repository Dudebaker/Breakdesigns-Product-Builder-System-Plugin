<?php
	/**
	 * @package         plg_system_breakdesignsproductbuilder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	
	use Joomla\Plugin\System\BreakdesignsProductBuilder\Helper\CoreFileExtenderHelper;
	
	/**
	 * Function to check if an override has to be executed
	 * The function has to use the same name as the file with an installer parameter!
	 *
	 * @param \Joomla\CMS\Installer\Installer|null $installer
	 * @param bool                                 $force
	 *
	 * @since        version
	 * @noinspection PhpMissingParamTypeInspection
	 */
	function CartEvent($installer = null, $force = false) : void
	{
		checkCartEventOverride($installer, $force);
	}
	
	/**
	 * Function to add the needed card event
	 *
	 * @param \Joomla\CMS\Installer\Installer|null $installer
	 * @param bool                                 $force
	 *
	 * @since        version
	 * @noinspection PhpMissingParamTypeInspection
	 */
	function checkCartEventOverride($installer = null, $force = false) : void
	{
		$extensionNames = ['VIRTUEMART', 'VirtueMart Package', 'PLG_SYSTEM_BREAKDESIGNS_PRODUCT_BUILDER'];
		$extendName     = 'Breakdesigns Product Builder - Cart Event Extension';
		$extendContent  = ['vDispatcher::trigger(\'plgVmOnAddToCartBreakDesignProductBuilder\',array(&$productData));'];
		$extendFile     = 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'cart.php';
		$extendAfter    = '$productData[\'customProductData\'] = $customProductDataTmp;';
		$extendVersion  = 1.0;
		
		CoreFileExtenderHelper::handleCoreFileExtender($installer, $extensionNames, $extendName, $extendContent, $extendFile, null, $extendAfter, $extendVersion, $force);
	}