# UNL Magento Modules

These modules and themes are used by the [University of Nebraska-Lincoln](http://www.unl.edu/) to host the service [UNL Marketplace](http://marketplace.unl.edu/). They provide the look-and-feel for the frontend and the functionality on the backend to support distributed accounting and reconciliation.

Much of the `Unl_Core` module contains core Magento overrides to fix lesser known Magento quirks.

## INSTALLATION

These install instructions are based on the following assumptions:

1. Magento has been downloaded
`<magento>` - http://svn.magentocommerce.com/source/branches/1.8

2. ModMan is installed and your Magento download has been init'd
`<modman>` - https://github.com/colinmollenhour/modman

Run the following command
`modman clone git@git.unl.edu:iim/unl-magento.git`
or
`modman clone https://github.com/kabel/unl-magento.git`

Ensure the file permissions for the Magento files is ok for the web server to read/write. A script is include at `data/scripts/install-permissions.sh` to assist in inital permission setting. Review http://www.magentocommerce.com/knowledge-base/entry/ce18-and-ee113-installing for more information.

Open the application in your web browser and follow the installation wizard.

After installation is complete, you should follow the Magento recommendations for filesystem security from http://www.magentocommerce.com/knowledge-base/entry/install-privs-after. A script at `data/scripts/prod-permissions.sh` is here to assist. For Nebraska taxing purposes, please follow the instructions below for TAX RATE MAINTENANCE

## CONFIGURATION

From the Admin Panel interface, the following configuration settings should be set to ensure complete functionality. In the navigation go to `System > Configuration`

`Sales > Shipping Methods`

 * Configure each carrier per the business requirements (requires API credentials)
 * Disable unused carriers

`Sales > Payment Methods`

 * Configure each provider per the business requirement (requires API credentials)
 * Disable unused providers

## MAINTENANCE

When running maintenance on the magento project that may require temporary shutdown of magento run the following command.

    cd /path/to/magento
    /path/to/unl-magento/data/scripts/swapMaintenance.sh YOUR_IP_ADDRESS
    
Once the maintenance is over run
   
    cd /path/to/magento
    /path/to/unl-magento/data/scripts/swapMaintenance.sh -e

## TAX RATE MAINTENANCE

Included in the `data/tax/` directory is the schema for a separate database that can handle translating the tax boundary and rate information from the [Nebraska Department of Revenue](http://nebraska.avalara.com/) into something Magento can use.

To use the database, please create a new schema and load the `SSTSchema.sql` file into it.

New tax data should be made available quarterly from NEDOR:

1. Download the "Rate and Boundary Data" from the above mentioned site
2. Unzip the file and load the two files into some temporary MySQL database.
Use the `sst_import.sql` file as a template for quickly loading new data into the database
3. Export the resultsets from running the `tax_procedures.sql` file, into CSV. Place all of them in the `results` directory
4. Run the shell script `build.sh` to compile all resultsets into a single CSV file named `rates.csv`
5. [OPTIONAL] Compress the files, so you don't have to send so much to the web server 
   * Rename the boundary file: `mv NEB#####.txt NEB.txt`
   * `zip` the files `NEB.txt` and `rates.csv`, individually
6. In the Admin Panel go to `Sales > Tax > Import / Export Tax Rates`
7. Upload the boundary file
8. Upload the rates file (using the full import form)

## HELP

For help on general design and configuration refer to the documentation provided at http://magento.com/
