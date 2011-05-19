<?php

class Unl_Core_Model_Cms_Wysiwyg_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage
{
    /* Overrides
     * @see Mage_Cms_Model_Wysiwyg_Images_Storage::getDirsCollection()
     * by checking for DB use before trying to use it
     */
    public function getDirsCollection($path)
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $subDirectories = Mage::getModel('core/file_storage_directory_database')->getSubdirectories($path);

            foreach ($subDirectories as $directory) {
                $fullPath = rtrim($path, DS) . DS . $directory['name'];
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0777, true);
                }
            }
        }

        $conditions = array('reg_exp' => array(), 'plain' => array());

        foreach ($this->getConfig()->dirs->exclude->children() as $dir) {
            $conditions[$dir->getAttribute('regexp') ? 'reg_exp' : 'plain'][(string) $dir] = true;
        }
        // "include" section takes precedence and can revoke directory exclusion
        foreach ($this->getConfig()->dirs->include->children() as $dir) {
            unset($conditions['regexp'][(string) $dir], $conditions['plain'][(string) $dir]);
        }

        $regExp = $conditions['reg_exp'] ? ('~' . implode('|', array_keys($conditions['reg_exp'])) . '~i') : null;
        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setCollectFiles(false)
            ->setCollectRecursively(false);
        $storageRootLength = strlen($this->getHelper()->getStorageRoot());

        foreach ($collection as $key => $value) {
            $rootChildParts = explode(DIRECTORY_SEPARATOR, substr($value->getFilename(), $storageRootLength));

            if (array_key_exists($rootChildParts[0], $conditions['plain'])
                || ($regExp && preg_match($regExp, $value->getFilename()))) {
                $collection->removeItemByKey($key);
            }
        }

        return $collection;
    }

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

    public function deleteDirectory($path)
    {
        // prevent accidental root directory deleting
        $rootCmp = rtrim($this->getHelper()->getStorageRoot(), DS);
        $pathCmp = rtrim($path, DS);

        if ($rootCmp == $pathCmp) {
            Mage::throwException(Mage::helper('cms')->__('Cannot delete root directory %s.', $path));
        }

        $io = new Varien_Io_File();

        Mage::helper('core/file_storage_database')->deleteFolder($path);
        if (!$io->rmdir($path, true)) {
            Mage::throwException(Mage::helper('cms')->__('Cannot delete directory %s.', $path));
        }

        if (strpos($pathCmp, $rootCmp) === 0) {
            $io->rmdir($this->getThumbnailRoot() . DS . ltrim(substr($pathCmp, strlen($rootCmp)), '\\/'), true);
        }
    }
}
