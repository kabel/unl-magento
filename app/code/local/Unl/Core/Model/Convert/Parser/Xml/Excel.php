<?php

class Unl_Core_Model_Convert_Parser_Xml_Excel extends Mage_Dataflow_Model_Convert_Parser_Xml_Excel
{
    /* Overrides the logic of
     * @see Mage_Dataflow_Model_Convert_Parser_Xml_Excel::_getXmlString()
     * by removing the value escaping
     */
    protected function _getXmlString(array $fields = array())
    {
        $xmlHeader = '<?xml version="1.0"?>' . "\n";
        $xmlRegexp = '/^<cell><row>(.*)?<\/row><\/cell>\s?$/ms';

        if (is_null($this->_xmlElement)) {
            $xmlString = $xmlHeader . '<cell><row></row></cell>';
            $this->_xmlElement = new SimpleXMLElement($xmlString, LIBXML_NOBLANKS);
        }

        $xmlData = array();
        $xmlData[] = '<Row>';
        foreach ($fields as $value) {
            $this->_xmlElement->row = $value;
            $value = str_replace($xmlHeader, '', $this->_xmlElement->asXML());
            $value = preg_replace($xmlRegexp, '\\1', $value);
            if (is_numeric($value)) {
                $value = trim($value);
                $dataType = 'Number';
            } else {
                $dataType = 'String';
            }
            $value = str_replace(array("\r\n", "\r", "\n"), '&#10;', $value);

            $xmlData[] = '<Cell><Data ss:Type="' . $dataType . '">' . $value . '</Data></Cell>';
        }
        $xmlData[] = '</Row>';

        return join('', $xmlData);
    }

}
