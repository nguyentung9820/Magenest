<?php

namespace Magenest\Blog\Model\ResourceModel\Blog;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';


    protected function _construct()
    {
        $this->_init('Magenest\Blog\Model\Blog', 'Magenest\Blog\Model\ResourceModel\Blog');
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $userTable = $this->getTable('admin_user');
        $this->addFilterToMap('id', 'main_table.id')
        ->addFilterToMap('firstname','admin_user.firstname')
        ->addFilterToMap('username','admin_user.username');

        $this->getSelect()
            ->joinLeft($userTable,'main_table.author_id = admin_user.user_id',['firstname' => 'admin_user.firstname','username' => 'admin_user.username']);
        return $this; // TODO: Change the autogenerated stub
    }



}
