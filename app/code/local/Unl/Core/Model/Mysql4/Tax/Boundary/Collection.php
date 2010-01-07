<?php

class Unl_Core_Model_Mysql4_Tax_Boundary_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_streetSuffixes = array(
        'ALY' => array(
            'ALLEY',
            'ALLEE',
            'ALLY'
        ),
        'ANX' => array(
            'ANNEX',
            'ANEX',
            'ANNX'
        ),
        'ARC' => array(
            'ARCADE'
        ),
        'AVE' => array(
            'AVENUE',
            'AV',
            'AVEN',
            'AVENU',
            'AVN',
            'AVNUE'
        ),
        'BYU' => array(
            'BAYOO',
            'BAYOU'
        ),
        'BCH' => array(
            'BEACH'
        ),
        'BND' => array(
            'BEND'
        ),
        'BLF' => array(
            'BLUFF',
            'BLUF'
        ),
        'BLFS' => array(
            'BLUFFS'
        ),
        'BTM' => array(
            'BOTTOM',
            'BOT',
            'BOTTM'
        ),
        'BLVD' => array(
            'BOULEVARD',
            'BOUL',
            'BOULV'
        ),
        'BR' => array(
            'BRANCH',
            'BRNCH'
        ),
        'BRG' => array(
            'BRIDGE',
            'BRDGE'
        ),
        'BRK' => array(
            'BROOK'
        ),
        'BRKS' => array(
            'BROOKS'
        ),
        'BG' => array(
            'BURG'
        ),
        'BGS' => array(
            'BURGS'
        ),
        'BYP' => array(
            'BYPASS',
            'BYPA',
            'BYPAS',
            'BYPS'
        ),
        'CP' => array(
            'CAMP',
            'CMP'
        ),
        'CYN' => array(
            'CANYON',
            'CANYN',
            'CNYN'
        ),
        'CPE' => array(
            'CAPE'
        ),
        'CSWY' => array(
            'CAUSEWAY',
            'CAUSWAY'
        ),
        'CTR' => array(
            'CENTER',
            'CEN',
            'CENT',
            'CENTR',
            'CENTRE',
            'CNTER',
            'CNTR'
        ),
        'CTRS' => array(
            'CENTERS'
        ),
        'CIR' => array(
            'CIRCLE',
            'CIRC',
            'CIRCL',
            'CRCL',
            'CRCLE'
        ),
        'CIRS' => array(
            'CIRCLES'
        ),
        'CLF' => array(
            'CLIFF'
        ),
        'CLFS' => array(
            'CLIFFS'
        ),
        'CLB' => array(
            'CLUB'
        ),
        'CMN' => array(
            'COMMON'
        ),
        'COR' => array(
            'CORNER'
        ),
        'CORS' => array(
            'CORNERS'
        ),
        'CRSE' => array(
            'COURSE'
        ),
        'CT' => array(
            'COURT',
            'CRT'
        ),
        'CTS' => array(
            'COURTS'
        ),
        'CV' => array(
            'COVE'
        ),
        'CVS' => array(
            'COVES'
        ),
        'CRK' => array(
            'CREEK',
            'CK',
            'CR'
        ),
        'CRES' => array(
            'CRESCENT',
            'CRECENT',
            'CRESENT',
            'CRSCNT',
            'CRSENT',
            'CRSNT'
        ),
        'CRST' => array(
            'CREST'
        ),
        'XING' => array(
            'CROSSING',
            'CRSSING',
            'CRSSNG'
        ),
        'XRD' => array(
            'CROSSROAD'
        ),
        'CURV' => array(
            'CURVE'
        ),
        'DL' => array(
            'DALE'
        ),
        'DM' => array(
            'DAM'
        ),
        'DV' => array(
            'DIVIDE',
            'DIV',
            'DVD'
        ),
        'DR' => array(
            'DRIVE',
            'DRIV',
            'DRV'
        ),
        'DRS' => array(
            'DRIVES'
        ),
        'EST' => array(
            'ESTATE'
        ),
        'ESTS' => array(
            'ESTATES'
        ),
        'EXPY' => array(
            'EXPRESSWAY',
            'EXP',
            'EXPR',
            'EXPRESS',
            'EXPW'
        ),
        'EXT' => array(
            'EXTENSION',
            'EXTN',
            'EXTNSN'
        ),
        'EXTS' => array(
            'EXTENSIONS'
        ),
        'FALL' => array(
            'FALL'
        ),
        'FLS' => array(
            'FALLS'
        ),
        'FRY' => array(
            'FERRY',
            'FRRY'
        ),
        'FLD' => array(
            'FIELD'
        ),
        'FLDS' => array(
            'FIELDS'
        ),
        'FLT' => array(
            'FLAT'
        ),
        'FLTS' => array(
            'FLATS'
        ),
        'FRD' => array(
            'FORD'
        ),
        'FRDS' => array(
            'FORDS'
        ),
        'FRST' => array(
            'FOREST',
            'FORESTS'
        ),
        'FRG' => array(
            'FORGE',
            'FORG'
        ),
        'FRGS' => array(
            'FORGES'
        ),
        'FRK' => array(
            'FORK'
        ),
        'FRKS' => array(
            'FORKS'
        ),
        'FT' => array(
            'FORT',
            'FRT'
        ),
        'FWY' => array(
            'FREEWAY',
            'FREEWY',
            'FRWAY',
            'FRWY'
        ),
        'GDN' => array(
            'GARDEN',
            'GARDN',
            'GRDEN',
            'GRDN'
        ),
        'GDNS' => array(
            'GARDENS',
            'GRDNS'
        ),
        'GTWY' => array(
            'GATEWAY',
            'GATEWY',
            'GATWAY',
            'GTWAY'
        ),
        'GLN' => array(
            'GLEN'
        ),
        'GLNS' => array(
            'GLENS'
        ),
        'GRN' => array(
            'GREEN'
        ),
        'GRNS' => array(
            'GREENS'
        ),
        'GRV' => array(
            'GROVE',
            'GROV'
        ),
        'GRVS' => array(
            'GROVES'
        ),
        'HBR' => array(
            'HARBOR',
            'HARB',
            'HARBR',
            'HRBOR'
        ),
        'HBRS' => array(
            'HARBORS'
        ),
        'HVN' => array(
            'HAVEN',
            'HAVN'
        ),
        'HTS' => array(
            'HEIGHTS',
            'HEIGHT',
            'HGTS',
            'HT'
        ),
        'HWY' => array(
            'HIGHWAY',
            'HIGHWY',
            'HIWAY',
            'HIWY',
            'HWAY'
        ),
        'HL' => array(
            'HILL'
        ),
        'HLS' => array(
            'HILLS'
        ),
        'HOLW' => array(
            'HOLLOW',
            'HLLW',
            'HOLLOWS',
            'HOLWS'
        ),
        'INLT' => array(
            'INLET'
        ),
        'IS' => array(
            'ISLAND',
            'ISLND'
        ),
        'ISS' => array(
            'ISLANDS',
            'ISLNDS'
        ),
        'ISLE' => array(
            'ISLE',
            'ISLES'
        ),
        'JCT' => array(
            'JUNCTION',
            'JCTION',
            'JCTN',
            'JUNCTN',
            'JUNCTON'
        ),
        'JCTS' => array(
            'JUNCTIONS',
            'JCTNS'
        ),
        'KY' => array(
            'KEY'
        ),
        'KYS' => array(
            'KEYS'
        ),
        'KNL' => array(
            'KNOLL',
            'KNOL'
        ),
        'KNLS' => array(
            'KNOLLS'
        ),
        'LK' => array(
            'LAKE'
        ),
        'LKS' => array(
            'LAKES'
        ),
        'LAND' => array(
            'LAND'
        ),
        'LNDG' => array(
            'LANDING',
            'LNDNG'
        ),
        'LN' => array(
            'LANE',
            'LA',
            'LANES'
        ),
        'LGT' => array(
            'LIGHT'
        ),
        'LGTS' => array(
            'LIGHTS'
        ),
        'LF' => array(
            'LOAF'
        ),
        'LCK' => array(
            'LOCK'
        ),
        'LCKS' => array(
            'LOCKS'
        ),
        'LDG' => array(
            'LODGE',
            'LDGE',
            'LODG'
        ),
        'LOOP' => array(
            'LOOP',
            'LOOPS'
        ),
        'MALL' => array(
            'MALL'
        ),
        'MNR' => array(
            'MANOR'
        ),
        'MNRS' => array(
            'MANORS'
        ),
        'MDW' => array(
            'MEADOW'
        ),
        'MDWS' => array(
            'MEADOWS',
            'MEDOWS'
        ),
        'MEWS' => array(
            'MEWS'
        ),
        'ML' => array(
            'MILL'
        ),
        'MLS' => array(
            'MILLS'
        ),
        'MSN' => array(
            'MISSION',
            'MISSN',
            'MSSN'
        ),
        'MTWY' => array(
            'MOTORWAY'
        ),
        'MT' => array(
            'MOUNT',
            'MNT'
        ),
        'MTN' => array(
            'MOUNTAIN',
            'MNTAIN',
            'MNTN',
            'MOUNTIN',
            'MTIN'
        ),
        'MTNS' => array(
            'MOUNTAINS',
            'MNTNS'
        ),
        'NCK' => array(
            'NECK'
        ),
        'ORCH' => array(
            'ORCHARD',
            'ORCHRD'
        ),
        'OVAL' => array(
            'OVAL',
            'OVL'
        ),
        'OPAS' => array(
            'OVERPASS'
        ),
        'PARK' => array(
            'PARK',
            'PK',
            'PRK',
            'PARKS'
        ),
        'PKWY' => array(
            'PARKWAY',
            'PARKWY',
            'PKWAY',
            'PKY',
            'PARKWAYS',
            'PKWYS'
        ),
        'PASS' => array(
            'PASS'
        ),
        'PSGE' => array(
            'PASSAGE'
        ),
        'PATH' => array(
            'PATH',
            'PATHS'
        ),
        'PIKE' => array(
            'PIKE',
            'PIKES'
        ),
        'PNE' => array(
            'PINE'
        ),
        'PNES' => array(
            'PINES'
        ),
        'PL' => array(
            'PLACE'
        ),
        'PLN' => array(
            'PLAIN'
        ),
        'PLNS' => array(
            'PLAINS',
            'PLAINES'
        ),
        'PLZ' => array(
            'PLAZA',
            'PLZA'
        ),
        'PT' => array(
            'POINT'
        ),
        'PTS' => array(
            'POINTS'
        ),
        'PRT' => array(
            'PORT'
        ),
        'PRTS' => array(
            'PORTS'
        ),
        'PR' => array(
            'PRAIRIE',
            'PRARIE',
            'PRR'
        ),
        'RADL' => array(
            'RADIAL',
            'RAD',
            'RADIEL'
        ),
        'RAMP' => array(
            'RAMP'
        ),
        'RNCH' => array(
            'RANCH',
            'RANCHES',
            'RNCHS'
        ),
        'RPD' => array(
            'RAPID'
        ),
        'RPDS' => array(
            'RAPIDS'
        ),
        'RST' => array(
            'REST'
        ),
        'RDG' => array(
            'RIDGE',
            'RDGE'
        ),
        'RDGS' => array(
            'RIDGES'
        ),
        'RIV' => array(
            'RIVER',
            'RIVR',
            'RVR'
        ),
        'RD' => array(
            'ROAD'
        ),
        'RDS' => array(
            'ROADS'
        ),
        'RTE' => array(
            'ROUTE'
        ),
        'ROW' => array(
            'ROW'
        ),
        'RUE' => array(
            'RUE'
        ),
        'RUN' => array(
            'RUN'
        ),
        'SHL' => array(
            'SHOAL'
        ),
        'SHLS' => array(
            'SHOALS'
        ),
        'SHR' => array(
            'SHORE',
            'SHOAR'
        ),
        'SHRS' => array(
            'SHORES',
            'SHOARS'
        ),
        'SKWY' => array(
            'SKYWAY'
        ),
        'SPG' => array(
            'SPRING',
            'SPNG',
            'SPRNG'
        ),
        'SPGS' => array(
            'SPRINGS',
            'SPNGS',
            'SPRNGS'
        ),
        'SPUR' => array(
            'SPUR',
            'SPURS'
        ),
        'SQ' => array(
            'SQUARE',
            'SQR',
            'SQRE',
            'SQU'
        ),
        'SQS' => array(
            'SQUARES',
            'SQRS'
        ),
        'STA' => array(
            'STATION',
            'STATN',
            'STN'
        ),
        'STRA' => array(
            'STRAVENUE',
            'STRAV',
            'STRAVE',
            'STRAVEN',
            'STRAVN',
            'STRVN',
            'STRVNUE'
        ),
        'STRM' => array(
            'STREAM',
            'STREME'
        ),
        'ST' => array(
            'STREET',
            'STR',
            'STRT'
        ),
        'STS' => array(
            'STREETS'
        ),
        'SMT' => array(
            'SUMMIT',
            'SUMIT',
            'SUMITT'
        ),
        'TER' => array(
            'TERRACE',
            'TERR'
        ),
        'TRWY' => array(
            'THROUGHWAY'
        ),
        'TRCE' => array(
            'TRACE',
            'TRACES'
        ),
        'TRAK' => array(
            'TRACK',
            'TRACKS',
            'TRK',
            'TRKS'
        ),
        'TRFY' => array(
            'TRAFFICWAY'
        ),
        'TRL' => array(
            'TRAIL',
            'TR',
            'TRAILS',
            'TRLS'
        ),
        'TUNL' => array(
            'TUNNEL',
            'TUNEL',
            'TUNLS',
            'TUNNELS',
            'TUNNL'
        ),
        'TPKE' => array(
            'TURNPIKE',
            'TPK',
            'TRNPK',
            'TRPK',
            'TURNPK'
        ),
        'UPAS' => array(
            'UNDERPASS'
        ),
        'UN' => array(
            'UNION'
        ),
        'UNS' => array(
            'UNIONS'
        ),
        'VLY' => array(
            'VALLEY',
            'VALLY',
            'VLLY'
        ),
        'VLYS' => array(
            'VALLEYS'
        ),
        'VIA' => array(
            'VIADUCT',
            'VDCT',
            'VIADCT'
        ),
        'VW' => array(
            'VIEW'
        ),
        'VWS' => array(
            'VIEWS'
        ),
        'VLG' => array(
            'VILLAGE',
            'VILL',
            'VILLAG',
            'VILLG',
            'VILLIAGE'
        ),
        'VLGS' => array(
            'VILLAGES'
        ),
        'VL' => array(
            'VILLE'
        ),
        'VIS' => array(
            'VISTA',
            'VIST',
            'VST',
            'VSTA'
        ),
        'WALK' => array(
            'WALK',
            'WALKS'
        ),
        'WALL' => array(
            'WALL'
        ),
        'WAY' => array(
            'WAY',
            'WY'
        ),
        'WAYS' => array(
            'WAYS'
        ),
        'WL' => array(
            'WELL'
        ),
        'WLS' => array(
            'WELLS'
        )
    );
    
    protected $_secondaryAbbr = array(
        'APT'  => 'APARTMENT',
        'BSMT' => 'BASEMENT',
        'BLDG' => 'BUILDING',
        'DEPT' => 'DEPARTMENT',
        'FL'   => 'FLOOR',
        'FRNT' => 'FRONT',
        'HNGR' => 'HANGAR',
        'LBBY' => 'LOBBY',
        'LOT'  => 'LOT',
        'LOWR' => 'LOWER',
        'OFC'  => 'OFFICE',
        'PH'   => 'PENTHOUSE',
        'PIER' => 'PIER',
        'REAR' => 'REAR',
        'RM'   => 'ROOM',
        'SIDE' => 'SIDE',
        'SLIP' => 'SLIP',
        'SPC'  => 'SPACE',
        'STOP' => 'STOP',
        'STE'  => 'SUITE',
        'TRLR' => 'TRAILER',
        'UNIT' => 'UNIT',
        'UPPR' => 'UPPER'
    );
    
    protected function _construct()
    {
        $this->_init('unl_core/tax_boundary');
    }
    
    protected function _matchesSecondaryAbbr($piece)
    {
        if (array_key_exists($piece, $this->_secondaryAbbr)) {
            return $piece;
        } else {
            return array_search($piece, $this->_secondaryAbbr);
        }
    }
    
    protected function _matchesStreetSuffix($piece)
    {
        if (array_key_exists($piece, $this->_streetSuffixes)) {
            return $piece;
        } else {
            foreach ($this->_streetSuffixes as $suffix => $alts) {
                if (in_array($piece, $alts)) {
                    return $suffix;
                }
            }
        }
        
        return false;
    }
    
    protected function _reverseSuffix($suffix)
    {
        if (!empty($this->_streetSuffixes[$suffix])) {
            return $this->_streetSuffixes[$suffix][0];
        }
    }
    
    protected function _processPieces($pieces, &$search, &$possib, $strict = false)
    {
        $street_name = array();
        $addr2Swap = false;
        for ($i = count($pieces) - 1; $i >= 0; $i--) {
            $piece = strtoupper(trim($pieces[$i]));
            if (empty($piece)) { continue; }
                    
            if ($temp = $this->_matchesSecondaryAbbr($piece)) {
                if (!empty($street_name) || !empty($possib['street_suffix']) || !empty($possib['post-directional'])) {
                    $street_name[] = $piece;
                } else {
                    $possib['secondary_abbr'] = $temp;
                }
            } elseif (preg_match('/^([ns][ew]?|[ew])$/i', $piece)) {
                if (!empty($street_name) || !empty($possib['street_suffix']) && empty($possib['post-directional'])) {
                    $street_name[] = $piece;
                } else {
                    $possib['post-directional'] = $piece;
                }
            } elseif ($temp = $this->_matchesStreetSuffix($piece)) {              
                if (!empty($street_name) || !empty($possib['street_suffix'])) {
                    $street_name[] = $piece;
                } elseif ($strict && isset($possib['secondary_addr']) && empty($possib['secondary_abbr'])) {
                    $street_name[] = $possib['secondary_addr'];
                    unset($possib['secondary_addr']);
                    $addr2Swap = true;
                    $street_name[] = $piece;
                } else {
                    $possib['street_suffix'] = $temp;
                }
            } else {
                if (!empty($street_name) || !empty($possib['street_suffix']) || !empty($possib['post-directional']) || !empty($possib['secondary_abbr'])) {
                    $street_name[] = $piece;
                } else {
                    if (isset($possib['secondary_addr'])) {
                        if ($possib['secondary_addr'] != '1/2') {
                            $street_name[] = $piece;
                        } else {
                            $possib['secondary_addr'] = $piece . ' ' . $possib['secondary_addr'];
                        }
                    } else {
                        $possib['secondary_addr'] = $piece;
                    }
                }
            }
        }
        
        if (empty($street_name)) {
            if (!empty($possib['street_suffix'])) {
                $street_name[] = $this->_reverseSuffix($possib['street_suffix']);
                unset($possib['street_suffix']);
            } elseif (!empty($possib['post-directional'])) {
                $street_name[] = $possib['post-directional'];
                unset($possib['post-directional']);
            } elseif (empty($possib['secondary_abbr']) && !empty($possib['secondary_addr'])) {
                $street_name[] = $possib['secondary_addr'];
                unset($possib['secondary_addr']);
            } elseif (isset($possib['pre-directional'])) {
                $street_name[] = $possib['pre-directional'];
                unset($possib['pre-directional']);
            }
        }
        
        if ($strict && !$addr2Swap && !empty($possib['secondary_addr'])) {
            array_unshift($street_name, $possib['secondary_addr']);
            unset($possib['secondary_addr']);
        }
        
        $search['street_name'] = array_reverse($street_name);
    }
    
    protected function _processSecondary($addr2, &$possib)
    {
        $addr2 = str_replace(array('#', '.'), '', $addr2);
        $addr2 = str_replace(array("\n", "\r", "\t"), ' ', $addr2);
        $pieces = explode(' ', $addr2);
        
        foreach ($pieces as $piece) {
            $piece = strtoupper(trim($piece));
            if (empty($piece)) { continue; }
            
            if ($temp = $this->_matchesSecondaryAbbr($piece)) {
                $possib['secondary_abbr'] = $temp;
            } else {
                $possib['secondary_addr'] = $piece;
            }
        }
    }
    
    protected function _runFilters($items, $addr2, $possib, $search)
    {
        $filtered = $items;
        if (!empty($addr2)) {
            $this->_processSecondary($addr2, $possib);
        }
        
        if (!empty($possib)) {
            foreach ($possib as $key => $value) {
                switch ($key) {
                    case 'pre-directional':
                        $this->_applyFilter('street_pre_directional', $value, $filtered);
                        break;
                    case 'street_suffix':
                        $this->_applyFilter('street_suffix_abbr', $value, $filtered);
                        break;
                    case 'post-directional':
                        $this->_applyFilter('street_post_directional', $value, $filtered);
                        break;
                    case 'secondary_abbr':
                        $this->_applyFilter('address_secondary_abbr', $value, $filtered);
                        break;
                    case 'secondary_addr':
                        $this->_applyFilter('address_secondary_low', $value, $filtered, 'address_secondary_high');
                        break;
                }
            }
        }
        
        if (count($filtered) > 1) {
            $value = array('B');
            if ($search['address'] % 2 == 0) {
                $value[] = 'E';
            } else {
                $value[] = 'O';
            }
            $this->_applyFilter('odd_even_indicator', $value, $filtered);
        }
        
        if (empty($filtered)) {
            return current($items);
        } else {
            return array_shift($filtered);
        }
    }
    
    protected function _applyFilter($name, $value, &$filtered, $name2 = null)
    {
        foreach ($filtered as $key => $item) {
            $keep = false;
            if (!empty($name2)) {
                $keep = ($item[$name] <= $value && $value <= $item[$name2]);
            } else {
                if (is_array($value)) {
                    $keep = in_array($item[$name], $value);
                } else {
                    $keep = ($item[$name] == $value);
                }
            }
            
            if (!$keep) {
                unset($filtered[$key]);
            }
        }
    }
    
    protected function _runOnMatch($address, $matches, $strict = false, $zipInstead = false)
    {
        $this->_reset();
        $addr = $address->getStreet();
        $search = array('address' => $matches[1]);
        $possib = array();
        
        $pre = trim($matches[2]);
        if (!empty($pre)) {
            $possib = array('pre-directional' => $pre);
        }
        
        $street_pieces = explode(' ', $matches[3]);
        $this->_processPieces($street_pieces, $search, $possib, $strict);
        
        $select = $this->getSelect()->where('record_type = ?', 'A')
            ->where('NOW() BETWEEN begin_date AND end_date')
            ->where('? BETWEEN low_address_range AND high_address_range', $search['address'])
            ->where('street_name = ?', implode(' ', $search['street_name']));
            
        if ($zipInstead) {
            $select->where('zip_code = ?', $address->getPostcode());
        } else {
            $select->where('city_name = ?', $address->getCity());
        }
            
        $count = count($this);
        if ($count) {
            if ($count > 1) {
                $item = $this->_runFilters($this->getItems(), isset($addr[1]) ? $addr[1] : '', $possib, $search);
            } else {
                $item = current($this->getItems());
            }
            
            return $item['zip_code'] . '-' . $item['plus_4'];
        } else {
            if (!$strict) {
                return $this->_runOnMatch($address, $matches, true, $zipInstead);
            } elseif (!$zipInstead) {
                return $this->_runOnMatch($address, $matches, false, true);
            }
        }
        
        return '';
    }
    
    public function getZipFromAddress($address)
    {
        $zip = '';
        $addr = $address->getStreet();
        
        $addr[0] = str_replace(array('#', '.'), '', $addr[0]);
        $addr[0] = str_replace(array("\n", "\r", "\t"), ' ', $addr[0]);
        if (preg_match('/(\d+)\s([ns][ew]?\s|[ew]\s)?([^\W_][a-z\d\-\/\s]*)/i', $addr[0], $matches)) {
            $zip = $this->_runOnMatch($address, $matches);
        }
        
        return $zip;
    }
}