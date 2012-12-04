<?php

class Unl_Core_Model_Resource_Tax_Boundary_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Official/Common directional abbreviations.
     * Official list from USPS.
     *
     * @var array
     */
    protected $_directionalAbbr = array(
        'N' => array(
            'NORTH'
        ),
        'S' => array(
            'SOUTH',
            'SO' // unofficial
        ),
        'E' => array(
            'EAST'
        ),
        'W' => array(
            'WEST'
        ),
        'NE' => array(
            'NORTHEAST'
        ),
        'NW' => array(
            'NORTHWEST'
        ),
        'SE' => array(
            'SOUTHEAST'
        ),
        'SW' => array(
            'SOUTHWEST'
        ),
    );

    /**
     * A regular expression for matching a directional
     *
     * @var string
     */
    protected $_directionalRegExp = '[ns][ew]?|[ew]|(?:north|so(?:uth)?)(?:east|west)?|east|west';

    /**
     * Official street suffix abbreviations from the USPS.
     *
     * @var array
     */
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

    /**
     * Official secondary address indentifier abbreviations from the USPS.
     *
     * @var array
     */
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

    /**
     * Searches for and returns the matching abbreviation from a given
     * dictionary.
     *
     * Returns false if no abbreviation is found.
     *
     * @param string $token The token to check for abbreviations
     * @param array $search An array with string keys and an array of strings or string values
     * @param boolean $recursive If the passed $seach array contains array values
     * @return string|boolean Returns the corresponding $token abbreviation
     */
    protected function _matchesAbbrArray($token, $search, $recursive = true)
    {
        if (array_key_exists($token, $search)) {
            return $token;
        } else {
            if ($recursive) {
                foreach ($search as $abbr => $alts) {
                    if (in_array($token, $alts)) {
                        return $abbr;
                    }
                }
            } else {
                return array_search($token, $search);
            }
        }

        return false;
    }

    /**
     * Returns the official un-abbreviated version of an abbreviation.
     *
     * @param string $abbr
     * @param array $search An array of arrays of strings, with the first index as the un-abbreviated version
     * @return string
     */
    protected function _reverseAbbr($abbr, $search)
    {
        if (!empty($search[$abbr])) {
            return $search[$abbr][0];
        }

        return $abbr;
    }

    protected function _matchesSecondaryAbbr($piece)
    {
        return $this->_matchesAbbrArray($piece, $this->_secondaryAbbr, false);
    }

    protected function _matchesDirectional($piece)
    {
        return $this->_matchesAbbrArray($piece, $this->_directionalAbbr);
    }

    protected function _reverseDirectional($piece)
    {
        return $this->_reverseAbbr($piece, $this->_directionalAbbr);
    }

    protected function _matchesStreetSuffix($piece)
    {
        return $this->_matchesAbbrArray($piece, $this->_streetSuffixes);
    }

    protected function _reverseSuffix($suffix)
    {
        return $this->_reverseAbbr($suffix, $this->_streetSuffixes);
    }

    /**
     * Returns a formated ZIP+4, given possibly unpadded inputs.
     *
     * @param string|int $code
     * @param string|int $ext
     * @return string
     */
    protected function _formatZip($code, $ext)
    {
        return sprintf('%05s-%04s', $code, $ext);
    }

    /**
     * Removes unecessary characters from a string of an address line
     *
     * @param string $address
     * @return string
     */
    public function sanitizeAddress($address)
    {
        $address = str_replace(array('#', '.', ','), '', $address);
        $address = str_replace(array("\n", "\r", "\t"), ' ', $address);
        $address = str_replace(array('   ', '  '), ' ', $address);

        return $address;
    }

    /**
     * Removes elements from the $filtered array that do not match
     * the provided $name (key) and $value.
     *
     * @param string $name
     * @param mixed $value
     * @param array $filtered
     * @param string $name2
     */
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

    /**
     * Parses a given string for possible tokens to filter by a secondary address
     *
     * @param string $secondary A line of text to match against the secondary address
     * @param array $possib The array of possible search filters to add to
     */
    public function parseSecondary($secondary, &$possib)
    {
        $secondary = $this->sanitizeAddress($secondary);

        $pieces = explode(' ', $secondary);

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

    /**
     * Parses the passed $pieces (tokens) into arrays of searchable filters
     * ($search) and possible additional filters ($possib).
     *
     * @param array $pieces An array of string tokens
     * @param array $search An array to fill with searchable filters
     * @param array $possib An array to fill with possible additional filters
     * @param boolean $strict A flag to enfore strict parsing rules
     */
    public function parsePieces($pieces, &$search, &$possib, $strict = false)
    {
        $street_name = array();
        $addr2Swap = false;

        for ($i = count($pieces) - 1; $i >= 0; $i--) {
            $piece = strtoupper(trim($pieces[$i]));

            if (empty($piece)) {
                continue;
            }

            if ($temp = $this->_matchesSecondaryAbbr($piece)) {
                if (!empty($street_name) || !empty($possib['street_suffix']) || !empty($possib['post-directional'])) {
                    $street_name[] = $piece;
                } else {
                    $possib['secondary_abbr'] = $temp;
                }
            } elseif ($temp = $this->_matchesDirectional($piece)) {
                if (!empty($street_name) || !empty($possib['street_suffix']) && empty($possib['post-directional'])) {
                    $street_name[] = $this->_reverseDirectional($temp);
                } else {
                    $possib['post-directional'] = $temp;
                }
            } elseif ($temp = $this->_matchesStreetSuffix($piece)) {
                if (!empty($possib['street_suffix'])) {
                    $street_name[] = $piece;
                } elseif (!$strict && $i > 0 && !empty($street_name)) {
                    $possib['trash'] = $street_name;
                    if (isset($possib['secondary_addr']) && empty($possib['secondary_abbr'])) {
                        $possib['trash'] = array_merge(array($possib['secondary_addr']), $possib['trash']);
                        unset($possib['secondary_addr']);
                    }
                    $street_name = array();
                    $possib['street_suffix'] = $temp;
                } elseif ($strict && isset($possib['secondary_addr']) && empty($possib['secondary_abbr'])) {
                    $street_name[] = $possib['secondary_addr'];
                    unset($possib['secondary_addr']);
                    $addr2Swap = true;
                    $street_name[] = $this->_reverseSuffix($temp);
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
            } elseif (isset($possib['trash'])) {
                $street_name = $possib['trash'];
            }
        }

//         if ($strict && !$addr2Swap && !empty($possib['secondary_addr'])) {
//             array_unshift($street_name, $possib['secondary_addr']);
//             unset($possib['secondary_addr']);
//         }

        $search['street_name'] = array_reverse($street_name);
    }

    /**
     * Filters loaded items by additional parsed conditions ($possib).
     *
     * Returns the closest matched item OR the first item if the conditions
     * are too specific.
     *
     * @param array $items
     * @param string $secondary An optional secondary address line to parse (ignored if empty)
     * @param array $possib An array of additional conditions
     * @param array $search The array of conditions that returned $items
     * @return mixed
     */
    protected function _runFilters($items, $secondary, $possib, $search)
    {
        $filtered = $items;

        if (!empty($secondary)) {
            $this->parseSecondary($secondary, $possib);
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

    public function initSearchArrays($matches, &$search, &$possib)
    {
        $search['address'] = $matches[1];

        $pre = $this->_matchesDirectional(strtoupper(trim($matches[2])));
        if (!empty($pre)) {
            $possib = array('pre-directional' => $pre);
        }

        return explode(' ', $matches[3]);
    }

    /**
     * Parses an street address line into a searchable format and attempts
     * to load a matching "address record type" row.
     *
     * Returns the closest matching row's formatted ZIP+4 or an empty string.
     *
     * $matches is an array of strings that holds to results of pre-matching:
     * <code>Array(
     *   [0]: full line matched
     *   [1]: primary address number
     *   [2]: [optional] predirectional
     *   [3]: remaining address line tokens
     * )</code>
     *
     * @param Varien_Object $address The address object representing the entire address
     * @param array $matches
     * @param string $secondary
     * @return string
     */
    protected function _runOnMatch($address, $matches, $secondary)
    {
        $correctCity = false;
        $strict = false;
        $adapter = $this->getConnection();
        $now = Mage::getSingleton('core/date')->date('Y-m-d');

        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(new Zend_Db_Expr('COUNT(*)')))
            ->where('record_type = ?', 'A')
            ->where('? BETWEEN begin_date AND end_date', $now)
            ->where('city_name = ?', $address->getCity());
        $cityCount = $adapter->fetchOne($select);

        if (!$cityCount) {
            $select = clone $this->getSelect();
            $select->reset(Zend_Db_Select::COLUMNS)
                ->columns(array(new Zend_Db_Expr('COUNT(*)')))
                ->where('record_type = ?', 'A')
                ->where('? BETWEEN begin_date AND end_date', $now)
                ->where('zip_code = ?', $address->getPostcode());
            $zipCount = $adapter->fetchOne($select);

            if ($zipCount) {
                $correctCity = true;
            }
        }

        while ($cityCount || $zipCount) {
            $this->_reset();
            $search = array();
            $possib = array();
            $street_pieces = $this->initSearchArrays($matches, $search, $possib);

            $this->parsePieces($street_pieces, $search, $possib, $strict);

            $this->addFieldToSelect(array(
                'city_name',
                'zip_code',
                'plus_4',
                'street_pre_directional',
                'street_suffix_abbr',
                'street_post_directional',
                'address_secondary_low',
                'address_secondary_high',
                'odd_even_indicator',
            ));

            $select = $this->getSelect()->where('record_type = ?', 'A')
                ->where('? BETWEEN begin_date AND end_date', $now)
                ->where('? BETWEEN low_address_range AND high_address_range', $search['address'])
                ->where('street_name LIKE ?', implode(' ', $search['street_name']) . '%');

            if (!$cityCount) {
                $select->where('zip_code = ?', $address->getPostcode());
            } else {
                $select->where('city_name = ?', $address->getCity());
            }

            $count = count($this);
            if ($count) {
                if ($count > 1) {
                    $item = $this->_runFilters($this->getItems(), $secondary, $possib, $search);
                } else {
                    $item = current($this->getItems());
                }

                if ($correctCity) {
                    $address->setCity($item['city_name']);
                }

                return $this->_formatZip($item['zip_code'], $item['plus_4']);
            } else {
                if ($strict) {
                    break;
                } else {
                    $strict = true;
                }
            }
        }

        return '';
    }

    /**
     * Get the tax helper instance
     *
     * @return Unl_Core_Helper_Tax
     */
    protected function _getHelper()
    {
        return Mage::helper('unl_core/tax');
    }

    public function getPreMatchExpression()
    {
        return '/(\d+)\s+(?:(' . $this->_directionalRegExp . ')\s+)?([^\W_][a-z\d\-\/\s]*)/i';
    }

    /**
     * Try to find and return the Zip+4 for the provided address
     *
     * @param Mage_Customer_Model_Address_Abstract $address
     * @return Unl_Core_Model_Resource_Tax_Boundary_Collection
     */
    public function validateAddressZip($address)
    {
        if (preg_match('/^(\d{5})(?:-(\d{4}))$/', $address->getPostcode(), $matches)) {
            if ($address->getValidatedZip() == $address->getPostcode()) {
                return $this;
            }

            $address->setPostcode($matches[1]);
        }

        $zip   = '';
        $lines = $address->getStreet();

        if (empty($lines)) {
            return $this;
        }

        foreach ($lines as $line) {
            if (preg_match('/(?:P\.?\s*O\.?\s*)?Box\s+(\d+)/i', $line, $matches)) {
                $zip = $this->_formatZip($address->getPostcode(), substr($matches[1], -4));
                $address->setPostcode($zip);

                return $this;
            }
        }

        $cacheKey = sha1(implode('|', array(
            implode(' ', $lines),
            $address->getCity(),
            $address->getPostcode()
        )));
        $cachedZip = $this->_getHelper()->loadAddressCache($cacheKey);

        if ($cachedZip !== false) {
            if ($cachedZip) {
                $address->setValidatedZip($cachedZip)
                    ->setPostcode($cachedZip);
            }

            return $this;
        }

        foreach ($lines as $i => $line) {
            if ($i == 1) {
                $secondary = $lines[0];
            } else {
                $secondary = isset($lines[1]) ? $lines[1] : '';
            }

            $line = $this->sanitizeAddress($line);

            if (preg_match($this->getPreMatchExpression(), $line, $matches)) {
                $zip = $this->_runOnMatch($address, $matches, $secondary);
            }

            if ($zip) {
                $address->setValidatedZip($zip)
                    ->setPostcode($zip);
                break;
            }
        }

        if (!$zip) {
            try{
                Mage::log('NE tax boundary validation failed for address: ' . $address->format('oneline'), Zend_Log::NOTICE, 'unl_tax.log');
            } catch (Exception $ex) {}
        }

        $this->_getHelper()->saveAddressCache($cacheKey, $zip);

        return $this;
    }

    /**
     * Returns a string formated for fetching tax rates
     *
     * @param Varien_Object $item
     * @return string
     */
    protected function _formatBoundaryFips($item)
    {
        $city = $item->getData('fips_place_number');
        $county = $item->getData('fips_county_code');
        $format = '~~';

        if (empty($city) && empty($county)) {
            return $format;
        }

        if (!empty($city)) {
            $format .= sprintf('%05s', $city);
        }

        $format .= '-';

        if (!empty($county)) {
            $format .= sprintf('%03s', $county);
        }

        return $format;
    }

    /**
     * Translates a given $zip string by search for a matching
     * row with the "zip record type" and/or "zip+4 record type".
     *
     * If the $zip cannot be translated, a notice is logged.
     *
     * The result is cached into memory.
     *
     * @param string $zip
     */
    public function translateZip($zip)
    {
        $result = Mage::helper('unl_core/tax')->zipRegistry($zip);
        if (false === $result) {
            $result = '';
            $originalZip = $zip;
            $now = Mage::getSingleton('core/date')->date('Y-m-d');

            if (preg_match('/^(\d{5})(?:-(\d{4}))$/', $zip, $matches)) {
                $this->_reset();

                $this->addFieldToSelect(array('fips_place_number', 'fips_county_code'));
                $this->getSelect()->where('record_type = ?', '4')
                    ->where('? BETWEEN begin_date AND end_date', $now)
                    ->where('? BETWEEN zip_code_low AND zip_code_high', $matches[1])
                    ->where('? BETWEEN zip_ext_low AND zip_ext_high', $matches[2]);

                if (count($this)) {
                    $result = $this->_formatBoundaryFips($this->getFirstItem());
                } else {
                    $zip = $matches[1];
                }
            }

            if (!$result) {
                $this->_reset();

                $this->addFieldToSelect(array('fips_place_number', 'fips_county_code'));
                $this->getSelect()->where('record_type = ?', 'Z')
                    ->where('? BETWEEN begin_date AND end_date', $now)
                    ->where('? BETWEEN zip_code_low AND zip_code_high', $zip);

                if (count($this)) {
                    $result = $this->_formatBoundaryFips($this->getFirstItem());
                } else {
                    // LOG ERROR
                    Mage::log('NE tax boundary could not translate zip: ' . $originalZip, Zend_Log::NOTICE, 'unl_tax.log');
                }
            }

            Mage::helper('unl_core/tax')->zipRegister($zip, $result);
        }

        return Mage::helper('unl_core/tax')->zipRegistry($zip);
    }
}
