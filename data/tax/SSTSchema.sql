CREATE TABLE `rates` (
  `state` char(2) NOT NULL,
  `jurisdiction_type` char(2) DEFAULT NULL,
  `jurisdiction_fips_code` char(5) DEFAULT NULL,
  `general_tax_rate_intra` decimal(6,5) unsigned DEFAULT NULL,
  `general_tax_rate_inter` decimal(6,5) unsigned DEFAULT NULL,
  `food_drug_tax_rate_intra` decimal(6,5) unsigned DEFAULT NULL,
  `food_drug_tax_rate_inter` decimal(6,5) unsigned DEFAULT NULL,
  `begin_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`rate_id`),
  KEY `IX_RATE_TYPE_FIPS` (`jurisdiction_type`,`jurisdiction_fips_code`)
) ENGINE=MyISAM;

CREATE TABLE `boundaries` (
  `record_type` char(1) NOT NULL,
  `begin_date` date NOT NULL,
  `end_date` date NOT NULL,
  `low_address_range` int(10) unsigned DEFAULT NULL,
  `high_address_range` int(10) unsigned DEFAULT NULL,
  `odd_even_indicator` char(1) DEFAULT NULL,
  `street_pre_directional` varchar(2) DEFAULT NULL,
  `street_name` varchar(20) DEFAULT NULL,
  `street_suffix_abbr` varchar(4) DEFAULT NULL,
  `street_post_directional` varchar(2) DEFAULT NULL,
  `address_secondary_abbr` varchar(4) DEFAULT NULL,
  `address_secondary_low` varchar(8) DEFAULT NULL,
  `address_secondary_high` varchar(8) DEFAULT NULL,
  `address_secondary_odd_even` char(1) DEFAULT NULL,
  `city_name` varchar(28) DEFAULT NULL,
  `zip_code` int(5) unsigned DEFAULT NULL,
  `plus_4` int(4) unsigned zerofill DEFAULT NULL,
  `zip_code_low` int(5) unsigned DEFAULT NULL,
  `zip_ext_low` int(4) unsigned zerofill DEFAULT NULL,
  `zip_code_high` int(5) unsigned DEFAULT NULL,
  `zip_ext_high` int(4) unsigned zerofill DEFAULT NULL,
  `composite_ser_code` char(5) DEFAULT NULL,
  `fips_state_code` char(2) DEFAULT NULL,
  `fips_state_indicator` char(2) DEFAULT NULL,
  `fips_county_code` char(3) DEFAULT NULL,
  `fips_place_number` char(5) DEFAULT NULL,
  `fips_place_class_code` char(2) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `special_tax_district_code_src_1` char(2) DEFAULT NULL,
  `special_tax_district_code_1` char(5) DEFAULT NULL,
  `tax_auth_type_code_1` char(2) DEFAULT NULL,
  `special_tax_district_code_src_2` char(2) DEFAULT NULL,
  `special_tax_district_code_2` char(5) DEFAULT NULL,
  `tax_auth_type_code_2` char(2) DEFAULT NULL,
  `special_tax_district_code_src_3` char(2) DEFAULT NULL,
  `special_tax_district_code_3` char(5) DEFAULT NULL,
  `tax_auth_type_code_3` char(2) DEFAULT NULL,
  `special_tax_district_code_src_4` char(2) DEFAULT NULL,
  `special_tax_district_code_4` char(5) DEFAULT NULL,
  `tax_auth_type_code_4` char(2) DEFAULT NULL,
  `special_tax_district_code_src_5` char(2) DEFAULT NULL,
  `special_tax_district_code_5` char(5) DEFAULT NULL,
  `tax_auth_type_code_5` char(2) DEFAULT NULL,
  `special_tax_district_code_src_6` char(2) DEFAULT NULL,
  `special_tax_district_code_6` char(5) DEFAULT NULL,
  `tax_auth_type_code_6` char(2) DEFAULT NULL,
  `special_tax_district_code_src_7` char(2) DEFAULT NULL,
  `special_tax_district_code_7` char(5) DEFAULT NULL,
  `tax_auth_type_code_7` char(2) DEFAULT NULL,
  `special_tax_district_code_src_8` char(2) DEFAULT NULL,
  `special_tax_district_code_8` char(5) DEFAULT NULL,
  `tax_auth_type_code_8` char(2) DEFAULT NULL,
  `special_tax_district_code_src_9` char(2) DEFAULT NULL,
  `special_tax_district_code_9` char(5) DEFAULT NULL,
  `tax_auth_type_code_9` char(2) DEFAULT NULL,
  `special_tax_district_code_src_10` char(2) DEFAULT NULL,
  `special_tax_district_code_10` char(5) DEFAULT NULL,
  `tax_auth_type_code_10` char(2) DEFAULT NULL,
  `special_tax_district_code_src_11` char(2) DEFAULT NULL,
  `special_tax_district_code_11` char(5) DEFAULT NULL,
  `tax_auth_type_code_11` char(2) DEFAULT NULL,
  `special_tax_district_code_src_12` char(2) DEFAULT NULL,
  `special_tax_district_code_12` char(5) DEFAULT NULL,
  `tax_auth_type_code_12` char(2) DEFAULT NULL,
  `special_tax_district_code_src_13` char(2) DEFAULT NULL,
  `special_tax_district_code_13` char(5) DEFAULT NULL,
  `tax_auth_type_code_13` char(2) DEFAULT NULL,
  `special_tax_district_code_src_14` char(2) DEFAULT NULL,
  `special_tax_district_code_14` char(5) DEFAULT NULL,
  `tax_auth_type_code_14` char(2) DEFAULT NULL,
  `special_tax_district_code_src_15` char(2) DEFAULT NULL,
  `special_tax_district_code_15` char(5) DEFAULT NULL,
  `tax_auth_type_code_15` char(2) DEFAULT NULL,
  `special_tax_district_code_src_16` char(2) DEFAULT NULL,
  `special_tax_district_code_16` char(5) DEFAULT NULL,
  `tax_auth_type_code_16` char(2) DEFAULT NULL,
  `special_tax_district_code_src_17` char(2) DEFAULT NULL,
  `special_tax_district_code_17` char(5) DEFAULT NULL,
  `tax_auth_type_code_17` char(2) DEFAULT NULL,
  `special_tax_district_code_src_18` char(2) DEFAULT NULL,
  `special_tax_district_code_18` char(5) DEFAULT NULL,
  `tax_auth_type_code_18` char(2) DEFAULT NULL,
  `special_tax_district_code_src_19` char(2) DEFAULT NULL,
  `special_tax_district_code_19` char(5) DEFAULT NULL,
  `tax_auth_type_code_19` char(2) DEFAULT NULL,
  `special_tax_district_code_src_20` char(2) DEFAULT NULL,
  `special_tax_district_code_20` char(5) DEFAULT NULL,
  `tax_auth_type_code_20` char(2) DEFAULT NULL,
  `boundary_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`boundary_id`),
  KEY `IX_BOUNDARY_RECORD_TYPE` (`record_type`),
  KEY `IX_BOUNDARY_CITY_STREET` (`city_name`,`street_name`),
  KEY `IX_BOUNDARY_FIPS_PLACE` (`fips_place_number`)
) ENGINE=MyISAM;

