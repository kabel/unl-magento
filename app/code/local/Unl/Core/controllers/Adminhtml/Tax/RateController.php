<?php

require_once 'Mage/Adminhtml/controllers/Tax/RateController.php';

class Unl_Core_Adminhtml_Tax_RateController extends Mage_Adminhtml_Tax_RateController
{
    public function boundaryImportPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_boundary_file']['tmp_name'])) {
            try {
                $this->_import('boundaries');

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
                $this->_import('rates');

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

    protected function _import($type)
    {
        switch ($type) {
            case 'rates':
                $fileOffset = 'import_rates_file';
                $zipOffset  = '#rates.csv';
                break;
            case 'boundaries':
                $fileOffset = 'import_boundary_file';
                $zipOffset  = '#NEB.txt';
                break;
            default:
                return $this;
        }

        $fileName = $tmpName = $_FILES[$fileOffset]['tmp_name'];
        $pathinfo = pathinfo($_FILES[$fileOffset]['name']);

        if ($pathinfo['extension'] == 'zip') {
            $fileName = 'zip://' . $fileName . $zipOffset;
        } else if ($pathinfo['extension'] == 'gz') {
            $fileName = 'compress.zlib://' . $fileName;
        }

        $fh = @fopen($fileName, 'r');
        if (!$fh) {
            throw new Exception('Failed to open stream');
        }

        if ($this->getRequest()->getParam('maintenance_mode')) {
            $maint = Mage::helper('backup')->turnOnMaintenanceMode();
            if (!$maint) {
                Mage::throwException(Mage::helper('backup')->__('You do not have sufficient permissions to enable Maintenance Mode during this operation.')
                    . ' ' . Mage::helper('backup')->__('Please either unselect the "Put store on the maintenance mode" checkbox or update your permissions to proceed with the backup."'));
            }
        }

        try {
            /* @var $resource Unl_Core_Model_Resource_Tax_Boundary */
            $resource = Mage::getResourceModel('unl_core/tax_boundary');
            $canLoad = $resource->supportsLoadFile();
            $i = 0;
            $data = array();
            if ($type == 'rates') {
                $resource->beginRateImport();
                $columns = $resource->getTaxRateColumns();
                $tmpPrefix = 'unltaxrate_';
                $rowLimit = 10000;
            } else {
                $resource->beginImport();
                $columns = $resource->getInsertColumns();
                $tmpPrefix = 'unltaxboundary_';
                $rowLimit = 1000;
            }

            while ($rowData = fgetcsv($fh)) {
                if (empty($rowData) || empty($rowData[0])) {
                    continue;
                }

                $rowData = array_slice($rowData, 0, count($columns));

                if (count($rowData) != count($columns)) {
                    if ($canLoad) {
                        throw new Exception('Invalid column count in import');
                    } else {
                        continue;
                    }
                }

                if ($canLoad) {
                    if ($fileName != $tmpName) {
                        $tmpName = tempnam(sys_get_temp_dir(), $tmpPrefix);
                        copy($fileName, $tmpName);
                    }
                    chmod($tmpName, 0644);

                    if ($type == 'rates') {
                        $resource->loadLocalRateFile($tmpName);
                    } else {
                        $resource->loadLocalFile($tmpName);
                    }

                    if ($fileName != $tmpName) {
                        unlink($tmpName);
                    }

                    break;
                } else {
                    if ($i < $rowLimit) {
                        $data[] = $rowData;
                        $i++;
                    } else {
                        if ($type == 'rates') {
                            $resource->insertTaxRates($data);
                        } else {
                            $resource->insertArray($data);
                        }
                        $i = 0;
                        $data = array();
                    }
                }
            }

            fclose($fh);

            if ($type == 'rates') {
                $resource->rebuildTaxCalculation();
            }
        } catch (Exception $e) {
            $this->_endMaintenance();
            throw $e;
        }

        $this->_endMaintenance();
    }

    protected function _endMaintenance()
    {
        if ($this->getRequest()->getParam('maintenance_mode')) {
            Mage::helper('backup')->turnOffMaintenanceMode();
        }

        return $this;
    }
}
