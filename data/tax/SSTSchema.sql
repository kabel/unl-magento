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
  `spcl_tax_dist_code_src_1` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_1` char(5) DEFAULT NULL,
  `tax_auth_type_code_1` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_2` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_2` char(5) DEFAULT NULL,
  `tax_auth_type_code_2` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_3` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_3` char(5) DEFAULT NULL,
  `tax_auth_type_code_3` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_4` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_4` char(5) DEFAULT NULL,
  `tax_auth_type_code_4` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_5` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_5` char(5) DEFAULT NULL,
  `tax_auth_type_code_5` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_6` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_6` char(5) DEFAULT NULL,
  `tax_auth_type_code_6` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_7` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_7` char(5) DEFAULT NULL,
  `tax_auth_type_code_7` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_8` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_8` char(5) DEFAULT NULL,
  `tax_auth_type_code_8` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_9` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_9` char(5) DEFAULT NULL,
  `tax_auth_type_code_9` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_10` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_10` char(5) DEFAULT NULL,
  `tax_auth_type_code_10` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_11` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_11` char(5) DEFAULT NULL,
  `tax_auth_type_code_11` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_12` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_12` char(5) DEFAULT NULL,
  `tax_auth_type_code_12` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_13` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_13` char(5) DEFAULT NULL,
  `tax_auth_type_code_13` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_14` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_14` char(5) DEFAULT NULL,
  `tax_auth_type_code_14` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_15` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_15` char(5) DEFAULT NULL,
  `tax_auth_type_code_15` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_16` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_16` char(5) DEFAULT NULL,
  `tax_auth_type_code_16` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_17` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_17` char(5) DEFAULT NULL,
  `tax_auth_type_code_17` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_18` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_18` char(5) DEFAULT NULL,
  `tax_auth_type_code_18` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_19` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_19` char(5) DEFAULT NULL,
  `tax_auth_type_code_19` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_src_20` char(2) DEFAULT NULL,
  `spcl_tax_dist_code_20` char(5) DEFAULT NULL,
  `tax_auth_type_code_20` char(2) DEFAULT NULL,
  `boundary_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`boundary_id`),
  KEY `IX_BOUNDARY_RECORD_TYPE` (`record_type`),
  KEY `IX_BOUNDARY_CITY_STREET` (`city_name`,`street_name`),
  KEY `IX_BOUNDARY_FIPS_PLACE` (`fips_place_number`)
) ENGINE=MyISAM;

CREATE TABLE `county` (
  `county_id` varchar(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`county_id`)
) ENGINE=MyISAM;

INSERT INTO `county` (`county_id`,`name`) VALUES ('001','Adams');
INSERT INTO `county` (`county_id`,`name`) VALUES ('003','Antelope');
INSERT INTO `county` (`county_id`,`name`) VALUES ('005','Arthur');
INSERT INTO `county` (`county_id`,`name`) VALUES ('007','Banner');
INSERT INTO `county` (`county_id`,`name`) VALUES ('009','Blaine');
INSERT INTO `county` (`county_id`,`name`) VALUES ('011','Boone');
INSERT INTO `county` (`county_id`,`name`) VALUES ('013','Box Butte');
INSERT INTO `county` (`county_id`,`name`) VALUES ('015','Boyd');
INSERT INTO `county` (`county_id`,`name`) VALUES ('017','Brown');
INSERT INTO `county` (`county_id`,`name`) VALUES ('019','Buffalo');
INSERT INTO `county` (`county_id`,`name`) VALUES ('021','Burt');
INSERT INTO `county` (`county_id`,`name`) VALUES ('023','Butler');
INSERT INTO `county` (`county_id`,`name`) VALUES ('025','Cass');
INSERT INTO `county` (`county_id`,`name`) VALUES ('027','Cedar');
INSERT INTO `county` (`county_id`,`name`) VALUES ('029','Chase');
INSERT INTO `county` (`county_id`,`name`) VALUES ('031','Cherry');
INSERT INTO `county` (`county_id`,`name`) VALUES ('033','Cheyenne');
INSERT INTO `county` (`county_id`,`name`) VALUES ('035','Clay');
INSERT INTO `county` (`county_id`,`name`) VALUES ('037','Colfax');
INSERT INTO `county` (`county_id`,`name`) VALUES ('039','Cuming');
INSERT INTO `county` (`county_id`,`name`) VALUES ('041','Custer');
INSERT INTO `county` (`county_id`,`name`) VALUES ('043','Dakota');
INSERT INTO `county` (`county_id`,`name`) VALUES ('045','Dawes');
INSERT INTO `county` (`county_id`,`name`) VALUES ('047','Dawson');
INSERT INTO `county` (`county_id`,`name`) VALUES ('049','Deuel');
INSERT INTO `county` (`county_id`,`name`) VALUES ('051','Dixon');
INSERT INTO `county` (`county_id`,`name`) VALUES ('053','Dodge');
INSERT INTO `county` (`county_id`,`name`) VALUES ('055','Douglas');
INSERT INTO `county` (`county_id`,`name`) VALUES ('057','Dundy');
INSERT INTO `county` (`county_id`,`name`) VALUES ('059','Fillmore');
INSERT INTO `county` (`county_id`,`name`) VALUES ('061','Franklin');
INSERT INTO `county` (`county_id`,`name`) VALUES ('063','Frontier');
INSERT INTO `county` (`county_id`,`name`) VALUES ('065','Furnas');
INSERT INTO `county` (`county_id`,`name`) VALUES ('067','Gage');
INSERT INTO `county` (`county_id`,`name`) VALUES ('069','Garden');
INSERT INTO `county` (`county_id`,`name`) VALUES ('071','Garfield');
INSERT INTO `county` (`county_id`,`name`) VALUES ('073','Gosper');
INSERT INTO `county` (`county_id`,`name`) VALUES ('075','Grant');
INSERT INTO `county` (`county_id`,`name`) VALUES ('077','Greeley');
INSERT INTO `county` (`county_id`,`name`) VALUES ('079','Hall');
INSERT INTO `county` (`county_id`,`name`) VALUES ('081','Hamilton');
INSERT INTO `county` (`county_id`,`name`) VALUES ('083','Harlan');
INSERT INTO `county` (`county_id`,`name`) VALUES ('085','Hayes');
INSERT INTO `county` (`county_id`,`name`) VALUES ('087','Hitchcock');
INSERT INTO `county` (`county_id`,`name`) VALUES ('089','Holt');
INSERT INTO `county` (`county_id`,`name`) VALUES ('091','Hooker');
INSERT INTO `county` (`county_id`,`name`) VALUES ('093','Howard');
INSERT INTO `county` (`county_id`,`name`) VALUES ('095','Jefferson');
INSERT INTO `county` (`county_id`,`name`) VALUES ('097','Johnson');
INSERT INTO `county` (`county_id`,`name`) VALUES ('099','Kearney');
INSERT INTO `county` (`county_id`,`name`) VALUES ('101','Keith');
INSERT INTO `county` (`county_id`,`name`) VALUES ('103','Keya Paha');
INSERT INTO `county` (`county_id`,`name`) VALUES ('105','Kimball');
INSERT INTO `county` (`county_id`,`name`) VALUES ('107','Knox');
INSERT INTO `county` (`county_id`,`name`) VALUES ('109','Lancaster');
INSERT INTO `county` (`county_id`,`name`) VALUES ('111','Lincoln');
INSERT INTO `county` (`county_id`,`name`) VALUES ('113','Logan');
INSERT INTO `county` (`county_id`,`name`) VALUES ('115','Loup');
INSERT INTO `county` (`county_id`,`name`) VALUES ('117','McPherson');
INSERT INTO `county` (`county_id`,`name`) VALUES ('119','Madison');
INSERT INTO `county` (`county_id`,`name`) VALUES ('121','Merrick');
INSERT INTO `county` (`county_id`,`name`) VALUES ('123','Morrill');
INSERT INTO `county` (`county_id`,`name`) VALUES ('125','Nance');
INSERT INTO `county` (`county_id`,`name`) VALUES ('127','Nemaha');
INSERT INTO `county` (`county_id`,`name`) VALUES ('129','Nuckolls');
INSERT INTO `county` (`county_id`,`name`) VALUES ('131','Otoe');
INSERT INTO `county` (`county_id`,`name`) VALUES ('133','Pawnee');
INSERT INTO `county` (`county_id`,`name`) VALUES ('135','Perkins');
INSERT INTO `county` (`county_id`,`name`) VALUES ('137','Phelps');
INSERT INTO `county` (`county_id`,`name`) VALUES ('139','Pierce');
INSERT INTO `county` (`county_id`,`name`) VALUES ('141','Platte');
INSERT INTO `county` (`county_id`,`name`) VALUES ('143','Polk');
INSERT INTO `county` (`county_id`,`name`) VALUES ('145','Red Willow');
INSERT INTO `county` (`county_id`,`name`) VALUES ('147','Richardson');
INSERT INTO `county` (`county_id`,`name`) VALUES ('149','Rock');
INSERT INTO `county` (`county_id`,`name`) VALUES ('151','Saline');
INSERT INTO `county` (`county_id`,`name`) VALUES ('153','Sarpy');
INSERT INTO `county` (`county_id`,`name`) VALUES ('155','Saunders');
INSERT INTO `county` (`county_id`,`name`) VALUES ('157','Scotts Bluff');
INSERT INTO `county` (`county_id`,`name`) VALUES ('159','Seward');
INSERT INTO `county` (`county_id`,`name`) VALUES ('161','Sheridan');
INSERT INTO `county` (`county_id`,`name`) VALUES ('163','Sherman');
INSERT INTO `county` (`county_id`,`name`) VALUES ('165','Sioux');
INSERT INTO `county` (`county_id`,`name`) VALUES ('167','Stanton');
INSERT INTO `county` (`county_id`,`name`) VALUES ('169','Thayer');
INSERT INTO `county` (`county_id`,`name`) VALUES ('171','Thomas');
INSERT INTO `county` (`county_id`,`name`) VALUES ('173','Thurston');
INSERT INTO `county` (`county_id`,`name`) VALUES ('175','Valley');
INSERT INTO `county` (`county_id`,`name`) VALUES ('177','Washington');
INSERT INTO `county` (`county_id`,`name`) VALUES ('179','Wayne');
INSERT INTO `county` (`county_id`,`name`) VALUES ('181','Webster');
INSERT INTO `county` (`county_id`,`name`) VALUES ('183','Wheeler');
INSERT INTO `county` (`county_id`,`name`) VALUES ('185','York');

CREATE TABLE `place` (
  `fips_place_number` int(5) unsigned zerofill NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`fips_place_number`)
) ENGINE=MyISAM;

INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00205,'Abie');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00240,'Adams');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00415,'Ainsworth');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00555,'Albion');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00625,'Alda');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00730,'Alexandria');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00870,'Allen');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00905,'Alliance');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (00975,'Alma');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01150,'Alvo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01290,'Amherst');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01465,'Anoka');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01500,'Anselmo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01535,'Ansley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01780,'Arapahoe');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01850,'Arcadia');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (01990,'Arlington');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02095,'Arnold');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02200,'Arthur');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02305,'Ashland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02375,'Ashton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02550,'Atkinson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02620,'Atlanta');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02655,'Auburn');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02690,'Aurora');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02795,'Avoca');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02830,'Axtell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (02865,'Ayr');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03005,'Bancroft');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03040,'Barada');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03075,'Barneston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03145,'Bartlett');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03180,'Bartley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03215,'Bassett');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03250,'Battle Creek');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03285,'Bayard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03355,'Bazile Mills');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03390,'Beatrice');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03495,'Beaver City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03530,'Beaver Crossing');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03600,'Bee');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03635,'Beemer');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03775,'Belden');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03810,'Belgrade');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (03950,'Bellevue');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04020,'Bellwood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04160,'Belvidere');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04195,'Benedict');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04230,'Benkelman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04300,'Bennet');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04405,'Bennington');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04615,'Bertrand');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04650,'Berwyn');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (04895,'Big Springs');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (05140,'Bladen');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (05350,'Blair');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (05455,'Bloomfield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (05490,'Bloomington');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (05560,'Blue Hill');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (05630,'Blue Springs');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06015,'Boys Town');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06050,'Bradshaw');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06085,'Brady');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06120,'Brainard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06260,'Brewster');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06295,'Bridgeport');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06400,'Bristow');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06470,'Broadwater');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06505,'Brock');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06610,'Broken Bow');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06750,'Brownville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06785,'Brule');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06820,'Bruning');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06855,'Bruno');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (06890,'Brunswick');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07065,'Burchard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07205,'Burr');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07310,'Burton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07345,'Burwell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07415,'Bushnell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07485,'Butte');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07555,'Byron');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07625,'Cairo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07660,'Callaway');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07730,'Cambridge');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07870,'Campbell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (07975,'Carleton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08010,'Carroll');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08150,'Cedar Bluffs');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08185,'Cedar Creek');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08220,'Cedar Rapids');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08360,'Center');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08535,'Central City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08570,'Ceresco');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08605,'Chadron');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08675,'Chambers');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08780,'Chapman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (08885,'Chappell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09095,'Chester');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09165,'Clarks');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09200,'Clarkson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09270,'Clatonia');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09375,'Clay Center');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09445,'Clearwater');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09655,'Clinton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09760,'Cody');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (09865,'Coleridge');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10005,'Colon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10110,'Columbus');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10180,'Comstock');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10250,'Concord');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10390,'Cook');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10495,'Cordova');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10600,'Cornlea');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10635,'Cortland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10740,'Cotesfield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (10985,'Cowles');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11020,'Cozad');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11055,'Crab Orchard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11090,'Craig');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11195,'Crawford');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11230,'Creighton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11300,'Creston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11370,'Crete');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11440,'Crofton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11475,'Crookston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11615,'Culbertson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11825,'Curtis');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (11860,'Cushing');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12000,'Dakota City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12070,'Dalton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12105,'Danbury');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12140,'Dannebrog');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12245,'Davenport');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12280,'Davey');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12315,'David City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12420,'Dawson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12455,'Daykin');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12525,'Decatur');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12770,'Denton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12840,'Deshler');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (12945,'Deweese');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13015,'De Witt');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13085,'Diller');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13190,'Dix');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13225,'Dixon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13295,'Dodge');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13365,'Doniphan');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13435,'Dorchester');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13505,'Douglas');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13750,'Du Bois');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13855,'Dunbar');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13890,'Duncan');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (13960,'Dunning');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14065,'Dwight');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14100,'Eagle');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14380,'Eddyville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14450,'Edgar');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14520,'Edison');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14555,'Elba');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14730,'Elgin');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (14975,'Elk Creek');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15360,'Elm Creek');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15430,'Elmwood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15500,'Elsie');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15570,'Elwood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15605,'Elyria');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15710,'Emerson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15815,'Emmet');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (15920,'Endicott');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16025,'Ericson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16165,'Eustis');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16270,'Ewing');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16340,'Exeter');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16410,'Fairbury');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16445,'Fairfield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16550,'Fairmont');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16655,'Falls City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16725,'Farnam');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16760,'Farwell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16830,'Filley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (16935,'Firth');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17110,'Fordyce');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17145,'Fort Calhoun');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17320,'Foster');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17530,'Franklin');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17670,'Fremont');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17775,'Friend');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17810,'Fullerton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17880,'Funk');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (17950,'Gandy');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18230,'Garland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18300,'Garrison');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18405,'Geneva');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18475,'Genoa');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18580,'Gering');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18615,'Gibbon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18720,'Gilead');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (18825,'Giltner');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19070,'Glenvil');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19245,'Goehner');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19350,'Gordon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19385,'Gothenburg');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19455,'Grafton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19595,'Grand Island');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (19910,'Grant');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20190,'Greenwood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20225,'Gresham');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20260,'Gretna');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20295,'Gross');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20365,'Guide Rock');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20435,'Gurley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20470,'Hadar');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20540,'Haigler');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20610,'Hallam');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20680,'Halsey');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20750,'Hamlet');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20785,'Hampton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20960,'Harbine');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (20995,'Hardy');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21240,'Harrison');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21275,'Hartington');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21345,'Harvard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21415,'Hastings');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21660,'Hayes Center');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21730,'Hay Springs');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21765,'Hazard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21835,'Heartwell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (21905,'Hebron');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22045,'Hemingford');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22080,'Henderson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22115,'Hendley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22150,'Henry');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22185,'Herman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22290,'Hershey');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22325,'Hickman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22430,'Hildreth');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22605,'Holbrook');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22640,'Holdrege');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22815,'Holstein');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (22920,'Homer');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23025,'Hooper');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23200,'Hordville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23235,'Hoskins');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23340,'Howells');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23375,'Hubbard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23410,'Hubbell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23445,'Humboldt');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23480,'Humphrey');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23550,'Huntley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23655,'Hyannis');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23690,'Imperial');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23830,'Indianola');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (23970,'Inglewood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24075,'Inman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24285,'Ithaca');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24355,'Jackson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24530,'Jansen');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24670,'Johnson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24740,'Johnstown');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24915,'Julian');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (24950,'Juniata');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (25055,'Kearney');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (25160,'Kenesaw');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (25230,'Kennard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (25405,'Kilgore');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (25475,'Kimball');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26175,'Lamar');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26350,'Laurel');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26385,'La Vista');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26420,'Lawrence');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26455,'Lebanon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26560,'Leigh');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26630,'Leshara');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26805,'Lewellen');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26875,'Lewiston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26910,'Lexington');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (26980,'Liberty');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (28000,'Lincoln');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (28105,'Lindsay');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (28245,'Linwood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (28350,'Litchfield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (28420,'Lodgepole');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29050,'Long Pine');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29085,'Loomis');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29175,'Lorton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29260,'Louisville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29470,'Loup City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29645,'Lushton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29715,'Lyman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29750,'Lynch');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29855,'Lyons');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29925,'McCook');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (29960,'McCool Junction');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30030,'McGrew');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30065,'McLean');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30240,'Madison');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30275,'Madrid');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30310,'Magnet');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30345,'Malcolm');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30380,'Malmo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30415,'Manley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30730,'Marquette');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (30940,'Martinsburg');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31045,'Maskell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31115,'Mason City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31220,'Maxwell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31325,'Maywood');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31395,'Mead');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31465,'Meadow Grove');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31570,'Melbeta');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31640,'Memphis');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31780,'Merna');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (31815,'Merriman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32060,'Milford');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32130,'Miller');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32200,'Milligan');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32305,'Minatare');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32340,'Minden');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32410,'Mitchell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32550,'Monowi');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32585,'Monroe');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32760,'Moorefield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32830,'Morrill');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (32865,'Morse Bluff');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33250,'Mullen');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33320,'Murdock');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33425,'Murray');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33530,'Naper');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33565,'Naponee');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33705,'Nebraska City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33740,'Nehawka');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33775,'Neligh');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33880,'Nelson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33950,'Nemaha');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (33985,'Nenzel');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34090,'Newcastle');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34230,'Newman Grove');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34265,'Newport');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34300,'Nickerson');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34370,'Niobrara');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34545,'Nora');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34615,'Norfolk');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34650,'Norman');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34720,'North Bend');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (34825,'North Loup');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35000,'North Platte');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35245,'Oak');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35385,'Oakdale');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35490,'Oakland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35560,'Obert');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35665,'Oconto');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35700,'Octavia');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35735,'Odell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (35980,'Ogallala');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (36015,'Ohiowa');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37000,'Omaha');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37105,'O''Neill');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37140,'Ong');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37210,'Orchard');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37280,'Ord');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37420,'Orleans');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37525,'Osceola');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37560,'Oshkosh');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37595,'Osmond');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37630,'Otoe');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37770,'Overton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (37910,'Oxford');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38085,'Page');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38130,'Palisade');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38160,'Palmer');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38190,'Palmyra');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38225,'Panama');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38295,'Papillion');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38575,'Pawnee City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38610,'Paxton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38750,'Pender');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38960,'Peru');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (38995,'Petersburg');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39030,'Phillips');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39065,'Pickrell');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39100,'Pierce');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39135,'Pilger');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39170,'Plainview');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39310,'Platte Center');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39345,'Plattsmouth');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39380,'Pleasant Dale');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39450,'Pleasanton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39590,'Plymouth');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39660,'Polk');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39695,'Ponca');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39870,'Potter');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (39975,'Prague');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40325,'Preston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40360,'Primrose');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40430,'Prosser');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40570,'Ragan');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40605,'Ralston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40675,'Randolph');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40710,'Ravenna');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40780,'Raymond');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (40920,'Red Cloud');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41130,'Republican City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41235,'Reynolds');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41375,'Richland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41480,'Rising City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41515,'Riverdale');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41760,'Riverton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (41830,'Roca');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42075,'Rockville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42145,'Rogers');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42250,'Rosalie');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42355,'Roseland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42495,'Royal');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42670,'Rulo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42775,'Rushville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (42810,'Ruskin');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43055,'Saint Edward');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43090,'Saint Helena');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43265,'Saint Paul');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43370,'Salem');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43475,'Santee');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43685,'Sargent');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (43755,'Saronville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44035,'Schuyler');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44070,'Scotia');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44245,'Scottsbluff');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44280,'Scribner');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44385,'Seneca');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44420,'Seward');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44595,'Shelby');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (44700,'Shelton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45085,'Shickley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45190,'Sholes');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45225,'Shubert');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45295,'Sidney');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45400,'Silver Creek');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45575,'Smithfield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45610,'Snyder');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (45680,'South Bend');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46030,'South Sioux City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46135,'Spalding');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46275,'Spencer');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46380,'Sprague');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46520,'Springfield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46625,'Springview');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46695,'Stamford');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46800,'Stanton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46835,'Staplehurst');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (46870,'Stapleton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47010,'Steele City');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47080,'Steinauer');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47115,'Stella');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47150,'Sterling');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47220,'Stockham');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47290,'Stockville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47360,'Strang');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47395,'Stratton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47465,'Stromsburg');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47500,'Stuart');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47675,'Sumner');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47815,'Superior');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47850,'Surprise');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47920,'Sutherland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (47955,'Sutton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48060,'Swanton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48235,'Syracuse');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48270,'Table Rock');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48305,'Talmage');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48410,'Tarnov');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48445,'Taylor');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48480,'Tecumseh');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48515,'Tekamah');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48585,'Terrytown');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48690,'Thayer');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48760,'Thedford');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48900,'Thurston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (48935,'Tilden');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49005,'Tobias');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49145,'Trenton');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49285,'Trumbull');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49425,'Uehling');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49460,'Ulysses');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49530,'Unadilla');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49635,'Union');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49880,'Upland');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49915,'Utica');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (49950,'Valentine');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50020,'Valley');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50125,'Valparaiso');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50230,'Venango');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50335,'Verdel');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50370,'Verdigre');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50510,'Verdon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50790,'Virginia');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50895,'Waco');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (50965,'Wahoo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51070,'Wakefield');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51175,'Wallace');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51245,'Walthill');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51595,'Washington');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51630,'Waterbury');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51665,'Waterloo');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51700,'Wauneta');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51735,'Wausa');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51770,'Waverly');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (51840,'Wayne');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52015,'Weeping Water');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52085,'Wellfleet');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52295,'Western');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52540,'Weston');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52575,'West Point');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52925,'Whitney');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52960,'Wilber');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (52995,'Wilcox');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53240,'Wilsonville');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53275,'Winnebago');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53345,'Winnetoon');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53380,'Winside');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53415,'Winslow');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53450,'Wisner');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53520,'Wolbach');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53555,'Wood Lake');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53660,'Wood River');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53835,'Wymore');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (53905,'Wynot');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (54045,'York');
INSERT INTO `place` (`fips_place_number`,`name`) VALUES (54115,'Yutan');

CREATE TABLE `region` (
  `region_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`region_id`)
) ENGINE=MyISAM;

