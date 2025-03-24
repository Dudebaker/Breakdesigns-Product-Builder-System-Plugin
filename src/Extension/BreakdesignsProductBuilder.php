<?php
	/**
	 * @package         plg_system_breakdesignsproductbuilder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	namespace Joomla\Plugin\System\BreakdesignsProductBuilder\Extension;
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Installer\Installer;
	use Joomla\CMS\Language\Text;
	use Joomla\CMS\Plugin\CMSPlugin;
	use Joomla\CMS\Router\Route;
	use Joomla\Database\DatabaseAwareTrait;
	use Joomla\Database\DatabaseInterface;
	use Joomla\Event\Event;
	use Joomla\Event\SubscriberInterface;
	use Joomla\Plugin\System\BreakdesignsProductBuilder\Helper\CoreFileExtenderHelper;
	use VirtueMartCart;
	use VmConfig;
	
	defined('_JEXEC') or die;
	
	class BreakdesignsProductBuilder extends CMSPlugin implements SubscriberInterface
	{
		use DatabaseAwareTrait;
		
		#region Joomla Events
		
		/**
		 * {@inheritdoc}
		 * @since version
		 */
		public static function getSubscribedEvents() : array
		{
			return [
				'onAfterInitialise'                         => 'onAfterInitialise',
				'onExtensionAfterUpdate'                    => 'onExtensionAfterUpdate',
				'onInstallerAfterInstaller'                 => 'onInstallerAfterInstaller',
				'plgVmOnAddToCartBreakDesignProductBuilder' => 'onAddToCartBreakDesignProductBuilder',
				#'plgVmOnAddToCart'                          => 'onAddToCart',
				#'plgVmOnAddToCartFilter'                    => 'onAddToCartFilter',
				#'plgVmOnRemoveFromCart'                     => 'onRemoveFromCart'
			];
		}
		
		/**
		 * Listener for the `onAfterInitialise` event
		 *
		 * This event is triggered after the framework has loaded and the application initialise method has been called.
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onAfterInitialise() : void
		{
			$this->CheckCoreFileExtender() ||
			$this->CheckForUpdateCartEvent() ||
			$this->TaskRemoveProductBuilderProductsFromCart();
		}
		
		/**
		 * Listener for the `onExtensionAfterUpdate` event
		 *
		 * Executed after update of an extension (but not always?)
		 * Check if any overrides have to be added
		 *
		 * @param \Joomla\CMS\Installer\Installer|null $installer Installer object
		 *
		 * @return  void
		 *
		 * @since        version
		 * @noinspection PhpMissingParamTypeInspection
		 */
		public function onExtensionAfterUpdate($installer = null) : void
		{
			CoreFileExtenderHelper::checkOverrides($installer);
		}
		
		/**
		 * Listener for the `onInstallerAfterInstaller` event
		 *
		 * Executed after installation of an extension (or update via install instead of update in backend)
		 * Check if any overrides have to be added
		 *
		 * @param \Joomla\Event\Event|null $event
		 *
		 * @since version
		 */
		public function onInstallerAfterInstaller(Event $event = null) : void
		{
			if ($event === null)
			{
				return;
			}
			
			$arguments = $event->getArguments();
			
			foreach ($arguments as $argument)
			{
				if ($argument instanceof Installer)
				{
					CoreFileExtenderHelper::checkOverrides($argument);
					break;
				}
			}
		}
		
		/**
		 * Listener for the `plgVmOnAddToCart` event
		 *
		 * This event is triggered after a product got added to the cart
		 *
		 * @param \Joomla\Event\Event|null $event
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onAddToCart(?Event $event) : void
		{
			# Here we are too late, the product gets added up to the already existing ones which maybe do not have the pbproduct_id set, so the whole is done with the own onAddToCartBreakDesignProductBuilder event
			
			
			/*if ($event === null)
			{
				return;
			}
			
			$cart = $event->getArgument(0);
			if (empty($cart))
			{
				return;
			}
			
			$input         = $this->getApplication()?->getInput();
			$pbProductId   = $input->getInt('pbproduct_id', 0);
			$pbproductUuid = $input->getString('pbproduct_uuid', 0);
			
			if ($pbProductId === 0)
			{
				return;
			}
			
			foreach ($cart->cartProductsData as $cart_key => $row)
			{
				if ($row['virtuemart_product_id'] !== $cart->lastAddedProduct)
				{
					continue;
				}
				
				$row['customProductData']['pbproduct_id']   = $pbProductId;
				$row['customProductData']['pbproduct_uuid'] = $pbproductUuid;
				
				$cart->cartProductsData[$cart_key] = $row;
				break;
			}
			
			$event->setArgument(0, $cart);*/
		}
		
		/**
		 * Listener for the `plgVmOnAddToCartFilter` event
		 *
		 * This event is triggered before a product gets added to the cart for each customfield of the product
		 *
		 * @param \Joomla\Event\Event|null $event
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onAddToCartFilter(?Event $event) : void
		{
			# This does only work if the product has customfields, otherwise the event does not get triggered, so the whole is done with the own onAddToCartBreakDesignProductBuilder event
			
			
			/*if ($event === null)
			{
				return;
			}
			
			$arrayKeyProduct           = 0;
			$arrayKeyCustomfield       = 1;
			$arrayKeyCustomProductData = 2;
			$arrayKeyCustomFiltered    = 3;
			
			$product           = $event->getArgument($arrayKeyProduct);
			$customfield       = $event->getArgument($arrayKeyCustomfield);
			$customProductData = $event->getArgument($arrayKeyCustomProductData);
			$customFiltered    = $event->getArgument($arrayKeyCustomFiltered);
			
			if (empty($product))
			{
				return;
			}
			
			if (array_key_exists('pbproduct_id', $customProductData))
			{
				return;
			}
			
			$input         = $this->getApplication()?->getInput();
			$pbProductId   = $input->getInt('pbproduct_id', 0);
			$pbproductUuid = $input->getString('pbproduct_uuid', 0);
			
			if ($pbProductId === 0)
			{
				return;
			}
			
			$customProductData['pbproduct_id']   = $pbProductId;
			$customProductData['pbproduct_uuid'] = $pbproductUuid;
			
			$event->setArgument($arrayKeyCustomProductData, $customProductData);
			$event->setArgument($arrayKeyCustomFiltered, true);*/
		}
		
		/**
		 * Listener for the `plgOnAddToCartBreakDesignProductBuilder` event
		 *
		 * This event is triggered before the customfields of a product get checked
		 *
		 * @param \Joomla\Event\Event|null $event
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onAddToCartBreakDesignProductBuilder(?Event $event) : void
		{
			if ($event === null)
			{
				return;
			}
			
			$productData = $event->getArgument(0);
			
			$input         = $this->getApplication()?->getInput();
			$pbProductId   = $input->getInt('pbproduct_id', 0);
			$pbproductUuid = $input->getString('pbproduct_uuid', 0);
			
			if ($pbProductId === 0)
			{
				return;
			}
			
			$productData['customProductData']['pbproduct_id']         = $pbProductId;
			$productData['customProductData']['pbproduct_uuid']       = $pbproductUuid;
			$productData['customProductData']['pbproduct_uuid_entry'] = uniqid('', true);
			
			$event->setArgument(0, $productData);
		}
		
		/**
		 * Listener for the `plgVmOnRemoveFromCart` event
		 *
		 * This event is triggered before a product gets removed from the cart
		 *
		 * @param \Joomla\Event\Event|null $event
		 *
		 * @return  void
		 *
		 * @since version
		 */
		public function onRemoveFromCart(?Event $event) : void
		{
			# plgVmOnRemoveFromCart event is useless.
			# VM4.4 and newer do not use this function, they always use VirtueMartCart->updateProductCart .
			# In this function is no event to attach.
			# It's not possible to check if the removed product is a Product Builder one.
			# Workaround with direct requests check (see function CheckForCartProductRemoveEvent below)
			# But to be sure we have to create template-overrides to no even show the remove possibility.
		}
		#endregion
		
		#region Request Handling
		
		/**
		 * Checks if the core file extension exists only if the plugin parameter is set to do so
		 *
		 * @since version
		 */
		public function CheckCoreFileExtender() : bool
		{
			if (!$this->getApplication()?->isClient('administrator'))
			{
				return false;
			}
			
			$checkCoreExtension = $this->params->get('check_core_extension', 1);
			
			if (!$checkCoreExtension)
			{
				return false;
			}
			
			CoreFileExtenderHelper::checkOverrides(null, true);
			
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
			            ->update($db->quoteName('#__extensions'))
			            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode(['check_core_extension' => 0])))
			            ->where($db->quoteName('element') . ' = ' . $db->quote('breakdesignsproductbuilder'))
			            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
			$db->setQuery($query);
			$db->execute();
			
			$this->getApplication()?->enqueueMessage(Text::_('Core file extender checked'));
			
			return true;
		}
		
		/**
		 * Check if a product builder product gets changed (removed or quantity changed)
		 * Needed since Virtuemart 4.4 and newer do not use the VirtueMartCart->removeProductCart function and so the onRemoveFromCart is never called
		 * And for quantity changes is nowhere an event
		 *
		 * @since version
		 */
		public function CheckForUpdateCartEvent() : bool
		{
			if ($this->getApplication()?->isClient('administrator'))
			{
				return false;
			}
			
			$input = $this->getApplication()?->getInput();
			
			$option = $input->get('option', '', 'string');
			$task   = $input->get('task', '', 'string');
			
			if ($option !== 'com_virtuemart' || $task !== 'updatecart')
			{
				return false;
			}
			
			$quantities = $input->post->get('quantity', '', 'array');
			
			if (empty($quantities))
			{
				return false;
			}
			
			$cart = $this->getVirtuemartCart();
			
			foreach ($quantities as $key => $quantity)
			{
				$product = $cart->cartProductsData[$key];
				
				if (!array_key_exists('pbproduct_uuid', $product['customProductData']))
				{
					continue;
				}
				
				$input->set('quantity', []);
				$input->post->set('quantity', []);
				
				if (array_key_exists('delete_' . $key, $_POST))
				{
					$input->set('delete_' . $key, '');
					$input->post->set('delete_' . $key, '');
					
					$this->getApplication()?->enqueueMessage(Text::_('PLG_SYSTEM_BREAKDESIGNS_PRODUCT_BUILDER_CANNOT_REMOVE_PRODUCT'), $this->getApplication()::MSG_WARNING);
				} else
				{
					$this->getApplication()?->enqueueMessage(Text::_('PLG_SYSTEM_BREAKDESIGNS_PRODUCT_BUILDER_CANNOT_CHANGE_PRODUCT_QUANTITY'), $this->getApplication()::MSG_WARNING);
				}
				
				return true;
			}
			
			return false;
		}
		
		/**
		 * Remove Product Builder products from cart
		 *
		 * @since version
		 */
		public function TaskRemoveProductBuilderProductsFromCart() : bool
		{
			if ($this->getApplication()?->isClient('administrator'))
			{
				return false;
			}
			
			$input              = $this->getApplication()?->getInput();
			$task               = $input->get('task', '', 'string');
			$productBuilderUuid = $input->get('pb_uuid', '', 'string');
			
			if ($task !== 'pbproduct_remove' || empty($productBuilderUuid))
			{
				return false;
			}
			
			$cart = $this->getVirtuemartCart();
			
			if (!isset($cart->cartProductsData) || count($cart->cartProductsData) === 0)
			{
				return false;
			}
			
			foreach ($cart->cartProductsData as $key => $product)
			{
				$cartProductProductBuilderUuid = $cart->cartProductsData[$key]['customProductData']['pbproduct_uuid'];
				if (!empty($cartProductProductBuilderUuid) && $cartProductProductBuilderUuid === $productBuilderUuid)
				{
					unset($cart->cartProductsData[$key]);
				}
			}
			
			$cart->setCartIntoSession(true);
			
			$this->getApplication()?->redirect(Route::_('index.php?option=com_virtuemart&view=cart', false));
			
			return true;
		}
		#endregion
		
		#region Helper function
		/**
		 * Function to load all dependencies and get the Virtuemart Cart
		 *
		 * @return VirtueMartCart
		 *
		 * @since version
		 */
		private function getVirtuemartCart() : VirtueMartCart
		{
			if (!class_exists('VmConfig'))
			{
				require(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
			}
			
			VmConfig::loadConfig();
			
			if (!class_exists('VirtueMartCart'))
			{
				require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'cart.php';
			}
			
			return VirtueMartCart::getCart();
		}
		#endregion
	}