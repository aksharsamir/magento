<?php
 
namespace Etailors\Forms\Controller\Adminhtml\Page;
 
class DeleteAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    protected $resultRedirectFactory;
 
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }
	
	/**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Etailors_Forms::Forms_Config');
    }
 
    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
		$params = $this->getRequest()->getParams();
		$id = $this->getRequest()->getParam('id');
        /** @var \Magebuzz\Staff\Model\Grid $model */
        $model = $this->_objectManager->create('Etailors\Forms\Model\Form\Page');
		
		/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		
        if ($id) {
            $model->load($id);
			$formId = $model->getFormId();
            if (!$model->getId()) {
                $this->messageManager->addError(__('This page no longer exists.')); 
                return $resultRedirect->setPath('*/editor/edit', ['id' => $formId]);
            }
			
			try {
				
				$model->delete();
				$this->messageManager->addSuccess(__('The page has been deleted.'));
				return $resultRedirect->setPath('*/editor/edit', ['id' => $formId]);
			}
			catch (\Exception $e) {
				$this->messageManager->addError(__('Something went wrong trying to delete the page.'));
				return $resultRedirect->setPath('*/editor/edit', ['id' => $formId]);
			}
        }
		
        $this->messageManager->addError(__('Something went wrong trying to delete the page.'));
        
		return $resultRedirect->setPath('*/editor/index');		
    }
}