INSERT INTO `region` (`region_id`,`code`) VALUES (1,'AL');
INSERT INTO `region` (`region_id`,`code`) VALUES (2,'AK');
INSERT INTO `region` (`region_id`,`code`) VALUES (3,'AS');
INSERT INTO `region` (`region_id`,`code`) VALUES (4,'AZ');
INSERT INTO `region` (`region_id`,`code`) VALUES (5,'AR');
INSERT INTO `region` (`region_id`,`code`) VALUES (6,'AF');
INSERT INTO `region` (`region_id`,`code`) VALUES (7,'AA');
INSERT INTO `region` (`region_id`,`code`) VALUES (8,'AC');
INSERT INTO `region` (`region_id`,`code`) VALUES (9,'AE');
INSERT INTO `region` (`region_id`,`code`) VALUES (10,'AM');
INSERT INTO `region` (`region_id`,`code`) VALUES (11,'AP');
INSERT INTO `region` (`region_id`,`code`) VALUES (12,'CA');
INSERT INTO `region` (`region_id`,`code`) VALUES (13,'CO');
INSERT INTO `region` (`region_id`,`code`) VALUES (14,'CT');
INSERT INTO `region` (`region_id`,`code`) VALUES (15,'DE');
INSERT INTO `region` (`region_id`,`code`) VALUES (16,'DC');
INSERT INTO `region` (`region_id`,`code`) VALUES (17,'FM');
INSERT INTO `region` (`region_id`,`code`) VALUES (18,'FL');
INSERT INTO `region` (`region_id`,`code`) VALUES (19,'GA');
INSERT INTO `region` (`region_id`,`code`) VALUES (20,'GU');
INSERT INTO `region` (`region_id`,`code`) VALUES (21,'HI');
INSERT INTO `region` (`region_id`,`code`) VALUES (22,'ID');
INSERT INTO `region` (`region_id`,`code`) VALUES (23,'IL');
INSERT INTO `region` (`region_id`,`code`) VALUES (24,'IN');
INSERT INTO `region` (`region_id`,`code`) VALUES (25,'IA');
INSERT INTO `region` (`region_id`,`code`) VALUES (26,'KS');
INSERT INTO `region` (`region_id`,`code`) VALUES (27,'KY');
INSERT INTO `region` (`region_id`,`code`) VALUES (28,'LA');
INSERT INTO `region` (`region_id`,`code`) VALUES (29,'ME');
INSERT INTO `region` (`region_id`,`code`) VALUES (30,'MH');
INSERT INTO `region` (`region_id`,`code`) VALUES (31,'MD');
INSERT INTO `region` (`region_id`,`code`) VALUES (32,'MA');
INSERT INTO `region` (`region_id`,`code`) VALUES (33,'MI');
INSERT INTO `region` (`region_id`,`code`) VALUES (34,'MN');
INSERT INTO `region` (`region_id`,`code`) VALUES (35,'MS');
INSERT INTO `region` (`region_id`,`code`) VALUES (36,'MO');
INSERT INTO `region` (`region_id`,`code`) VALUES (37,'MT');
INSERT INTO `region` (`region_id`,`code`) VALUES (38,'NE');
INSERT INTO `region` (`region_id`,`code`) VALUES (39,'NV');
INSERT INTO `region` (`region_id`,`code`) VALUES (40,'NH');
INSERT INTO `region` (`region_id`,`code`) VALUES (41,'NJ');
INSERT INTO `region` (`region_id`,`code`) VALUES (42,'NM');
INSERT INTO `region` (`region_id`,`code`) VALUES (43,'NY');
INSERT INTO `region` (`region_id`,`code`) VALUES (44,'NC');
INSERT INTO `region` (`region_id`,`code`) VALUES (45,'ND');
INSERT INTO `region` (`region_id`,`code`) VALUES (46,'MP');
INSERT INTO `region` (`region_id`,`code`) VALUES (47,'OH');
INSERT INTO `region` (`region_id`,`code`) VALUES (48,'OK');
INSERT INTO `region` (`region_id`,`code`) VALUES (49,'OR');
INSERT INTO `region` (`region_id`,`code`) VALUES (50,'PW');
INSERT INTO `region` (`region_id`,`code`) VALUES (51,'PA');
INSERT INTO `region` (`region_id`,`code`) VALUES (52,'PR');
INSERT INTO `region` (`region_id`,`code`) VALUES (53,'RI');
INSERT INTO `region` (`region_id`,`code`) VALUES (54,'SC');
INSERT INTO `region` (`region_id`,`code`) VALUES (55,'SD');
INSERT INTO `region` (`region_id`,`code`) VALUES (56,'TN');
INSERT INTO `region` (`region_id`,`code`) VALUES (57,'TX');
INSERT INTO `region` (`region_id`,`code`) VALUES (58,'UT');
INSERT INTO `region` (`region_id`,`code`) VALUES (59,'VT');
INSERT INTO `region` (`region_id`,`code`) VALUES (60,'VI');
INSERT INTO `region` (`region_id`,`code`) VALUES (61,'VA');
INSERT INTO `region` (`region_id`,`code`) VALUES (62,'WA');
INSERT INTO `region` (`region_id`,`code`) VALUES (63,'WV');
INSERT INTO `region` (`region_id`,`code`) VALUES (64,'WI');
INSERT INTO `region` (`region_id`,`code`) VALUES (65,'WY');

