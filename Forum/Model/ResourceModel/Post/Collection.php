<?php

namespace Magenest\Forum\Model\ResourceModel\Post;

use Magenest\Forum\Model\Post as PostModel;
use Magenest\Forum\Model\ResourceModel\Post as PostResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(PostModel::class, PostResourceModel::class);
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $customer = $this->getTable('customer_entity');
        $this->getSelect()
            ->joinLeft($customer, 'main_table.author_id =' .$customer.'.entity_id',['firstname', 'lastname','email']);
        return $this; // TODO: Change the autogenerated stub
    }
}
