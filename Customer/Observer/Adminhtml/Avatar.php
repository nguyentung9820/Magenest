<?php
namespace Magenest\Customer\Observer\Adminhtml;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Eav\Model\Config;

class Avatar implements ObserverInterface
{
    protected $eavConfig;
    protected $request;
    protected $customerFactory;
    protected $getmessenger;
    protected $attributeResource;
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource,
        Config $eavConfig,
        CustomerFactory $customerFactory,
        \Magento\Framework\App\RequestInterface $request,
        Context $context
    )
    {
        $this->attributeResource = $attributeResource;
        $this->eavConfig = $eavConfig;
        $this->getmessenger = $context->getMessageManager();
        $this->customerFactory = $customerFactory;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $req = $this->request->getParams();
        $data = $observer->getData('customer');


    }


}
