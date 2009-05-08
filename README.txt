There are a total of 5 symlinks to get our customizations working in magento. 
The svn projects are labeled:
[Magento] : http://svn.magentocommerce.com/source/branches/1.3
[Unl-Magento] : http://its-gforge.unl.edu/svn/unl-magento

[Magento]/app/code/local/Unl -> [Unl-Magento]/app/code/local/Unl
[Magento]/app/etc/modules/Unl_All.xml -> [Unl-Magento]/app/etc/modules/Unl_All.xml
[Magento]/app/design/frontend/unl -> [Unl-Magento]/app/design/frontend/unl
[Magento]/app/design/adminhtml/default/default/template/unl -> [Unl-Magento]/app/design/adminhtml/default/default/template/unl
[Magento]/skin/frontend/unl -> [Unl-Magento]/design/frontend/unl

-- OPTIONAL --
[Magento]/app/design/adminhtml/default/default/template/tester -> [Unl-Magento]/app/design/adminhtml/default/default/template/tester  (optional: for testing module)

cd magento
ln -s /path/to/unl-magento/app/code/local/Unl app/code/local/Unl
ln -s /path/to/unl-magento/app/etc/modules/Unl_All.xml app/etc/modules/Unl_All.xml
ln -s /path/to/unl-magento/skin/frontend/unl skin/frontend/unl
ln -s /path/to/unl-magento/app/design/frontend/unl app/design/frontend/unl
ln -s /path/to/unl-magento/app/design/adminhtml/default/default/template/unl app/design/adminhtml/default/default/template/unl

-- OPTIONAL --
ln -s /path/to/unl-magento/app/design/adminhtml/default/default/template/tester app/design/adminhtml/default/default/template/tester

-- REQUIRED COPYING --
Because Magento has some security settings for its javascript proxy, using symlinks for the following file WILL NOT work. You must copy them from the [Unl-Magento] workspace to the [Magento] workspace.
[Unl-Magento]/js/lightbox -> [Magento]/js/lightbox
[Unl-Magento]/js/lib/ds-sleight.js -> [Magento]/js/lib/ds-sleight.js (this is only needed because the UNL Templates won't work with the version of this file provided by Varien)
[Unl-Magento]/lib/Varien/Data/Form/Element/Editor.php -> [Magento]/lib/Varien/Data/Form/Element/Editor.php (required for WYSIWYG editor in admin)

There are two configration settings for the theme to work:
System>Configuration>Design
Package:Current Package Name:unl
Themes:Default:modern

For a production server the configuration settings for "Web", "Store Email Addresses", "Contacts", "Catalog", "Inventory", "Sales", "Shipping ...", and "Payment ..." will need to be set to match the business practices.

For help on general design and configuration refer to the documentation provided by Varien at http://www.magentocommerce.com/

A listing of the events that handlers can be written for is in file dispatcheventlist.xls. It may not be up to date with the current version of Magento.

A new branch of Magento (1.3) is being used. Substancial updates have been made to the design/skin files that effect the UNL design.
Work will need to be done in the future to port over changes. However, due to scope priority, frontend interface has been tabled.