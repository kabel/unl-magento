<?php

class Unl_Core_Model_Backup_Db extends Mage_Backup_Model_Db
{
    const XML_PATH_MYSQLDUMP_BIN = 'system/backup/backup_bin';

    protected $_processedTables = array();
    protected $_processingStack = array();
    protected $_ignoreDataTables;

    public function __construct()
    {
        $this->_ignoreDataTablesList[] = 'tax/tax_calculation';
        $this->_ignoreDataTablesList[] = 'tax/tax_calculation_rate';
        $this->_ignoreDataTablesList[] = 'unl_core/tax_boundary';
    }

    public function createBackup(Mage_Backup_Model_Backup $backup)
    {
        $dumpCmd = Mage::getStoreConfig(self::XML_PATH_MYSQLDUMP_BIN);
        if (empty($dumpCmd)) {
            $dumpCmd = 'mysqldump';
        }

        $dbConfig = $this->getResource()->getDbConfig();
        if (!empty($dbConfig['unix_socket'])) {
            $dumpCmd .= ' -S ' . $dbConfig['unix_socket'];
        } elseif (!empty($dbConfig['host'])) {
            $dumpCmd .= ' -h ' . $dbConfig['host'];
        }

        $dumpCmd .= ' -u ' . $dbConfig['username'];
        $dumpCmd .= ' -p'  . $dbConfig['password'];

        $options = array('--single-transaction');

        $noDataCmd = $dumpCmd . ' --no-data ' . $dbConfig['dbname'];
        foreach ($this->getIgnoreDataTablesList() as $table) {
            $noDataCmd .= ' ' . $table;
            $options[] = '--ignore-table=' . $dbConfig['dbname'] . '.' . $table;
        }

        $tmpErrorLog = Mage::getBaseDir('var') . DS . 'log' . DS . 'tmpBackup.log';
        $errorRedir = " 2>>{$tmpErrorLog}";

        exec($noDataCmd . " {$errorRedir} | gzip -9 -c > " . $backup->getPath() . DS . $backup->getFileName());
        exec($dumpCmd . ' ' . implode(' ', $options) . ' ' . $dbConfig['dbname'] . " {$errorRedir} | gzip -9 -c >> "
            . $backup->getPath() . DS . $backup->getFileName());

        if (file_exists($tmpErrorLog)) {
            $errors = file_get_contents($tmpErrorLog);
            unlink($tmpErrorLog);
            if ($errors) {
                if ($backup->exists()) {
                    $backup->deleteFile();
                }

                throw new Exception('Failed during backup command.' . "\n" . $errors);
            }
        }

        return $this;

        /*
        $backup->open(true);

        $this->getResource()->turnOnSerializableMode();

        $tables = $this->getResource()->getTables();
        $this->getResource()->loadForeignKeysInfo($tables);

        $backup->write($this->getResource()->getHeader());

        foreach ($tables as $table) {
            $this->_processTable($backup, $table, true);
        }
        $backup->write($this->getResource()->getFooter());

        $this->getResource()->turnOnRepeatableReadMode();

        $backup->close();

        return $this;
        */
    }

    protected function _processTable(Mage_Backup_Model_Backup $backup, $table, $asTransaction = false)
    {
        if (in_array($table, $this->_processedTables) || in_array($table, $this->_processingStack)) {
            return $this;
        }

        if ($asTransaction) {
            $this->getResource()->beginTransaction();
        }

        array_push($this->_processingStack, $table);

        $dependencyTables = $this->getResource()->getTableReferences($table);
        foreach ($dependencyTables as $depTable) {
            $this->_processTable($backup, $depTable);
        }

        $backup->write($this->getResource()->getTableHeader($table)
            . $this->getResource()->getTableDropSql($table) . "\n");
        $backup->write($this->getResource()->getTableCreateSql($table, true) . "\n");

        $tableStatus = $this->getResource()->getTableStatus($table);

        if ($tableStatus->getRows() && !in_array($table, $this->getIgnoreDataTablesList())) {
            $backup->write($this->getResource()->getTableDataBeforeSql($table));

            if ($tableStatus->getDataLength() > self::BUFFER_LENGTH) {
                if ($tableStatus->getAvgRowLength() < self::BUFFER_LENGTH) {
                    $limit = floor(self::BUFFER_LENGTH / $tableStatus->getAvgRowLength());
                    $multiRowsLength = ceil($tableStatus->getRows() / $limit);
                }
                else {
                    $limit = 1;
                    $multiRowsLength = $tableStatus->getRows();
                }
            }
            else {
                $limit = $tableStatus->getRows();
                $multiRowsLength = 1;
            }

            for ($i = 0; $i < $multiRowsLength; $i ++) {
                $backup->write($this->getResource()->getTableDataSql($table, $limit, $i*$limit));
            }

            $backup->write($this->getResource()->getTableDataAfterSql($table));
        }

        array_pop($this->_processingStack);
        $this->_processedTables[] = $table;

        $dependencyTables = $this->getResource()->getTablesReferenced($table);
        foreach ($dependencyTables as $depTable) {
            $this->_processTable($backup, $depTable);
        }

        if ($asTransaction) {
            $this->getResource()->commitTransaction();
        }

        return $this;
    }

    /* Overrides
     * @see Mage_Backup_Model_Db::getIgnoreDataTablesList()
     * by caching the result
     */
    public function getIgnoreDataTablesList()
    {
        if (is_null($this->_ignoreDataTables)) {
            $this->_ignoreDataTables = parent::getIgnoreDataTablesList();
        }

        return $this->_ignoreDataTables;
    }
}
