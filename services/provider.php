<?php
	/**
	 * @package         plg_system_breakdesignsproductbuilder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	use Joomla\CMS\Extension\PluginInterface;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\DI\Container;
	use Joomla\DI\ServiceProviderInterface;
	use Joomla\Event\DispatcherInterface;
	use Joomla\Plugin\System\BreakdesignsProductBuilder\Extension\BreakdesignsProductBuilder;
	
	defined('_JEXEC') or die;
	
	return new class () implements ServiceProviderInterface
	{
		/**
		 * {@inheritdoc}
		 */
		public function register(Container $container) : void
		{
			$container->set(
				PluginInterface::class,
				function (Container $container)
				{
					$dispatcher = $container->get(DispatcherInterface::class);
					$plugin     = new BreakdesignsProductBuilder($dispatcher, (array)PluginHelper::getPlugin('system', 'breakdesignsproductbuilder'));
					
					$plugin->setApplication(Factory::getApplication());
					
					return $plugin;
				}
			);
		}
	};
