There are a few symlinks to get our customizations working in magento.

!!WARNING!!
The 1.3 branch of magento is NOT compatible with PHP 5.3 and there are some security issues with the JavaScript proxy and symlinks.
A patch ( magento1.3.patch ) has been included to run in the magento project space to correct these various issues.

cd /path/to/magento
patch < /path/to/unl-magento/magento1.3.patch


[magento] : http://svn.magentocommerce.com/source/branches/1.3
[unl-magento] : http://its-gforge.unl.edu/svn/unl-magento

cd /path/to/magento
ln -s /path/to/unl-magento/app/code/local/Unl app/code/local/Unl
ln -s /path/to/unl-magento/app/code/local/Varien app/code/local/Varien
ln -s /path/to/unl-magento/app/etc/modules/Unl_All.xml app/etc/modules/Unl_All.xml
ln -s /path/to/unl-magento/skin/frontend/unl skin/frontend/unl
ln -s /path/to/unl-magento/app/design/frontend/unl app/design/frontend/unl
ln -s /path/to/unl-magento/app/design/adminhtml/default/unl/ app/design/adminhtml/default/unl
ln -s /path/to/unl-magento/js/tiny_mce js/tiny_mce
ln -s /path/to/unl-magento/lib/SimpleCAS lib/SimpleCAS
ln -s /path/to/unl-magento/lib/SimpleCAS.php lib/SimpleCAS.php
ln -s /path/to/unl-magento/lib/UNL lib/UNL

ln -s /path/to/unl-magento/app/etc/modules/Zenprint_Xajax.xml app/etc/modules/
ln -s /path/to/unl-magento/app/etc/modules/Zenprint_Ordership.xml app/etc/modules/
ln -s /path/to/unl-magento/app/code/community/Zenprint app/etc/code/community/
ln -s /path/to/unl-magento/js/xajax_js js/
ln -s /path/to/unl-magento/lib/Xajax lib/


-- OPTIONAL --
ln -s /path/to/unl-magento/app/design/adminhtml/default/default/template/tester app/design/adminhtml/default/default/template/tester

There are two configration settings for the theme to work:
System>Configuration>Design
Package:Current Package Name:unl

The security features on the JavaScript Proxy ( /path/to/magento/js/index.php ) prevent files that outside the "real" js directory (this includes symlinked files)
To get the proxy to include the symlinked folder line 77 should be changed to
    if (strpos($fileRealPath, realpath(dirname(__FILE__))) !== 0 && strpos($fileRealPath, realpath(dirname(__FILE__).'/tiny_mce')) !== 0) {

For a production server the configuration settings for "Web", "Store Email Addresses", "Contacts", "Catalog", "Inventory", "Sales", "Shipping ...", and "Payment ..." will need to be set to match the business practices.

For help on general design and configuration refer to the documentation provided by Varien at http://www.magentocommerce.com/

A listing of the events that handlers can be written for is in file dispatcheventlist.xls. It may not be up to date with the current version of Magento.