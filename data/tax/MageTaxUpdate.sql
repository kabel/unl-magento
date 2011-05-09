TRUNCATE TABLE `unl_tax_boundary`;
LOAD DATA LOCAL INFILE 'NEB.txt' INTO TABLE `unl_tax_boundary` FIELDS TERMINATED BY ',' LINES TERMINATED BY '\r\n';
TRUNCATE TABLE `tax_calculation_rate`;
LOAD DATA LOCAL INFILE 'allRates.csv' INTO TABLE `tax_calculation_rate` FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\r' (`code`, `tax_country_id`, `tax_region_id`, `tax_postcode`, `rate`);
SOURCE MageTaxCalculationInit.sql;
