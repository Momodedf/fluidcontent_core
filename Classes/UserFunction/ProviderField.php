<?php
namespace FluidTYPO3\FluidcontentCore\UserFunction;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use FluidTYPO3\FluidcontentCore\Provider\CoreContentProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Variants Field TCA user function
 */
class ProviderField {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var CoreContentProvider
	 */
	protected $provider;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->provider = $this->objectManager->get('FluidTYPO3\FluidcontentCore\Provider\CoreContentProvider');
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function createVariantsField(array $parameters) {
		$extensionKeys = $this->provider->getVariantExtensionKeysForContentType($parameters['row']['CType']);
		$variantNames = $this->getVariantsLabel($extensionKeys);
		$defaults = $this->provider->getDefaults();
		$preSelected = $parameters['row']['content_variant'];
		if (CoreContentProvider::MODE_PRESELECT === $defaults['mode'] && TRUE === empty($preSelected)) {
			$preSelected = $defaults['variant'];
		}
		if (TRUE === is_array($extensionKeys) && 0 < count($extensionKeys)) {
			$options = array_combine($extensionKeys, $variantNames);
		} else {
			$options = array();
		}
		return $this->renderSelectField($parameters, $options, $preSelected);
	}

	/**
	 * @param array $parameters
	 * @param array $options
	 * @param mixed $selectedValue
	 * @return string
	 */
	protected function renderSelectField($parameters, $options, $selectedValue) {
		$hasSelectedValue = (TRUE === empty($selectedValue) || TRUE === array_key_exists($selectedValue, $options));
		$selected = (TRUE === empty($selectedValue) ? ' selected="selected"' : NULL);
		$html = array(
			'<select class="select" name="' . $parameters['itemFormElName'] . '" onchange="' . $parameters['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] . ';' . $parameters['fieldChangeFunc']['alert'] . '">',
			'<option' . $selected . ' value="">' . LocalizationUtility::translate('tt_content.nativeLabel', 'FluidcontentCore') . '</option>'
		);
		foreach ($options as $value => $label) {
			$selected = $value === $selectedValue ? ' selected="selected"' : NULL;
			$html[] = '<option' . $selected . ' value="' . $value . '">' . $label . '</option>';
		}
		if (FALSE === $hasSelectedValue) {
			$html[] = '<option selected="selected">INVALID: ' . $selectedValue . '</option>';
		}
		$html[] = '</select>';
		return implode(LF, $html);
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	public function createVersionsField(array $parameters) {
		$defaults = $this->provider->getDefaults();
		$preSelectedVariant = $parameters['row']['content_variant'];
		$preSelectedVersion = $parameters['row']['content_version'];
		if (CoreContentProvider::MODE_PRESELECT === $defaults['mode']) {
			if (TRUE === empty($preSelectedVariant)) {
				$preSelectedVariant = $defaults['variant'];
			}
			if (TRUE === empty($preSelectedVersion)) {
				$preSelectedVersion = $defaults['version'];
			}
		}

		$versions = $this->provider->getVariantVersions($parameters['row']['CType'], $preSelectedVariant);
		if (TRUE === is_array($versions) && 0 < count($versions)) {
			$options = array_combine($versions, $versions);
		} else {
			$options = array();
		}
		return $this->renderSelectField($parameters, $options, $preSelectedVersion);
	}

	/**
	 * @return string
	 */
	protected function getNoneFoundLabel() {
		return LocalizationUtility::translate('tt_content.noneFoundLabel', 'FluidcontentCore');
	}

	/**
	 * @param array $extensionKeys
	 * @return array
	 */
	protected function getVariantsLabel($extensionKeys) {
		$variantNames = array();
		foreach ($extensionKeys as $extensionKey) {
			$name = LocalizationUtility::translate('fluidcontent_core.variantLabel', $extensionKey);
			$variantNames[] = (FALSE == empty($name)) ? $name : $extensionKey;
		}
		return $variantNames;
	}

}
