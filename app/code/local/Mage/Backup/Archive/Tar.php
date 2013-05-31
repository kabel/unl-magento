<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extended version of Mage_Archive_Tar that supports filtering
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Archive_Tar extends Mage_Archive_Tar
{
    /**
     * Filenames or filename parts that are used for filtering files
     *
     * @var array()
     */
    protected $_skipFiles = array();

    protected $_tarCmd;

    protected function _initWriter()
    {
        $tar = 'tar ';
        if (strpos(PHP_OS, 'Darwin') === 0) {
            $tar = 'gnutar ';
        }

        $this->_tarCmd = $tar . '-rf ' . escapeshellarg($this->_destinationFilePath)
            . ' -C ' . escapeshellarg($this->_getCurrentPath()) . ' --no-recursion ';

        return $this;
    }

    /**
     * Filters files using Mage_Backup_Filesystem_Iterator_Filter and
     * passes to tar command.
     *
     * @param bool $skipRoot
     * @param bool $finalize
     */
    protected function _createTar($skipRoot = false, $finalize = false)
    {
        $path = $this->_getCurrentFile();

        $iterator = new RecursiveIteratorIterator(
            new Mage_Backup_Filesystem_Iterator_Filter(new RecursiveDirectoryIterator($path), $this->_skipFiles),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $this->_setCurrentFile($item->getPathname());
            $this->_packAndWriteCurrentFile();
        }
    }

    protected function _packAndWriteCurrentFile()
    {
        $currentFile = $this->_getCurrentFile();
        $path = $this->_getCurrentPath();
        $nameFile = str_replace($path, '', $currentFile);

        exec($this->_tarCmd . escapeshellarg($nameFile));
    }

    /**
     * Set files that shouldn't be added to tarball
     *
     * @param array $skipFiles
     * @return Mage_Backup_Archive_Tar
     */
    public function setSkipFiles(array $skipFiles)
    {
        $this->_skipFiles = $skipFiles;
        return $this;
    }
}