DROP PROCEDURE IF EXISTS `fetch_city_plus_rates`;

DELIMITER $$
CREATE PROCEDURE `fetch_city_plus_rates`(IN city varchar(28), IN plus_info VARCHAR(100), IN rate DECIMAL(6,5), IN start_from DATE)
BEGIN
SET start_from = IFNULL(start_from, NOW());
SELECT 
  CONCAT('US-NE-CityFips+-', b.fips_place_number, '-', plus_info) AS code,
  'US' AS country,
  s.region_id AS state,
  CONCAT('~~', b.fips_place_number, '-', b.fips_county_code) AS postal_code,
  rate * 100 AS rate
FROM boundaries b
JOIN place p ON b.fips_place_number = p.fips_place_number
JOIN region s ON s.code = 'NE'
WHERE record_type = '4'
  AND start_from BETWEEN b.begin_date AND b.end_date
  AND p.name = city
GROUP BY b.fips_place_number, b.fips_county_code;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_city_rates`;

DELIMITER $$
CREATE PROCEDURE `fetch_city_rates`(IN start_from DATE)
BEGIN
SET start_from = IFNULL(start_from, NOW());
SELECT
  CONCAT('US-NE-CityFips-', b.fips_place_number) AS code,
  'US' AS country,
  s.region_id AS state,
  CONCAT('~~', b.fips_place_number, '-', b.fips_county_code) AS postal_code,
  r.general_tax_rate_intra * 100 AS rate
FROM boundaries b
JOIN rates r ON b.fips_place_number = r.jurisdiction_fips_code AND r.jurisdiction_type = 01
JOIN region s ON s.code = 'NE'
WHERE record_type = '4'
  AND start_from BETWEEN b.begin_date AND b.end_date
  AND start_from BETWEEN r.begin_date AND r.end_date
  AND b.fips_place_number <> ''
GROUP BY b.fips_place_number, b.fips_county_code;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_county_rates`;

