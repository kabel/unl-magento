<?php

class Unl_Core_Model_Widget_Widget_Config extends Mage_Widget_Model_Widget_Config
{
    /**
     * Return Widgets Insertion Plugin Window URL
     *
     * @param Varien_Object Editor element config
     * @return string
     */
    /*
     * Overrides the logic of
     * @see Mage_Widget_Model_Widget_Config::getWidgetWindowUrl()
     * by getting a URL that does not always originate from adminhtml
     */
    public function getWidgetWindowUrl($config)
    {
        $params = array();

        $skipped = is_array($config->getData('skip_widgets')) ? $config->getData('skip_widgets') : array();
        if ($config->hasData('widget_filters')) {
            $all = Mage::getModel('widget/widget')->getWidgetsXml();
            $filtered = Mage::getModel('widget/widget')->getWidgetsXml($config->getData('widget_filters'));
            $reflection = new ReflectionObject($filtered);
            foreach ($all as $code => $widget) {
                if (!$reflection->hasProperty($code)) {
                    $skipped[] = $widget->getAttribute('type');
                }
            }
        }

        if (count($skipped) > 0) {
            $params['skip_widgets'] = $this->encodeWidgetsToQuery($skipped);
        }
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index', $params);
    }
}
