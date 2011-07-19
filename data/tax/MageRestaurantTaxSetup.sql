-- SETUP NEW TAX CLASS AND RULE
INSERT INTO `tax_class`
	(`class_name`, `class_type`)
VALUES
	('Restaurant Taxable', 'PRODUCT');

INSERT INTO `tax_calculation_rule`
	(`code`, `priority`, `position`)
VALUES
	('Retail-Restaurant-Rate 1',1,4);