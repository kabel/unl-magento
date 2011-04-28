<?php

class Unl_Core_Block_Adminhtml_Widget_Grid_Multicontainer extends Mage_Adminhtml_Block_Widget_Container
{
    protected $_grids = array();

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('report/grid/multicontainer.phtml');
    }

    protected function _prepareLayout()
    {
        foreach ($this->_grids as $id => $data) {
            $childId = $this->_prepareGridBlockId($id);
            $this->_addGridChildBlock($childId, $data['block_type']);
        }
        return parent::_prepareLayout();
    }

    public function addGrid($id, $block, $data = array())
    {
        $this->_grids[$id] = $data;
        $this->_grids[$id]['block_type'] = $block;

        return $this;
    }

    public function removeGrid($id)
    {
        if (isset($this->_grids[$id])) {
            unset($this->_grids[$id]);
        }

        return $this;
    }

    protected function _prepareGridBlockId($id)
    {
        return $id . '_grid';
    }

    protected function _addGridChildBlock($childId, $blockType)
    {
        $block = $this->getLayout()->createBlock($blockType);
        $block->setTemplate('widget/grid/multigrid.phtml');
        $this->setChild($childId, $block);
        return $block;
    }

    public function getGridsHtml()
    {
        $out = '';

        foreach ($this->_grids as $id => $data) {
            $childId = $this->_prepareGridBlockId($id);
            $child = $this->getChild($childId);

            if (!$child) {
                $child = $this->_addGridChildBlock($childId, $data['block_type']);
            }
            unset($data['block_type']);

            $child->addData($data);

            foreach (array('filter_data', 'period_type') as $key) {
                if ($this->hasData($key)) {
                    $child->setData($key, $this->getData($key));
                }
            }

            $out .= $this->getChildHtml($childId);
        }

        return $out;
    }

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
