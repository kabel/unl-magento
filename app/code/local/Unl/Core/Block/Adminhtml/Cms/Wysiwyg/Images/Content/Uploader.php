<?php

/**
 * Overriden Uploader block for Wysiwyg Images
 *
 * Implemetation copied from
 * @see Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Uploader
 */
class Unl_Core_Block_Adminhtml_Cms_Wysiwyg_Images_Content_Uploader
    extends Unl_Core_Block_Adminhtml_Media_Uploader
{
    public function __construct()
    {
        parent::__construct();
        $type = $this->_getMediaType();
        $allowed = Mage::getSingleton('cms/wysiwyg_images_storage')->getAllowedExtensions($type);
        $labels = array();
        $files = array();
        foreach ($allowed as $ext) {
            $labels[] = '.' . $ext;
            $files[] = $ext;
        }
        $this->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')
                ->addSessionParam()
                ->setQueryParam('ajax', '1')
                ->getUrl('*/*/upload', array('type' => $type))
            )
            ->setFileDataName('image')
            ->setFilters(array(
                array(
                    'title' => $this->helper('cms')->__('Images (%s)', implode(', ', $labels)),
                    'extensions' => implode(',', $files)
                )
            ))
            ->setDropElement('content');
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
