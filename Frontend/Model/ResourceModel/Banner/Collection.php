<?php

namespace Magenest\Frontend\Model\ResourceModel\Banner;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';


    protected function _construct()
    {
        $this->_init('Magenest\Frontend\Model\Banner', 'Magenest\Frontend\Model\ResourceModel\Banner');
    }


}
