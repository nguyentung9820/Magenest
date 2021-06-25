<?php
namespace Magenest\Movie\Block;
use Magento\Framework\View\Element\Template;
class Movie extends Template
{   
    private $_resultCollection;
    private $_productCollectionFactory;
    private $_diretorCollectionFactory;
    public function __construct(
        Template\Context $context,
        \Magenest\Movie\Model\ResourceModel\Movie\CollectionFactory $productCollectionFactory,
        \Magenest\Movie\Model\ResourceModel\Movie\Collection $resultCollection,
        \Magenest\Movie\Model\ResourceModel\Director\CollectionFactory $directorCollectionFactor,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resultCollection = $resultCollection;
        $this->_diretorCollectionFactory = $directorCollectionFactor;
    }
    public function getMovies() {
        // $collection = $this->_diretorCollectionFactory->create()
        // ->addFieldToSelect('name', 'director');
        $collection = $this->_productCollectionFactory->create()
        ->addFieldToSelect('name', 'movie')
        ->addFieldToSelect('description')
        ->addFieldToSelect('rating');
        $collection->joinTable();
        return $collection;
        }
    public function countRow(){
        $count= $this->_resultCollection->countRowInTable();
        return $count;
    }
}