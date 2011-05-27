<?php

class Unl_Core_Model_Catalog_Layer_Filter_Alpha extends Mage_Catalog_Model_Layer_Filter_Abstract
{
    protected $_appliedLetter;

    public function _construct()
    {
        parent::_construct();
        $this->_requestVar = 'let';
    }

    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$this->_isEnabled() || !$filter || !preg_match('/^[a-z1~]$/i', $filter)) {
            return $this;
        }
        $this->_appliedLetter = $filter;
        $filter = array(
            'value' => $filter
        );
        $collection = $this->getLayer()->getProductCollection();

        switch ($this->_appliedLetter) {
            case '1':
                $collection->addAttributeToSelect('name', 'inner');
                $collection->getSelect()->where('LEFT(IF(_table_name.value_id > 0, _table_name.value, _table_name_default.value), 1) REGEXP ?', '[[:digit:]]');
                $filter['label'] = '0-9';
                break;
            case '~':
                $collection->addAttributeToSelect('name', 'inner');
                $collection->getSelect()->where('LEFT(IF(_table_name.value_id > 0, _table_name.value, _table_name_default.value), 1) NOT REGEXP ?', '[[:alnum:]]');
                $filter['label'] = 'Symbols';
                break;
            default:
                $collection->addAttributeToFilter('name', array('like' => $this->_appliedLetter . '%'));
                $filter['label'] = strtoupper($this->_appliedLetter);
                break;
        }

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($filter['label'], $filter['value'])
        );

        return $this;
    }

    public function getName()
    {
        return Mage::helper('unl_core')->__('Alphabetic Listing');
    }

	/**
     * Get data array for building alpha filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $key = $this->getLayer()->getStateKey().'_ALPHALIST';
        $data = $this->getLayer()->getAggregator()->getCacheData($key);

        if ($data === null) {
            $data = array();

            if ($this->_isEnabled() && empty($this->_appliedLetter)) {
                $counts = array();
                $letterCount = $this->_getItemCountCollection($this->getLayer()->getProductCollection());
                foreach ($letterCount as $letter => $count) {
                    if (preg_match('/\d/', $letter)) {
                        if (!isset($counts['1'])) {
                            $counts['1'] = 0;
                        }
                        $counts['1'] += $count;
                    } elseif (!preg_match('/[a-z]/i', $letter)) {
                        if (!isset($counts['~'])) {
                            $counts['~'] = 0;
                        }
                        $counts['~'] += $count;
                    } else {
                        $counts[$letter] = $count;
                    }
                }

                if (isset($counts['~'])) {
                    $data[] = array(
                        'label' => 'Symbols',
                        'value' => '~',
                        'count' => $counts['~'],
                    );
                }

                if (isset($counts['1'])) {
                    $data[] = array(
                        'label' => '0-9',
                        'value' => '1',
                        'count' => $counts['1'],
                    );
                }

                foreach (range('A', 'Z') as $letter) {
                    $data[] = array(
                        'label' => $letter,
                        'value' => $letter,
                        'count' => isset($counts[$letter]) ? $counts[$letter] : 0,
                    );
                }
            }

            $tags = $this->getLayer()->getStateTags();
            $this->getLayer()->getAggregator()->saveCacheData($data, $key, $tags);
        }
        return $data;
    }

    /**
     * Uses the current category's data to check filter availability
     *
     * @return boolean
     */
    protected function _isEnabled()
    {
        $category = $this->getLayer()->getCurrentCategory();
        return (bool)$category->getIsAlphaList();
    }

    /**
     * Gets the count for each starting letter of product name
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return array
     */
    protected function _getItemCountCollection($collection)
    {
        $collection->addAttributeToSelect('name', 'left');
        $select = clone $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::GROUP)
            ->reset(Zend_Db_Select::ORDER)
            ->distinct(false)
            ->columns(array(
                'letter' => new Zend_Db_Expr('UPPER(LEFT(IF(_table_name.value_id > 0, _table_name.value, _table_name_default.value), 1))'),
                'product_count' => new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)')
            ))
            ->group('UPPER(LEFT(IF(_table_name.value_id > 0, _table_name.value, _table_name_default.value), 1))');

        $letters = $collection->getConnection()->fetchPairs($select);
        return $letters;
    }
}
