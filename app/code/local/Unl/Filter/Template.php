<?php

class Unl_Filter_Template extends Varien_Filter_Template
{
    const CONSTRUCTION_IF_PATTERN = '/{{if\s*(.*?)}}(.*?)((?:{{elseif\s*.*?}}.*?)*)({{else}}(.*?))?{{\\/if\s*}}/si';
    const CONSTRUCTION_ELSEIF_INNER_PATTERN = '/{{elseif\s*(.*?)}}(.*?)(?={{elseif|$)/si';

    /* Copies
     * @see Varien_Filter_Template::filter()
     * to force late static binding
     */
    public function filter($value)
    {
        // "depend" and "if" operands should be first
        foreach (array(
            self::CONSTRUCTION_DEPEND_PATTERN => 'dependDirective',
            self::CONSTRUCTION_IF_PATTERN     => 'ifDirective',
        ) as $pattern => $directive) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach($constructions as $index => $construction) {
                    $replacedValue = '';
                    $callback = array($this, $directive);
                    if(!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        if(preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach($constructions as $index=>$construction) {
                $replacedValue = '';
                $callback = array($this, $construction[1].'Directive');
                if(!is_callable($callback)) {
                    continue;
                }
                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (Exception $e) {
                    throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }
        return $value;
    }

    public function ifDirective($construction)
    {
        if (count($this->_templateVars) == 0) {
            return $construction[0];
        }

        if($this->_getVariable($construction[1], '') == '') {
            if (isset($construction[3])) {
                preg_match_all(self::CONSTRUCTION_ELSEIF_INNER_PATTERN, $construction[3], $constructions, PREG_SET_ORDER);
                foreach ($constructions as $index => $innerConstruction) {
                    if ($this->_getVariable($innerConstruction[1], '') != '') {
                        return $innerConstruction[2];
                    }
                }
            }

            if (isset($construction[4]) && isset($construction[5])) {
                return $construction[5];
            }
            return '';
        } else {
            return $construction[2];
        }
    }
}
