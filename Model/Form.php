<?php

namespace Etailors\Forms\Model;

use Magento\Framework\Model\AbstractModel;

class Form extends AbstractModel 
{
	/**
	 * @var \Etailors\Forms\Model\ResourceModel\Form\PageFactory
	 */
	protected $pageFactory;
	
	/**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param \Etailors\Forms\Model\ResourceModel\Form\PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Etailors\Forms\Model\ResourceModel\Form $resource = null,
        \Etailors\Forms\Model\ResourceModel\Form\Collection $resourceCollection = null,
		\Etailors\Forms\Model\Form\PageFactory $pageFactory,
        array $data = []
    ) {
		$this->pageFactory = $pageFactory;
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}
	
	protected function _construct() {
		$this->_init(\Etailors\Forms\Model\ResourceModel\Form::class);

	}
	
	public function getPagesCount() 
	{
		return ($this->getTreatPagesAsSections() == 1) ? 1 : $this->getPages()->count();
	}
	
	public function getPages() 
	{
		// Get the pages for current form in sort order
		return $this->pageFactory->create()->getCollection()
			->addFieldToFilter('form_id', $this->getId())
			->setOrder('sort_order', 'ASC');
	}
	
	public function getFields() 
	{
		$fields = [];
		foreach ($this->getPages() as $page) {
			foreach ($page->getFields() as $field) {
				$fields[] = $field;
			}
		}

		return $fields;
	}
	
	public function getPageByNum($num) 
	{
		return $this->pageFactory->create()->getCollection()
			->addFieldToFilter('form_id', $this->getId())
			->setOrder('sort_order', 'ASC')
			->setPageSize(1)
			->setCurPage($num)
			->getFirstItem();
	}	
}