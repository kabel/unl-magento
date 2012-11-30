<?php

class Unl_Spam_Block_Adminhtml_Widget_Grid_Column_Filter_Cidr extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        $value = explode('/', $this->getValue(), 2);

        $ip = @inet_pton($value[0]);
        $mask = false;

        if ($ip === false) {
            if (preg_match('/[a-f:]/i', $value[0])) {
                $groups = array_filter(explode(':', $value[0], 8));
                $mask = 16 * count($groups);
                if ($value[0][strlen($value[0]) -1 ] == ':') {
                    $ip = @inet_pton($value[0] . ':');
                } else {
                    $ip = @inet_pton($value[0] . '::');
                }
            } else {
                $groups = explode('.', $value[0], 4);
                $mask = 8 * count($groups);
                $ip = @inet_pton(implode('.', ($groups + array('0', '0', '0', '0'))));
            }
        }

        if ($ip == false) {
            $this->setValue('');
            return null;
        }

        $maskLength = strlen($ip) * 8;
        if ($mask || isset($value[1])) {
            $mask = isset($value[1]) ? $value[1] : $mask;
            if ($mask >= $maskLength) {
                $mask = false;
            } else {
                $mask = Mage::helper('unl_spam')->getCidrMask($mask, $maskLength);
            }
        }

        //TODO: Fix this for IPv6

        $cond = array();
        if ($mask) {
            $ip = $ip & $mask;
            $connection = $this->getColumn()->getGrid()->getCollection()->getConnection();
            $cond['field_expr'] = '(CONV(HEX(#?),16,10) & CONV(HEX(' . $connection->quote($mask) . '),16,10))';
        }
        $cond['eq'] = new Zend_Db_Expr($connection->quoteInto('CONV(HEX(?),16,10)', $ip));
        return $cond;
    }
}
