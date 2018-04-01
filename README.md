# mnpy-magento2
[MNPY](https://mnpy.io/) | [Documentation](https://mnpy.gitbooks.io/mnpy-api/content/)

Start accepting over hundreds cryptocurrencies without minimum costs, fixed contracts and hidden costs. At MNPY you pay for successful transactions only. Read more at [MNPY](https://mnpy.io/).

## Requirements
It's easy to start using MNPY, you need;

* PHP >7.0
* A [MNPY account](https://mnpy.io/dashboard/register)
* A Ethereum wallet, preferably ERC20 compliant
* Magento® 2.1.x or Magento® 2.2.x

## Installation

You can install this extension using [Composer](http://getcomposer.org/doc/00-intro.md).

```
$ composer require mnpy/mnpy-magento2
```
## Getting started

Execute the following commands to install the extension.
Don't execute the following commands without local testing.

```
$ php bin/magento setup:upgrade
$ php bin/magento module:enable Mnpy_Payment
$ php bin/magento cache:clean
```

After that, you will find the settings under Stores -> Configuration -> Sales -> Payment Methods.
Fill in your API key and select the currencies you want to accept.

## License
[BSD (Berkeley Software Distribution) License](https://opensource.org/licenses/bsd-license.php). Copyright &copy; 2018 MNPY
