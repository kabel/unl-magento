<?php

class Unl_Core_Model_Newsletter_Template  extends Mage_Newsletter_Model_Template
{
    /**
     * Prepare Process (with save)
     *
     * @return Mage_Newsletter_Model_Template
     */
    public function preprocess()
    {
        $this->_preprocessFlag = true;
        $this->setData('dummy_key', true);
        $this->save();
        $this->_preprocessFlag = false;
        return $this;
    }
}
