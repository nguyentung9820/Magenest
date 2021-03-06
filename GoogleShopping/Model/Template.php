<?php
namespace Magenest\GoogleShopping\Model;

use Magenest\GoogleShopping\Model\ResourceModel\Template as TemplateResourceModel;
use Magento\Catalog\Model\CategoryFactory as CatalogCategoryFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Template
 * @package Magenest\GoogleShopping\Model
 */
class Template extends AbstractModel
{
    const PRODUCT_TEMPLATE = 0;
    const CATEGORY_TEMPLATE = 1;

    protected $_eventPrefix = 'google_feed_template';

    protected $mapping = null;

    /** @var TemplateResourceModel  */
    protected $_templateResource;

    /** @var Json  */
    protected $_jsonFramework;

    /** @var CatalogCategoryFactory  */
    protected $categoryFactory;

    /**
     * Template constructor.
     * @param TemplateResourceModel $templateResource
     * @param Json $jsonFramework
     * @param CatalogCategoryFactory $categoryFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        TemplateResourceModel $templateResource,
        Json $jsonFramework,
        CatalogCategoryFactory $categoryFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_templateResource = $templateResource;
        $this->_jsonFramework = $jsonFramework;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init('Magenest\GoogleShopping\Model\ResourceModel\Template');
    }

    public function getMapping()
    {
        if ($this->mapping == null) {
            $this->buildMapping();
        }
        return $this->mapping;
    }

    /**
     * Build mapping
     *
     * @param int $parentId
     * @return void
     */
    protected function buildMapping($parentId = 0)
    {
        $dataMapping = [];
        if ($this->getData('content_template')) {
            $dataMapping = $this->_jsonFramework->unserialize($this->getData('content_template'));
        }
        $collection = $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('children_count')
            ->addAttributeToSelect('level')
            ->addAttributeToFilter('parent_id', $parentId)
            ->setOrder('position', 'asc')->load();

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($collection as $category) {
            $categoryId = $category->getId();
            $map = '';
            $map = isset($dataMapping[$categoryId]) ? $dataMapping[$categoryId] : '';

            if ($category->getName() || $category->getLevel() == 0) {
                $this->mapping[] = [
                    'category_id' => $categoryId,
                    'name' => $category->getName() ? $category->getName() : __('Default Category')->__toString(),
                    'map' => $map,
                    'level' => $category->getLevel(),
                    'path' => $category->getPath(),
                    'parent_id' => $category->getParentId(),
                    'has_childs' => $category->getChildrenCount() ? true : false,
                ];
            }

            if ($category->getChildrenCount()) {
                $this->buildMapping($category->getId());
            }
        }
    }

    public function handleDataMapping($dataMapping)
    {
        $results = [];
        if (is_array($dataMapping) && !empty($dataMapping)) {
            foreach ($dataMapping as $data) {
                $results[$data["category"]] = $data["mapp"];
            }
        }
        return $results;
    }

    /**
     * @return Template
     * @throws \Exception
     */
//    public function afterSave()
//    {
//        $this->saveMappingAttributes($this);
//        return parent::afterSave(); // TODO: Change the autogenerated stub
//    }

    /**
     * @param Template $templateModel
     * @throws \Exception
     */
    private function saveMappingAttributes(\Magenest\GoogleShopping\Model\Template $templateModel)
    {
        try {
            $templateId = $templateModel->getId();
            $templateContent = $templateModel->getContentTemplate();
            $attributesMapped = $this->_templateResource->getAllMagentoMappedFields($templateId);
            $attributesNew = [];
            if ($templateContent != '') {
                $templateContent = $this->_jsonFramework->unserialize($templateContent);
                foreach ($templateContent as $content) {
                    $content['template_id'] = $templateModel->getId();
                    $this->_templateResource->saveTemplateContent($content);
                    $attributesNew[] = $content['magento_attribute'];
                }
            }
            $diffArray = array_diff($attributesMapped, $attributesNew);
            $this->_templateResource->deleteTemplateContent($templateId, $diffArray);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
