<?php

class Unl_Core_Model_Core_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * Apply declared configuration for design
     * (fixes Mage's problem when emulating the locale before setting design)
     *
     * @return Unl_Core_Model_Core_Email_Template
     */
    protected function _applyDesignConfig()
    {
        if ($this->getDesignConfig()) {
            $design = Mage::getDesign();
            $this->getDesignConfig()
                ->setOldArea($design->getArea())
                ->setOldStore($design->getStore());

            if ($this->getDesignConfig()->getArea()) {
                Mage::getDesign()->setArea($this->getDesignConfig()->getArea());
            }

            if ($this->getDesignConfig()->getStore()) {
                $design->setStore($this->getDesignConfig()->getStore());
                $design->setTheme('');
                $design->setPackageName('');
                Mage::app()->getLocale()->emulate($this->getDesignConfig()->getStore());
            }

        }
        return $this;
    }
}