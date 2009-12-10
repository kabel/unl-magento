There are a total of 7 symlinks to get our customizations working in magento. 
The svn projects are labeled:
[Magento] : http://svn.magentocommerce.com/source/branches/1.3
[Unl-Magento] : http://its-gforge.unl.edu/svn/unl-magento

[Magento]/app/code/local/Unl -> [Unl-Magento]/app/code/local/Unl
[Magento]/app/code/local/Varien -> [Unl-Magento]/app/code/local/Varien
[Magento]/app/etc/modules/Unl_All.xml -> [Unl-Magento]/app/etc/modules/Unl_All.xml
[Magento]/skin/frontend/unl -> [Unl-Magento]/skin/frontend/unl
[Magento]/app/design/frontend/unl -> [Unl-Magento]/app/design/frontend/unl
[Magento]/app/design/adminhtml/default/default/template/unl -> [Unl-Magento]/app/design/adminhtml/default/default/template/unl
[Magento]/js/tiny_mce -> [Unl-Magento]/js/tiny_mce

-- OPTIONAL --
[Magento]/app/design/adminhtml/default/default/template/tester -> [Unl-Magento]/app/design/adminhtml/default/default/template/tester  (optional: for testing module)

cd magento
ln -s /path/to/unl-magento/app/code/local/Unl app/code/local/Unl
ln -s /path/to/unl-magento/app/code/local/Varien app/code/local/Varien
ln -s /path/to/unl-magento/app/etc/modules/Unl_All.xml app/etc/modules/Unl_All.xml
ln -s /path/to/unl-magento/skin/frontend/unl skin/frontend/unl
ln -s /path/to/unl-magento/app/design/frontend/unl app/design/frontend/unl
ln -s /path/to/unl-magento/app/design/adminhtml/default/default/template/unl app/design/adminhtml/default/default/template/unl
ln -s /path/to/unl-magento/js/tiny_mce js/tiny_mce

-- OPTIONAL --
ln -s /path/to/unl-magento/app/design/adminhtml/default/default/template/tester app/design/adminhtml/default/default/template/tester

There are two configration settings for the theme to work:
System>Configuration>Design
Package:Current Package Name:unl
Themes:Default:blank

For a production server the configuration settings for "Web", "Store Email Addresses", "Contacts", "Catalog", "Inventory", "Sales", "Shipping ...", and "Payment ..." will need to be set to match the business practices.

For help on general design and configuration refer to the documentation provided by Varien at http://www.magentocommerce.com/

A listing of the events that handlers can be written for is in file dispatcheventlist.xls. It may not be up to date with the current version of Magento.