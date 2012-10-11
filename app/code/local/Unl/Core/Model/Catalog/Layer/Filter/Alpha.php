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
                $collection->addAttributeToFilter('name', array(
                    'regexp' => '[[:digit:]]',
                    'field_expr' => 'LEFT(#?, 1)'
                ));
                $filter['label'] = '0-9';
                break;
            case '~':
                $collection->addAttributeToFilter('name', array(
                    'regexp' => '[[:alnum:]]',
                    'field_expr' => 'LEFT(#?, 1) NOT'
                ));
                $filter['label'] = 'Symbols';
                break;
            default:
                $collection->addAttributeToFilter('name', array(
                    'like' => Mage::getResourceHelper('core')->addLikeEscape($this->_appliedLetter, array('position' => 'start'))
                ));
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
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return array
     */
    protected function _getItemCountCollection($collection)
    {
        $collectionHelper = clone $collection;

        $collectionHelper->addExpressionAttributeToSelect('letter', 'UPPER(LEFT({{name}}, 1))', 'name');

        $select = $collectionHelper->getSelect();

        foreach ($select->getPart(Zend_Db_Select::COLUMNS) as $column) {
            if ($column[2] == 'letter') {
                $letterExpr = $column[1];
            }
        }

        if (!isset($letterExpr)) {
            Mage::throwException('Could not locate expression for alpha filtering');
        }

        $select->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::GROUP)
            ->reset(Zend_Db_Select::ORDER)
            ->distinct(false)
            ->limit()
            ->columns(array(
                'letter' => $letterExpr,
                'product_count' => new Zend_Db_Expr('COUNT(DISTINCT e.entity_id)')
            ))
            ->group($letterExpr);

        $letters = $collectionHelper->getConnection()->fetchPairs($select);
        return $letters;
    }
}
