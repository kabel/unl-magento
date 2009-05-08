<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form editor element
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Data_Form_Element_Editor extends Varien_Data_Form_Element_Textarea
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        if( $this->getWysiwyg() === true )
        {
            $this->setType('wysiwyg');
            $this->setExtType('wysiwyg');
        }
        else
        {
            $this->setType('textarea');
            $this->setExtType('textarea');
        }
    }

    public function getElementHtml()
    {
        if( $this->getWysiwyg() === true )
        {
            $element = ($this->getState() == 'html') ? '' : $this->getHtmlId();
            $stores = Mage::app()->getStores();
            $frontStore = array_shift($stores);

            $html = '
                <textarea name="'.$this->getName().'" title="'.$this->getTitle().'" id="'.$this->getHtmlId().'" class="textarea '.$this->getClass().'" '.$this->serialize($this->getHtmlAttributes()).' >'.$this->getEscapedValue().'</textarea>
                <script type="text/javascript" src="' . Mage::getBaseUrl('js') . 'tiny_mce/tiny_mce_src.js"></script>
                <script type="text/javascript">
                //<![CDATA[
                Event.observe(window, "load", function() {
                    tinyMCE.init({
                        //General options
                        mode : "exact",
                        theme : "advanced",
                        elements : "' . $element . '",
                        plugins : "safari,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
                        setup: function(ed) {
                            ed.onBeforeSetContent.add(function(ed, o) {
                                var skinUrlRE = new RegExp("\\{\\{skin url=[\'\\"]([^\'\\"]*)[\'\\"]\\}\\}", "g");
                                o.content = o.content.replace(skinUrlRE, "' . Mage::getDesign()->getSkinUrl('', array('_area' => 'frontend', '_package' => Mage::getStoreConfig('design/package/name', $frontStore), '_theme' => Mage::getStoreConfig('design/theme/default', $frontStore))) . '$1");
                            });
                            ed.onPostProcess.add(function(ed, o) {
                                var isGet = o.get || false;
                                if (isGet) {
                                    var skinUrlRE = new RegExp("' . Mage::getDesign()->getSkinUrl('', array('_area' => 'frontend', '_package' => Mage::getStoreConfig('design/package/name', $frontStore), '_theme' => Mage::getStoreConfig('design/theme/default', $frontStore))) . '([^\\\\s\\"\');]*)", "g")
                                    o.content = o.content.replace(skinUrlRE, "{{skin url=\'$1\'}}");
                                }
                            });
                        },
                        
                        //CSS
                        ' . (!$this->getData('disableCss') ? 'content_css: "/ucomm/templatedependents/templatecss/layouts/main.css,' . Mage::getDesign()->getSkinUrl('css/reset.css', array('_area' => 'frontend', '_package' => 'unl', '_theme' => 'modern')) . ',' . Mage::getDesign()->getSkinUrl('css/boxes.css', array('_area' => 'frontend', '_package' => 'unl', '_theme' => 'modern')) . ',' . Mage::getDesign()->getSkinUrl('css/custom.css', array('_area' => 'frontend', '_package' => 'unl', '_theme' => 'modern')) . '",' : '') . '
                        
                        //Theme options
                        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
                        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
                        theme_advanced_toolbar_location : "top",
                        theme_advanced_toolbar_align : "left",
                        theme_advanced_path_location : "bottom",
                        theme_advanced_resizing : true,
                        
                        //Cleanup options
                        extended_valid_elements : "' . ($this->getData('disableCss') ? 'style[type]' : '') . '",
                        convert_urls : false,
                        doctype : \'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\'
                    });
                });
                //]]>
                </script>';

                /*plugins : "inlinepopups,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,zoom,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
                theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking"*/

            $html.= $this->getAfterElementHtml();
            return $html;
        }
        else
        {
            return parent::getElementHtml();
        }
    }

    public function getTheme()
    {
        if(!$this->hasData('theme')) {
            return 'simple';
        }

        return $this->getData('theme');
    }
}
