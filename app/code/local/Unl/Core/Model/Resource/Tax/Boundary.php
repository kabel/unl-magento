<?php

class Unl_Core_Model_Resource_Tax_Boundary extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_pdoError = 'PDOStatement::execute(): LOAD DATA LOCAL INFILE forbidden';

    protected $_taxRateColumns = array(
        'code',
        'tax_country_id',
        'tax_region_id',
        'tax_postcode',
        'rate'
    );

    protected function _construct()
    {
        $this->_init('unl_core/tax_boundary', 'boundary_id');
    }

    public function getTaxRateColumns()
    {
        return $this->_taxRateColumns;
    }

    public function getInsertColumns()
    {
        $columns = $this->_getWriteAdapter()->describeTable($this->getMainTable());
        unset($columns['boundary_id']);
        $columns = array_keys($columns);

        return $columns;
    }

    protected function _beginImport($table, $withTruncate = true)
    {
        $adapter = $this->_getWriteAdapter();

        if ($withTruncate) {
            $adapter->truncateTable($table);
        } else {
            $adapter->delete($table);
            $adapter->query(sprintf('ALTER TABLE %s AUTO_INCREMENT=%s',
                $adapter->quoteIdentifier($table),
                1
            ));
        }

        return $this;
    }

    public function beginImport()
    {
        $this->_beginImport($this->getMainTable());

        return $this;
    }

    public function beginRateImport()
    {
        $this->_beginImport($this->getTable('tax/tax_calculation_rate'), false);

        return $this;
    }

    public function insertArray($data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->insertArray($this->getMainTable(), $this->getInsertColumns(), $data);

        return $this;
    }

    public function insertTaxRates($data)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->insertArray($this->getTable('tax/tax_calculation_rate'), $this->getTaxRateColumns(), $data);

        return $this;
    }

    public function loadLocalFile($filePath)
    {
        return $this->_loadLocalFile($filePath, $this->getMainTable());
    }

    public function loadLocalRateFile($filePath)
    {
        return $this->_loadLocalFile($filePath, $this->getTable('tax/tax_calculation_rate'), $this->getTaxRateColumns());
    }

    public function supportsLoadFile()
    {
        $connConfig = Mage::getConfig()->getResourceConnectionConfig('core_setup')->asArray();

        return strpos($connConfig['type'], 'mysql') !== false;
    }

    protected function _loadLocalFile($filePath, $table, $fields = array())
    {
        $connConfig = Mage::getConfig()->getResourceConnectionConfig('core_setup')->asArray();

        if (strpos($connConfig['type'], 'pdo') !== false) {
            $connConfig['driver_options'][PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
        }

        $adapter = $this->_resources->createConnection('unl_tax_load', $connConfig['type'], $connConfig);

        $sql = sprintf('LOAD DATA LOCAL INFILE %s INTO TABLE %s FIELDS TERMINATED BY %s',
            $adapter->quote($filePath),
            $adapter->quoteIdentifier($table),
            $adapter->quote(',')
        );

        if ($fields) {
            $columns = array_map(array($adapter, 'quoteIdentifier'), $fields);
            $sql .= sprintf(' (%s)', implode(', ', $columns));
        }

        $oldErrorHandler = set_error_handler(array($this, 'handleError'));

        try {
            $result = $adapter->raw_query($sql);
        } catch (Exception $e) {
            if ($e->getMessage() == $this->_pdoError) {
                $adapter = new Varien_Db_Adapter_Mysqli($connConfig);
                $result = $adapter->raw_query($sql);
            } else {
                set_error_handler($oldErrorHandler);
                throw $e;
            }
        }

        set_error_handler($oldErrorHandler);

        return $this;
    }

    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $errno = $errno & error_reporting();
        if ($errno == 0) {
            return false;
        }

        if ($errno === E_WARNING && $errstr == $this->_pdoError) {
            throw new Exception($errstr);
        } else {
            return mageCoreErrorHandler($errno, $errstr, $errfile, $errline);
        }
    }

    /**
     * Get a select statement for loading tax rate/rule/class needed for tax calculation
     *
     * @param string $ruleCond
     * @param string $customerClass
     * @param string $productClass
     * @param boolean $straightJoin
     * @return Varien_Db_Select
     */
    protected function _getRateLoadSelect($ruleCond, $customerClass, $productClass, $straightJoin = false)
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select();

        if ($straightJoin) {
            $select->useStraightJoin(true);
        }

        $customerCond = implode(' AND ', array(
            $adapter->prepareSqlCondition('cc.class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER),
            $adapter->prepareSqlCondition('cc.class_name', $customerClass)
        ));

        $productCond = array(
            $adapter->prepareSqlCondition('pc.class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT),
        );
        if (!empty($productClass)) {
            $productCond[] = $adapter->prepareSqlCondition('pc.class_name', $productClass);
        }
        $productCond = implode(' AND ', $productCond);

        $select
            ->from(array('rt' => $this->getTable('tax/tax_calculation_rate')), array('tax_calculation_rate_id'))
            ->join(array('ru' => $this->getTable('tax/tax_calculation_rule')), $ruleCond, array('tax_calculation_rule_id'))
            ->join(array('cc' => $this->getTable('tax/tax_class')), $customerCond, array('class_id'))
            ->join(array('pc' => $this->getTable('tax/tax_class')), $productCond, array('class_id'));

        return $select;
    }

    public function rebuildTaxCalculation()
    {
        $adapter = $this->_getWriteAdapter();

        $region = Mage::getModel('directory/region')->loadByCode('NE', 'US');

        $calculation = array(
            'state' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-State'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.code', array('regexp' => '^US-[A-Z]{2}-\\*$')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Taxable Goods',
                'straight' => true,
            ),
            'city' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-City'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', $region->getId()),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CityFips-%')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Taxable Goods',
                'straight' => true,
            ),
            'county' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-County'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', $region->getId()),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CountyFips-%')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Taxable Goods',
                'straight' => true,
            ),
            'force_state' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-State'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.code', 'US-NE'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Force Lincoln Taxable',
            ),
            'force_city' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-City'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', 0),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CityFips-%')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Force Lincoln Taxable',
            ),
            'reseller' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Resell-*-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Resell'),
                )),
                'customer' => 'Reseller',
                'product'  => '',
            ),
            'org' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Org-*-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Exempt Org'),
                )),
                'customer' => 'Exempt Org',
                'product'  => '',
            ),
            'carrier' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Carrier-*-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Exempt Carrier'),
                )),
                'customer' => 'Exempt Carrier',
                'product'  => '',
            ),
            'agency' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Agency-*-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Gov\'t Agency'),
                )),
                'customer' => 'Gov\'t Agency',
                'product'  => '',
            ),
            'food' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Food-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Exempt Food'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Exempt Food',
            ),
            'services' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Services-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Nontaxable Services'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Nontaxable Services',
            ),
            'agri' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Agri-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Agri feed|seed|chem|fert'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Agri feed|seed|chem|fert',
            ),
            'motor' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Motor-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Motor'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Motor Vehicles',
            ),
            'mach' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Mach-Rate 1'),
                    $adapter->prepareSqlCondition('rt.code', 'Agri mach|equip'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Exempt Agri mach|equip',
            ),
            'state_restaurant' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-State'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.code', array('regexp' => '^US-[A-Z]{2}-\\*$')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Restaurant Taxable',
                'straight' => true,
            ),
            'city_restaurant' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-City'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', $region->getId()),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CityFips-%')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Restaurant Taxable',
                'straight' => true,
            ),
            'force_state_restaurant' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-State'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.code', 'US-NE'),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Force Lincoln Restaurant Taxable',
            ),
            'force_city_restaurant' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Tax-City'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', 0),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CityFips-%')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Force Lincoln Restaurant Taxable',
            ),
            'restaurant' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Restaurant-Rate 1'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', $region->getId()),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CityFips+-%')),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-Restaurant')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Restaurant Taxable',
                'straight' => true,
            ),
            'force_restaurant' => array(
                'rule'     => implode(' AND ', array(
                    $adapter->prepareSqlCondition('ru.code', 'Retail-Restaurant-Rate 1'),
                    $adapter->prepareSqlCondition('rt.tax_country_id', 'US'),
                    $adapter->prepareSqlCondition('rt.tax_region_id', 0),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-CityFips+-%')),
                    $adapter->prepareSqlCondition('rt.code', array('like' => '%-Restaurant')),
                )),
                'customer' => 'Retail Customer',
                'product'  => 'Taxable Goods',
                'straight' => true,
            ),
        );

        foreach ($calculation as $calc) {
            $select = $this->_getRateLoadSelect($calc['rule'], $calc['customer'], $calc['product'],
                isset($calc['straight']) ? $calc['straight'] : false);

            $adapter->query($adapter->insertFromSelect($select, $this->getTable('tax/tax_calculation'), array(
                'tax_calculation_rate_id',
                'tax_calculation_rule_id',
                'customer_tax_class_id',
                'product_tax_class_id',
            )));
        }

        return $this;
    }
}
