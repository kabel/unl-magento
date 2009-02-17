There are a total of 4 symlinks to get our customizations working in magento. 
The svn projects are labeled:
[Magento] : http://svn.magentocommerce.com/source/branches/1.2
[Unl-Magento] : http://its-gforge.unl.edu/svn/unl-magento

[Magento]/app/code/local/Unl -> [Unl-Magento]/app/code/local/Unl
[Magento]/app/etc/modules/Unl_All.xml -> [Unl-Magento]/app/etc/modules/Unl_All.xml
[Magento]/app/design/frontend/unl -> [Unl-Magento]/app/design/frontend/unl
[Magento]/skin/frontend/unl -> [Unl-Magento]/design/frontend/unl
[Magento]/app/design/adminhtml/default/default/template/tester -> [Unl-Magento]/app/design/adminhtml/default/default/template/tester  (optional: for testing module)

cd magento
ln -s /path/to/unl-magento/app/code/local/Unl app/code/local/Unl
ln -s /path/to/unl-magento/app/etc/modules/Unl_All.xml app/etc/modules/Unl_All.xml
ln -s /path/to/unl-magento/skin/frontend/unl skin/frontend/unl
ln -s /path/to/unl-magento/app/design/frontend/unl app/design/frontend/unl
ln -s /path/to/unl-magento/app/design/adminhtml/default/default/template/tester app/design/adminhtml/default/default/template/tester


There are two configration settings for the theme to work:
System>Configuration>Design
Package:Current Package Name:unl
Themes:Default:modern

A new branch of Magento (1.2) is being used. Substancial updates have been made to the design/skin files that effect the UNL design.

Work will need to be done in the future to port over changes. However, due to scope priority, frontend interface has been tabled.