DELIMITER $$

CREATE PROCEDURE `fetch_county_rates`(IN start_from DATE)
BEGIN
SET start_from = IFNULL(start_from, NOW());
SELECT 
  CONCAT('US-NE-CountyFips-', b.fips_county_code) AS code,
  'US' AS country,
  s.region_id AS state,
  CONCAT('~~', b.fips_place_number, '-', b.fips_county_code) AS postal_code,
  r.general_tax_rate_intra * 100 AS rate
FROM boundaries b
JOIN rates r ON b.fips_county_code = r.jurisdiction_fips_code AND r.jurisdiction_type = 00
JOIN region s ON s.code = 'NE'
WHERE record_type = '4'
  AND start_from BETWEEN b.begin_date AND b.end_date
  AND start_from BETWEEN r.begin_date AND r.end_date
  AND b.fips_county_code <> ''
GROUP BY b.fips_place_number, b.fips_county_code;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_force_state_rate`;

DELIMITER $$
CREATE PROCEDURE `fetch_force_state_rate`(IN start_from DATE)
BEGIN
SET start_from = IFNULL(start_from, NOW());
SELECT
  CONCAT('US-', s.code) AS code,
  'US' AS county,
  0 AS state,
  '*' AS postal_code,
  r.general_tax_rate_intra * 100 AS rate
FROM region s
JOIN rates r ON s.code = 'NE' AND r.jurisdiction_fips_code = 31 AND r.jurisdiction_type = 45
WHERE start_from BETWEEN r.begin_date AND r.end_date;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_force_city_rate`;

