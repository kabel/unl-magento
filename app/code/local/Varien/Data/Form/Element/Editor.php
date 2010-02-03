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
            /*$wrapper = Mage::helper('core')->getLayout()->getBlockSingleton('unl_core/adminhtml_cms_editor_wrapper')->renderView();
            $wrapper = str_replace(array("\r\n", "\r", "\n"), '', addslashes($wrapper));
            $wrapper = explode('{0}', $wrapper);*/
            
            $element = ($this->getState() == 'html') ? '' : $this->getHtmlId();
            
            /* @var $design Mage_Core_Model_Design_Package */
            $design = Mage::getModel('core/design_package')->setStore(Mage::app()->getDefaultStoreView());
            
            $css = array(
                '/wdn/templates_3.0/css/all.css',
                $design->getSkinUrl('css/reset.css'),
                $design->getSkinUrl('css/styles.css')
            );

            $html = '
                <textarea style="height: 500px" name="'.$this->getName().'" title="'.$this->getTitle().'" id="'.$this->getHtmlId().'" class="textarea '.$this->getClass().'" '.$this->serialize($this->getHtmlAttributes()).' >'.$this->getEscapedValue().'</textarea>
                <script type="text/javascript" src="' . Mage::getBaseUrl('js') . 'tiny_mce/tiny_mce.js"></script>
                <script type="text/javascript">
                //<![CDATA[
                Event.observe(window, "load", function() {
                    tinyMCE.init({
                        //General options
                        mode : "exact",
                        theme : "advanced",
                        skin: "o2k7",
                        elements : "' . $element . '",
                        body_id : "maincontent",
                        body_class : "fixed",
                        plugins : "safari,style,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
                        theme_advanced_blockformats : "p,div,h1,h2,h3,h4,h5,h6,address,pre,code",
                        plugin_preview_pageurl : "' . $design->getSkinUrl('include/preview.shtml') . '",
                        plugin_preview_width : "800",
                        plugin_preview_height : "600",
                        VarienTemplates : {
                            _decodeRegEx : new RegExp("\\{\\{skin url=[\'\\"]([^\'\\"]*)[\'\\"]\\}\\}", "g"),
                            _encodeRegEx : new RegExp("' . $design->getSkinUrl('') . '([^\\\\s\\"\');]*)", "g"),
                            decode : function(c) {
                                var skinUrlRE = this._decodeRegEx;
                                return c.replace(skinUrlRE, "' . $design->getSkinUrl('') . '$1");
                            },
                            encode : function(c) {
                                var skinUrlRE = this._encodeRegEx;
                                return c.replace(skinUrlRE, "{{skin url=\'$1\'}}");
                            }
                        },
                        setup: function(ed) {
                            ed.onBeforeSetContent.add(function(ed, o) {
                                o.content = ed.settings.VarienTemplates.decode(o.content);
                            });
                            ed.onPostProcess.add(function(ed, o) {
                                var isGet = o.get || false;
                                if (isGet) {
                                    if (!o.preview) {
                                        o.content = ed.settings.VarienTemplates.encode(o.content)
                                    }
                                }
                            });
                        },
                        
                        //CSS
                        ' . (!$this->getData('disableCss') ? 'content_css: "' . implode(',', $css) . '",' : '') . '
                        
                        //Theme options
                        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
                        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,fullscreen",
                        theme_advanced_buttons4 : "styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
                        theme_advanced_toolbar_location : "top",
                        theme_advanced_toolbar_align : "left",
                        theme_advanced_path_location : "bottom",
                        theme_advanced_resizing : true,
                        
                        //Cleanup options
                        extended_valid_elements : "' . ($this->getData('disableCss') ? 'style[type]' : '') . '",
                        convert_urls : false,
                        doctype : \'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\'
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
