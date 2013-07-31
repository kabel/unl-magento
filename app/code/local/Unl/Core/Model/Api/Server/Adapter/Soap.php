<?php

class Unl_Core_Model_Api_Server_Adapter_Soap extends Mage_Api_Model_Server_Adapter_Soap
{
    /* Overrides
     * @see Mage_Api_Model_Server_Adapter_Soap::run()
     * by always replacing the content-type header for normal requests
     */
    public function run()
    {
        $apiConfigCharset = Mage::getStoreConfig("api/config/charset");

        if ($this->getController()->getRequest()->getParam('wsdl') !== null) {
            // Generating wsdl content from template
            $io = new Varien_Io_File();
            $io->open(array('path'=>Mage::getModuleDir('etc', 'Mage_Api')));

            $wsdlContent = $io->read('wsdl.xml');

            $template = Mage::getModel('core/email_template_filter');

            $wsdlConfig = new Varien_Object();
            $queryParams = $this->getController()->getRequest()->getQuery();
            if (isset($queryParams['wsdl'])) {
                unset($queryParams['wsdl']);
            }

            $wsdlConfig->setUrl(htmlspecialchars(Mage::getUrl('*/*/*', array('_query'=>$queryParams))));
            $wsdlConfig->setName('Magento');
            $wsdlConfig->setHandler($this->getHandler());

            $template->setVariables(array('wsdl' => $wsdlConfig));

            $this->getController()->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type','text/xml; charset='.$apiConfigCharset)
                ->setBody(
                    preg_replace(
                        '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                        '<?xml version="$1" encoding="'.$apiConfigCharset.'"?>',
                        $template->filter($wsdlContent)
                    )
                );
        } else {
            try {
                $this->_instantiateServer();

                $this->getController()->getResponse()
                    ->clearHeaders()
                    ->setHeader('Content-Type','text/xml; charset='.$apiConfigCharset, true)
                    ->setBody(
                        preg_replace(
                            '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                            '<?xml version="$1" encoding="'.$apiConfigCharset.'"?>',
                            $this->_soap->handle()
                        )
                    );
            } catch( Zend_Soap_Server_Exception $e ) {
                $this->fault( $e->getCode(), $e->getMessage() );
            } catch( Exception $e ) {
                $this->fault( $e->getCode(), $e->getMessage() );
            }
        }

        return $this;
    }
}