DELIMITER $$

CREATE PROCEDURE `fetch_force_city_rate`(IN city varchar(28), IN start_from DATE)
BEGIN
SET start_from = IFNULL(start_from, NOW());
SELECT
  CONCAT('US-NE-CityFips-', CAST(p.fips_place_number AS CHAR)) AS code,
  'US' AS country,
  0 AS state,
  '*' AS postal_code,
  r.general_tax_rate_intra * 100 AS rate
FROM place p
JOIN rates r ON p.fips_place_number = r.jurisdiction_fips_code AND r.jurisdiction_type = 01
WHERE p.name = city
  AND start_from BETWEEN r.begin_date AND r.end_date;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_state_rates`;

DELIMITER $$
CREATE PROCEDURE `fetch_state_rates`(IN start_from DATE)
BEGIN
SET start_from = IFNULL(start_from, NOW());
SELECT
  CONCAT('US-', s.code, '-*') AS code,
  'US' AS county,
  s.region_id AS state,
  '*' AS postal_code,
  IFNULL(r.general_tax_rate_intra, 0.000) * 100 AS rate
FROM region s
LEFT JOIN rates r ON IF(s.code = 'NE', r.jurisdiction_fips_code = 31 AND r.jurisdiction_type = 45, false)
WHERE (start_from BETWEEN r.begin_date AND r.end_date OR r.begin_date IS NULL);
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_force_city_plus_rate`;

