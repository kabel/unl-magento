<?php

class Unl_Core_Model_Cms_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    /**
     * Return files
     *
     * @param string $path Parent directory path
     * @param string $type Type of storage, e.g. image, media etc.
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFilesCollection($path, $type = null)
    {
        $collection = $this->getCollection($path)
            ->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(false)
            ->setOrder('mtime', Varien_Data_Collection::SORT_ORDER_ASC);

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }

        $helper = $this->getHelper();

        // prepare items
        foreach ($collection as $item) {
            $item->setId($helper->idEncode($item->getBasename()));
            $item->setName($item->getBasename());
            $item->setShortName($helper->getShortFilename($item->getBasename()));
            $item->setUrl($helper->getCurrentUrl() . $item->getBasename());

            if ($this->isImage($item->getBasename())) {
                $thumbUrl = $this->getThumbnailUrl($item->getFilename(), true);
                // generate thumbnail "on the fly" if it does not exists
                if(! $thumbUrl) {
                    $thumbUrl = Mage::getSingleton('adminhtml/url')->getUrl('*/*/thumbnail', array('file' => $item->getId()));
                }

                $size = @getimagesize($item->getFilename());

                if (is_array($size)) {
                    $item->setWidth($size[0]);
                    $item->setHeight($size[1]);
                }
            } else {
                $thumbUrl = Mage::getDesign()->getSkinUrl(self::THUMB_PLACEHOLDER_PATH_SUFFIX);
            }

            $item->setThumbUrl($thumbUrl);
        }

        return $collection;
    }
}