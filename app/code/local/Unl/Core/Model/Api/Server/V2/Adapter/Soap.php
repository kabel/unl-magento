<?php

class Unl_Core_Model_Api_Server_V2_Adapter_Soap extends Mage_Api_Model_Server_V2_Adapter_Soap
{
    /* Overrides
     * @see Mage_Api_Model_Server_V2_Adapter_Soap::run()
     * by forcing content-type header to be replaced during normal requests
     */
    public function run()
    {
        $apiConfigCharset = Mage::getStoreConfig("api/config/charset");

        if ($this->getController()->getRequest()->getParam('wsdl') !== null) {
            $wsdlConfig = Mage::getModel('api/wsdl_config');
            $wsdlConfig->setHandler($this->getHandler())
                ->init();
            $this->getController()->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type','text/xml; charset='.$apiConfigCharset)
                ->setBody(
                    preg_replace(
                        '/<\?xml version="([^\"]+)"([^\>]+)>/i',
                        '<?xml version="$1" encoding="'.$apiConfigCharset.'"?>',
                        $wsdlConfig->getWsdlContent()
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
