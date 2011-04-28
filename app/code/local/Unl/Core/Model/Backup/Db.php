<?php

class Unl_Core_Model_Backup_Db extends Mage_Backup_Model_Db
{
    /**
     * The list of tables to exclude from the backup
     *
     * @var array
     */
    protected $_ignoreTables = array(
        'unl_tax_boundary'
    );

    /* Overrides
     * @see Mage_Backup_Model_Db::createBackup()
     * by excluding certain tables and allowing infinite time
     */
    public function createBackup(Mage_Backup_Model_Backup $backup)
    {
        ini_set('max_execution_time', 0);
        $backup->open(true);

        $this->getResource()->beginTransaction();

        $tables = $this->getResource()->getTables();

        $backup->write($this->getResource()->getHeader());

        foreach ($tables as $table) {
            if (in_array($table, $this->_ignoreTables)) {
                continue;
            }

            $backup->write($this->getResource()->getTableHeader($table) . $this->getResource()->getTableDropSql($table) . "\n");
            $backup->write($this->getResource()->getTableCreateSql($table, false) . "\n");

            $tableStatus = $this->getResource()->getTableStatus($table);

            if ($tableStatus->getRows()) {
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
        }
        $backup->write($this->getResource()->getTableForeignKeysSql());
        $backup->write($this->getResource()->getFooter());

        $this->getResource()->commitTransaction();

        $backup->close();

        return $this;
    }
}
