<?php

namespace Etailors\Forms\Model\Config\Data;

use Magento\Framework\Config\Data as DataConfig;

class Autofill extends DataConfig
{
	public function toOptionArray($addEmptyValue = true) 
	{
		$autofillClasses = $this->get('autofill_classes');
		$optionArray = [];
		
		if($addEmptyValue) {
			$optionArray[] = [
				'label' => 'None',
				'value' => 0
			];
		}
		
		foreach ($autofillClasses as $autofillClass) {
			$optionArray[] = [
				'label' => $autofillClass['name'],
				'value' => $autofillClass['id']
			];
		}
		
		return $optionArray;
	}
	
	public function hasOption($option) 
	{
		$autofillClasses = $this->get('autofill_classes');
		foreach ($autofillClasses as $autofillClass) {
			if ($autofillClass['id'] == $option) {
				return true;
			}
		}
		return false;
	}
	
	public function getOptionDetails($option) 
	{
		$autofillClasses = $this->get('autofill_classes');
		foreach ($autofillClasses as $autofillClass) {
			if ($autofillClass['id'] == $option) {
				return $autofillClass;
			}
		}
		return false;
	}
}