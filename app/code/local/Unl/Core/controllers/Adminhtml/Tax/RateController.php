<?php

require_once 'Mage/Adminhtml/controllers/Tax/RateController.php';

class Unl_Core_Adminhtml_Tax_RateController extends Mage_Adminhtml_Tax_RateController
{
    protected $_prevMaintenanceState;

    protected function _toggleMaintenance($flag = true)
    {
        global $maintenanceFile;
        if (empty($maintenanceFile)) {
            return $this;
        }

        if ($flag) {
            if (is_null($this->_prevMaintenanceState)) {
                $this->_prevMaintenanceState = file_exists($maintenanceFile);
            }

            if (!file_exists($maintenanceFile)) {
                touch($maintenanceFile);
            }
        } else if (!$this->_prevMaintenanceState && file_exists($maintenanceFile)) {
            unlink($maintenanceFile);
        }

        return $this;
    }

    public function boundaryImportPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_boundary_file']['tmp_name'])) {
            try {
                $this->_importBoundaries();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('The tax boundaries have been imported.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Invalid file upload attempt'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/importExport');
    }

    public function fullImportPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_rates_file']['tmp_name'])) {
            try {
                $this->_fullRateImport();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('The tax rates have been imported.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Invalid file upload attempt'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/importExport');
    }

    protected function _fullRateImport()
    {
        $fileName = $tmpName = $_FILES['import_rates_file']['tmp_name'];
        $pathinfo = pathinfo($_FILES['import_rates_file']['name']);

        if ($pathinfo['extension'] == 'zip') {
            $fileName = 'zip://' . $fileName . '#rates.csv';
        } else if ($pathinfo['extension'] == 'gz') {
            $fileName = 'compress.zlib://' . $fileName;
        }

        $fh = @fopen($fileName, 'r');
        if (!$fh) {
            throw new Exception('Failed to open stream');
        }

        /* @var $resource Unl_Core_Model_Resource_Tax_Boundary */
        $resource = Mage::getResourceModel('unl_core/tax_boundary');
        $resource->beginRateImport();
        $dbConfig = Mage::getConfig()->getResourceConnectionConfig('core_setup')->asArray();
        $isMysql = strpos($dbConfig['type'], 'mysql') !== false;
        $columns = $resource->getTaxRateColumns();
        $i = 0;
        $data = array();

        while ($rowData = fgetcsv($fh)) {
            if (empty($rowData) || empty($rowData[0])) {
                continue;
            }

            if (count($rowData) != count($columns)) {
                if ($isMysql) {
                    throw new Exception('Invalid column count in rate import');
                } else {
                    continue;
                }
            }

            if ($isMysql) {
                if ($fileName != $tmpName) {
                    $tmpName = tempnam(sys_get_temp_dir(), 'unltaxrate_');
                    copy($fileName, $tmpName);
                }
                chmod($tmpName, 0644);

                $resource->loadLocalRateFile($tmpName);

                if ($fileName != $tmpName) {
                    unlink($tmpName);
                }

                break;
            } else {
                if ($i < 10000) {
                    $data[] = $rowData;
                    $i++;
                } else {
                    $resource->insertTaxRates($data);
                    $i = 0;
                    $data = array();
                }
            }
        }

        fclose($fh);

        $resource->rebuildTaxCalculation();
    }

    protected function _importBoundaries()
    {
        $fileName = $tmpName = $_FILES['import_boundary_file']['tmp_name'];
        $pathinfo = pathinfo($_FILES['import_boundary_file']['name']);

        if ($pathinfo['extension'] == 'zip') {
            $fileName = 'zip://' . $fileName . '#NEB.txt';
        } else if ($pathinfo['extension'] == 'gz') {
            $fileName = 'compress.zlib://' . $fileName;
        }

        $fh = @fopen($fileName, 'r');
        if (!$fh) {
            throw new Exception('Failed to open stream');
        }

        try {
            $this->_toggleMaintenance();
            /* @var $resource Unl_Core_Model_Resource_Tax_Boundary */
            $resource = Mage::getResourceModel('unl_core/tax_boundary');
            $resource->beginImport();
            $dbConfig = Mage::getConfig()->getResourceConnectionConfig('core_setup')->asArray();
            $isMysql = strpos($dbConfig['type'], 'mysql') !== false;
            $columns = $resource->getInsertColumns();
            $i = 0;
            $data = array();

            while ($rowData = fgetcsv($fh)) {
                if (empty($rowData) || empty($rowData[0])) {
                    continue;
                }

                $rowData = array_slice($rowData, 0, count($columns));

                if (count($rowData) != count($columns)) {
                    if ($isMysql) {
                        throw new Exception('Invalid column count in boundary import');
                    } else {
                        continue;
                    }
                }

                if ($isMysql) {
                    if ($fileName != $tmpName) {
                        $tmpName = tempnam(sys_get_temp_dir(), 'unltaxboundary_');
                        copy($fileName, $tmpName);
                    }
                    chmod($tmpName, 0644);

                    $resource->loadLocalFile($tmpName);

                    if ($fileName != $tmpName) {
                        unlink($tmpName);
                    }

                    break;
                } else {
                    if ($i < 1000) {
                        $data[] = $rowData;
                        $i++;
                    } else {
                        $resource->insertArray($data);
                        $i = 0;
                        $data = array();
                    }
                }
            }

            fclose($fh);
        } catch (Exception $e) {
            $this->_toggleMaintenance(false);
            throw $e;
        }
    }
}
