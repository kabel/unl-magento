-- BUILD STATE RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Tax-State' AND rt.`code` REGEXP '^US-[A-Z]{2}-\\*$'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Taxable Goods';
	
-- BUILD CITY RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Tax-City' AND rt.`code` LIKE '%-CityFips-%'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Taxable Goods';
	
-- BUILD COUNTY RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Tax-County' AND rt.`code` LIKE '%-CountyFips-%'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Taxable Goods';
	
-- BUILD RESELLER RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Resell-*-Rate 1' AND rt.`code` = 'Resell'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Reseller'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT';
	
-- BUILD ORG RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Org-*-Rate 1' AND rt.`code` = 'Exempt Org'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Exempt Org'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT';
	
-- BUILD CARRIER RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Carrier-*-Rate 1' AND rt.`code` = 'Exempt Carrier'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Exempt Carrier'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT';
	
-- BUILD AGENCY RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Agency-*-Rate 1' AND rt.`code` = 'Gov\'t Agency'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Gov\'t Agency'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT';
	
-- BUILD FOOD RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Food-Rate 1' AND rt.`code` = 'Exempt Food'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Exempt Food';

-- BUILD SERVICES RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Services-Rate 1' AND rt.`code` = 'Nontaxable Services'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Nontaxable Services';
	
-- BUILD AGRI RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Agri-Rate 1' AND rt.`code` = 'Agri feed|seed|chem|fert'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Agri feed|seed|chem|fert';
	
-- BUILD MOTOR RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Motor-Rate 1' AND rt.`code` = 'Motor'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Motor Vehicles';
	
-- BUILD MACH RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Mach-Rate 1' AND rt.`code` = 'Agri mach|equip'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Exempt Agri mach|equip';