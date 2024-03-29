<?php

namespace Etailors\Forms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Configuration extends AbstractHelper {
	
	public function __construct(
		Context $context
	) {
		parent::__construct($context);
	}
	
	public function isEnabled() {
		return ($this->getSetting('enabled') == 1) ? true : false;
	}
	
	/**
	 * @param string $section
	 * @param string $group
	 * @param string $field
	 * @return string
	 */
	public function getSetting($field, $group = 'general', $section = 'etailors_forms', $forceStore = null) 
	{
		$settingPath = $section . '/' . $group . '/' . $field;

		return $this->scopeConfig->getValue($settingPath, ScopeInterface::SCOPE_STORE);
	}
	
}