DELIMITER $$

CREATE PROCEDURE `fetch_force_city_plus_rate`(IN city varchar(28), IN plus_info VARCHAR(100), IN rate DECIMAL(6,5))
BEGIN
SELECT
  CONCAT('US-NE-CityFips+-', CAST(p.fips_place_number AS CHAR), '-', plus_info) AS code,
  'US' AS country,
  0 AS state,
  '*' AS postal_code,
  rate * 100 AS rate
FROM place p
WHERE p.name = city;
END$$

DELIMITER ;

DROP PROCEDURE IF EXISTS `fetch_exempt_rates`;

DELIMITER $$

CREATE PROCEDURE `fetch_exempt_rates`()
BEGIN
SELECT
  c.code AS code,
  'US' AS county,
  s.region_id AS state,
  '*' AS postal_code,
  0.000 AS rate
FROM region s
JOIN (
  SELECT 'Resell' AS code
  UNION
  SELECT 'Exempt Org'
  UNION
  SELECT 'Exempt Carrier'
  UNION
  SELECT 'Gov\'t Agency'
  UNION
  SELECT 'Exempt Food'
  UNION
  SELECT 'Nontaxable Services'
  UNION
  SELECT 'Agri feed|seed|chem|fert'
  UNION
  SELECT 'Motor'
  UNION
  SELECT 'Agri mach|equip'
) AS c
WHERE s.code = 'NE';
END$$

DELIMITER ;
