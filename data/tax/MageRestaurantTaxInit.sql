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
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Restaurant Taxable';

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
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Restaurant Taxable';

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
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Restaurant Taxable';

-- BUILD RESTUARANT RULE
INSERT INTO `tax_calculation` 
SELECT 
	rt.`tax_calculation_rate_id`,
	ru.`tax_calculation_rule_id`,
	cc.`class_id`,
	pc.`class_id`
FROM `tax_calculation_rate` rt
JOIN `tax_calculation_rule` ru
	ON ru.`code` = 'Retail-Restaurant-Rate 1' AND rt.`code` LIKE '%-CityFips+-%' AND rt.`code` LIKE '%-Restaurant'
JOIN `tax_class` cc
	ON cc.`class_type` = 'CUSTOMER' AND cc.`class_name` = 'Retail Customer'
JOIN `tax_class` pc
	ON pc.`class_type` = 'PRODUCT' AND pc.`class_name` = 'Restaurant Taxable';