<?php

class Unl_Core_Model_Cms_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    const THUMBS_EXTERNAL_DIR = '.external';

    /* Overrides
     * @see Mage_Cms_Model_Wysiwyg_Images_Storage::getFilesCollection()
     * by using the design path for the default thumbnail
     */
    public function getFilesCollection($path, $type = null)
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $files = Mage::getModel('core/file_storage_database')->getDirectoryFiles($path);

            $fileStorageModel = Mage::getModel('core/file_storage_file');
            foreach ($files as $file) {
                $fileStorageModel->saveFile($file);
            }
        }

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

    public function getThumbsPath($filePath = false)
    {
        if ($filePath && $path = $this->getThumbnailPath($filePath)) {
            return dirname($path);
        }

        return $this->getThumbnailRoot() . DS . self::THUMBS_EXTERNAL_DIR;
    }
}
