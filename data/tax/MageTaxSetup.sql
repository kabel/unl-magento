-- DO THE RATE IMPORT BEFORE RUNNING THIS --

INSERT INTO `tax_class`
	(`class_name`, `class_type`)
VALUES
	('Nontaxable Services', 'PRODUCT'),
	('Exempt Food', 'PRODUCT'),
	('Agri feed|seed|chem|fert', 'PRODUCT'),
	('Motor Vehicles', 'PRODUCT'),
	('Exempt Agri mach|equip', 'PRODUCT'),
	('Reseller', 'CUSTOMER'),
	('Exempt Org', 'CUSTOMER'),
	('Exempt Carrier', 'CUSTOMER'),
	('Gov\'t Agency', 'CUSTOMER');

TRUNCATE TABLE `tax_calculation_rule`;
INSERT INTO `tax_calculation_rule`
	(`code`, `priority`, `position`)
VALUES
	('Retail-Tax-State',10,1),
	('Retail-Tax-City',10,2),
	('Retail-Tax-County',10,3),
	('Retail-Food-Rate 1',10,4),
	('Retail-Services-Rate 1',10,5),
	('Retail-Agri-Rate 1',10,6),
	('Retail-Motor-Rate 1',10,7),
	('Retail-Mach-Rate 1',10,8),
	('Resell-*-Rate 1',10,9),
	('Org-*-Rate 1',10,10),
	('Carrier-*-Rate 1',10,11),
	('Agency-*-Rate 1',10,12);