<?php

namespace Magenest\CouponCode\Model\ResourceModel\Coupon;

use Magenest\CouponCode\Model\Coupon as CouponModel;
use Magenest\CouponCode\Model\ResourceModel\Coupon as CouponResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init collection
     */
    protected function _construct()
    {
        $this->_init(CouponModel::class, CouponResourceModel::class);
    }

    /**
     * Join with salesrule table and salesrule_customer_group table
     *
     * @return Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $salesRuleCustomerGroup = $this->getTable('salesrule_customer_group');
        $salesRuleTable = $this->getTable('salesrule');
        $this->addFieldToSelect('code')
            ->addFieldToSelect('usage_limit')
            ->addFieldToSelect('coupon_id')
            ->addFieldToSelect('usage_per_customer');
        $this->getSelect()
            ->group('main_table.rule_id')
            ->joinLeft($salesRuleTable, 'main_table.rule_id =' .$salesRuleTable.'.rule_id')
            ->joinLeft($salesRuleCustomerGroup, 'main_table.rule_id =' .$salesRuleCustomerGroup.'.rule_id');
        return $this; // TODO: Change the autogenerated stub
    }
}