-- --------------------------------------------------------------------------------
-- Routine DDL
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE `fetch_city_plus_rates`(IN state_id int, IN city_fips char(5), IN plus_info VARCHAR(100), IN rate DECIMAL(6,5))
BEGIN
SELECT 

  CONCAT('US-NE-', CAST(b.zip_code AS CHAR), '-', CAST(b.plus_4 AS CHAR), '-CityFips+-', b.fips_place_number, '-', plus_info) AS code,

  'US' AS country,

  state_id AS state,

  CONCAT(CAST(b.zip_code AS CHAR), '-', CAST(b.plus_4 AS CHAR)) AS postal_code,

  rate * 100 AS rate
FROM boundaries b

WHERE record_type = 'A'
  AND NOW() BETWEEN b.begin_date AND b.end_date

  AND b.fips_place_number = city_fips
ORDER BY b.zip_code, b.plus_4;
END$$

DELIMITER ;

-- --------------------------------------------------------------------------------
-- Routine DDL
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE `fetch_city_rates`(IN state_id int)
BEGIN
SELECT 

  CONCAT('US-NE-', CAST(b.zip_code AS CHAR), '-', CAST(b.plus_4 AS CHAR), '-CityFips-', b.fips_place_number) AS code,

  'US' AS country,

  state_id AS state,

  CONCAT(CAST(b.zip_code AS CHAR), '-', CAST(b.plus_4 AS CHAR)) AS postal_code,

  r.general_tax_rate_intra * 100 AS rate
FROM boundaries b

INNER JOIN rates r ON b.fips_place_number = r.jurisdiction_fips_code AND r.jurisdiction_type = 01
WHERE record_type = 'A'
  AND NOW() BETWEEN b.begin_date AND b.end_date

  AND NOW() BETWEEN r.begin_date AND r.end_date
  AND b.fips_place_number <> ''
ORDER BY b.zip_code, b.plus_4, b.fips_place_number DESC;
END$$

DELIMITER ;

-- --------------------------------------------------------------------------------
-- Routine DDL
-- --------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE `fetch_county_rates`(IN state_id int)
BEGIN
SELECT 

  CONCAT('US-NE-', CAST(b.zip_code AS CHAR), '-', CAST(b.plus_4 AS CHAR), '-CountyFips-', b.fips_county_code) AS code,

  'US' AS country,

  state_id AS state,

  CONCAT(CAST(b.zip_code AS CHAR), '-', CAST(b.plus_4 AS CHAR)) AS postal_code,

  r.general_tax_rate_intra * 100 AS rate
FROM boundaries b

INNER JOIN rates r ON b.fips_county_code = r.jurisdiction_fips_code AND r.jurisdiction_type = 00
WHERE record_type = 'A'
  AND NOW() BETWEEN b.begin_date AND b.end_date

  AND NOW() BETWEEN r.begin_date AND r.end_date
  AND b.fips_county_code <> ''
ORDER BY b.zip_code, b.plus_4, b.fips_county_code DESC;
END$$

DELIMITER ;
