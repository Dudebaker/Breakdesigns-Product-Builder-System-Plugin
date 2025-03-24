<?php
	/**
	 * @package         plg_system_breakdesignsproductbuilder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	namespace Joomla\Plugin\System\BreakdesignsProductBuilder\Helper;
	
	use Joomla\CMS\Installer\Installer;
	
	defined('_JEXEC') or die;
	
	/**
	 * Handles the core file extension with custom code
	 *
	 * @since version
	 */
	class CoreFileExtenderHelper
	{
		#region Public
		/**
		 * Method to find overrides which have to be executed
		 *
		 * @param \Joomla\CMS\Installer\Installer|null $installer
		 * @param bool                                 $force
		 *
		 * @since        version
		 * @noinspection PhpMissingParamTypeInspection
		 */
		public static function checkOverrides($installer = null, bool $force = false) : void
		{
			$directory            = __DIR__ . DS . 'CoreFileExtender';
			$coreFilExtenderFiles = scandir($directory);
			
			foreach ($coreFilExtenderFiles as $coreFilExtenderFile)
			{
				if (!str_ends_with($coreFilExtenderFile, '.php'))
				{
					continue;
				}
				
				require_once $directory . DS . $coreFilExtenderFile;
				
				$functionName = substr($coreFilExtenderFile, 0, -4);
				
				if (!function_exists($functionName))
				{
					continue;
				}
				
				$functionName($installer, $force);
			}
		}
		#endregion
		
		#region FileExtender
		/**
		 * Method to handle the core file extension with custom code
		 *
		 * @param Installer|null $installer
		 * @param array          $extensionName
		 * @param string         $extendName
		 * @param array          $extendContent
		 * @param string         $extendFile
		 * @param string|null    $extendBefore
		 * @param string|null    $extendAfter
		 * @param float          $extendVersion
		 * @param bool           $force
		 *
		 * @since        version
		 */
		public static function handleCoreFileExtender(mixed $installer, array $extensionName, string $extendName, array $extendContent, string $extendFile, string $extendBefore = null, string $extendAfter = null, float $extendVersion = 1, bool $force = false) : void
		{
			if (empty($extendBefore) && empty($extendAfter))
			{
				return;
			}
			
			if (!$force || ($installer instanceof Installer && !in_array($installer->manifest->name, $extensionName, false)))
			{
				// Executed from installer but wrong extension
				return;
			}
			
			$extendFile = JPATH_ROOT . DS . $extendFile;
			
			if (!file_exists($extendFile))
			{
				return;
			}
			
			$fileContent              = file_get_contents($extendFile);
			$extenderPrefix           = 'Core File Extender';
			$extendNameVersion        = "### $extenderPrefix - $extendName # v$extendVersion ###";
			$extendNameWithoutVersion = "### $extenderPrefix - $extendName #";
			$extendContentEnd         = '### END $extenderPrefix ###';
			
			if (str_contains($fileContent, $extendNameVersion))
			{
				// override already in place
				return;
			}
			
			$fileLineSeparator = self::detectNewlineType($fileContent);
			
			$extendPadding = self::getCoreFileExtensionPadding($fileContent, $fileLineSeparator, $extendBefore, $extendAfter);
			
			if (str_contains($fileContent, $extendPadding . $extendNameWithoutVersion))
			{
				$fileContent = self::removeCoreFileExtension($fileContent, $fileLineSeparator, $extendPadding . $extendNameWithoutVersion, $extendContentEnd, !empty($extendAfter));
			}
			
			self::addCoreFileExtension($extendFile, $fileContent, $fileLineSeparator, $extendNameVersion, $extendContent, $extendContentEnd, $extendPadding, $extendBefore, $extendAfter);
		}
		
		/**
		 * Method to detect the new line type (\r\n, \r, \n)
		 *
		 * @param string $content
		 *
		 * @return string
		 *
		 * @since version
		 */
		private static function detectNewlineType(string $content) : string
		{
			$arr = array_count_values(
				explode(
					' ',
					preg_replace(
						'/[^\r\n]*(\r\n|\n|\r)/',
						'\1 ',
						$content
					)
				)
			);
			
			arsort($arr);
			
			$newLineType = key($arr);
			
			if (is_null($newLineType) || is_numeric($newLineType))
			{
				return "\n";
			}
			
			return (string)$newLineType;
		}
		
		/**
		 * Method to detect the spacing of the line where the custom code will be added
		 *
		 * @param string      $fileContent
		 * @param string      $fileLineSeparator
		 * @param string|null $extendBefore
		 * @param string|null $extendAfter
		 *
		 * @return string
		 *
		 * @since version
		 */
		private static function getCoreFileExtensionPadding(string $fileContent, string $fileLineSeparator, string $extendBefore = null, string $extendAfter = null) : string
		{
			$textTillExtendBeforeAfter = substr($fileContent, 0, strpos($fileContent, $extendBefore ?? $extendAfter));
			
			return substr($textTillExtendBeforeAfter, strrpos($textTillExtendBeforeAfter, $fileLineSeparator) + strlen($fileLineSeparator));
		}
		
		/**
		 * Method to add the custom-code into the file on the position of $extendBefore or $extendAfter
		 *
		 * @param string      $extendFile
		 * @param string      $fileContent
		 * @param string      $fileLineSeparator
		 * @param string      $extendNameVersion
		 * @param array       $extendContent
		 * @param string      $extendContentEnd
		 * @param string      $extendPadding
		 * @param string|null $extendBefore
		 * @param string|null $extendAfter
		 *
		 * @since version
		 */
		private static function addCoreFileExtension(string $extendFile, string $fileContent, string $fileLineSeparator, string $extendNameVersion, array $extendContent, string $extendContentEnd, string $extendPadding, string $extendBefore = null, string $extendAfter = null) : void
		{
			$extendText = '';
			
			if (!is_null($extendAfter))
			{
				$extendText .= $extendPadding . $extendAfter . $fileLineSeparator . $fileLineSeparator;
			}
			
			$extendText .= $extendPadding . $extendNameVersion . $fileLineSeparator;
			
			foreach ($extendContent as $ec)
			{
				$extendText .= $extendPadding . $ec . $fileLineSeparator;
			}
			
			$extendText .= $extendPadding . $extendContentEnd . $fileLineSeparator;
			
			if (!is_null($extendBefore))
			{
				$extendText .= $fileLineSeparator . $extendPadding . $extendBefore;
			}
			
			$fileContents = str_replace($extendPadding . ($extendBefore ?? $extendAfter), $extendText, $fileContent);
			file_put_contents($extendFile, $fileContents);
		}
		
		/**
		 * Method to remove the custom-code block
		 *
		 * @param string $fileContent
		 * @param string $fileLineSeparator
		 * @param string $extendName
		 * @param string $extendContentEnd
		 * @param bool   $extendAfter
		 *
		 * @return string
		 *
		 * @since version
		 */
		private static function removeCoreFileExtension(string $fileContent, string $fileLineSeparator, string $extendName, string $extendContentEnd, bool $extendAfter = false) : string
		{
			$removeLineSeparatorsBefore = strlen($fileLineSeparator);
			
			if ($extendAfter)
			{
				$removeLineSeparatorsBefore += strlen($fileLineSeparator);
			}
			
			$oldVersionPos    = strpos($fileContent, $extendName) - $removeLineSeparatorsBefore;
			$oldVersionPosEnd = strpos($fileContent, $extendContentEnd) + strlen($extendContentEnd) + strlen($fileLineSeparator);
			$oldVersionText   = substr($fileContent, $oldVersionPos, $oldVersionPosEnd - $oldVersionPos);
			
			return str_replace($oldVersionText, '', $fileContent);
		}
		#endregion
	}