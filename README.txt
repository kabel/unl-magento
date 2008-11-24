There are a total of 4 symlinks to get our customizations working in magento. The svn projects are labeled 
[Magento] : http://svn.magentocommerce.com/source/branches/1.1
[Unl-Magento] : http://its-gforge.unl.edu/svn/unl-magento

[Magento]/app/code/local/Unl -> [Unl-Magento]/app/code/local/Unl
[Magento]/app/etc/modules/Unl_All.xml -> [Unl-Magento]/app/etc/modules/Unl_All.xml
[Magento]/app/design/frontend/unl -> [Unl-Magento]/app/design/frontend/unl
[Magento]/skin/frontend/unl -> [Unl-Magento]/design/frontend/unl

Please note that a new release of magento version 1.1 has come out last week an there may be some changes to the design/layout file the need to be merged with UNL's custom skin. This is the only thing that should cause problems with a fresh checkout.