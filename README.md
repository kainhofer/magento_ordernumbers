# OpenTools_Ordernumber

This is a module to modify the order, invoice, shipment and credit memo numbers in Magento 1.9 (NOT in Magento 2 - feel free to fork this module and update it to Magento 2!). Many variables and counter options are available in the format string.

## Installation

Install via [modman](ssh://open-tools.net@open-tools.net/var/www/open-tools.net/private/git/Magento_Ordernumber/):

```
$ cd <magento root>
$ modman init # if you've never used modman before
$ modman clone ssh://open-tools.net@open-tools.net/var/www/open-tools.net/private/git/Magento_Ordernumber/
```

## Configuration

Visit *System -> Configuration -> Sales -> Customize Order Numbers* and set it to Yes to enable custom numbers for certain number types. All configuration is done there, there is no separate component or view available.

## Usage

Note that it's disabled by default.
