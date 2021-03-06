<?php
namespace Magenest\NotificationBox\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class NotificationDetail extends Action
{
    /** @var PageFactory  */
    protected $resultPageFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Create page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Notification Detail'));
        return $resultPage;
    }
}
