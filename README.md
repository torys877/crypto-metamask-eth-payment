# CurrencyPrecision Magento 2 Extension

Metamask Ethereum payment method module for Magento 2

## Table of contents

* [Description](#description)
* [Feature](#feature)
* [Installation](#installation)
  * [Composer Installation](#composer-installation)
  * [Settings](#settings)
    * [Live Settings](#live-settings)
    * [Test Mode](#test-mode)
    * [Display Eth Currency](#display-eth-currency)
* [Screenshots](#screenshots)
* [Author](#author)
* [License](#license)

## Description

Extension allows to receive direct payments from customer Ethereum blockchain wallet to merchant Ethereum wallet using Metamask in Ethereum cryptocurrency Ethers.

## Idea

Using any gateways and third party services to make and receive payments breaks the philosophy of cryptocurrencies. If you use cryptocurrencies to pay you should be able to make it directly between wallets that no one can control, interrupt and change your transaction.
But when this module was created there are only modules for Magento 2 from companies that are gateways between customer and merchant and handle the payment process, can control this process and payment processes in this case have no any differences from using bank account.

The main idea of this module is to allow you make and receive payments directly between wallets, without using any third party gateways and commission and pay only networks gas.

## Features

1) Display prices in Ether currency
2) Pay by Ether from checkout after placing order
3) Pay from customer accound order view page (customer can pay on checkout or, if have any troubles, later from his account)
4) Check transaction status using `web3js` library
5) Check transaction status through `etherscan.io` API request
6) Check transactions by cron using `etherscan.io` API request
7) Check transaction manually from admin area transaction page

## Installation

To install module you need to add repositories to your `composer.json`:

### Composer installation 

```angular2html
    "repositories": {
        "crypto-base": {
            "type": "git",
            "url": "git@github.com:torys877/crypto-base.git"
        },
        "crypto-currency-precision": {
            "type": "git",
            "url": "git@github.com:torys877/crypto-currency-precision.git"
        },
        "crypto-metamask-eth-payment": {
            "type": "git",
            "url": "git@github.com:torys877/crypto-metamask-eth-payment.git"
        }
    }
```

Or add repositories from console:

`composer config repositories.crypto-base git git@github.com:torys877/crypto-base.git`

`composer config repositories.crypto-currency-precision git git@github.com:torys877/crypto-currency-precision.git`

`composer config repositories.crypto-metamask-eth-payment git git@github.com:torys877/crypto-metamask-eth-payment.git`

Install module:

`composer require crypto/metamask-eth-payment:v1.0.0`

And run

```angular2html
php bin/magento setup:upgrade
```

### Settings

![Checkout Payment Page](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/Configuration.png)

#### Live Settings
- **Enabled** - enable/disable payment method
- **Title** - payment method title
- **Ether Network Version** - version of network (can be checked in metamask settings, by default Ethereus is `1`, but testing networks have another version)
- **Merchant Ether Address** - merchant ethereum address to take payment
- **Etherscan Url** - url to view transactions (can be different in different networks, useful during testing)
- **Check transaction on Etherscan** - if set not cron does not work and transactions are checking only on frontend on payment page using web3js
- **Etherscan Api Url** - display if `Check transaction on Etherscan` is enabled. Api url for needed network to check transaction (can be different for different networks)
- **Etherscan Api Key** - display if `Check transaction on Etherscan` is enabled. Api key for etherscan - can be taken on [etherscan.io/apis](https://etherscan.io/apis)

#### Test Mode
Settings are the same as for `Live`, but you need to use test networks (test network version, test network etherscan api url/key etc. On screen uses `Ropsten` network)

#### Display Eth Currency

To display ETH currency you also need to add it to `allowed currencies` and add currency rate in magento (automatically update rates will maybe included in future releases)

## Screenshots

### Display Prices

![Display Prices](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/prices_homepage.png)

### Payment Method On Checkout
    
    Note: Payment method is displaying only if ETH currency selected 

![Checkout payment method](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/checkout_payment.png)


### Connect Wallet

    Note: If wallet is not connected firstly customer sees 'Connect Wallet' button, then 'Pay' button

![Connect Wallet](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/connect_wallet_button.png)


### Pay by ETH

![Pay By Eth](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/pay_by_eth_button2.png)


### Processing Transaction

    Note: After confirming transaction in Metamask wallet, customer should wait on the same page to magento confirm transaction
    Magento sends request to metamask using Web3js and try to get confirmation message, after it, Magento
    sends request to server and if Etherscan checking is enabled, transaction status also is checking on etherscan.

    IMPORTANT: it is highly recommended to enable etherscan checking to prevent JS injection on processing page

![Processing Transaction](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/processing_transaction.png)


### Check transaction in admin area

    Transaction ID is clickable and move you on etherscan page for this transaction

![Check in admin area](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/transaction.png)

### Pay From Customer Account

    If customer placed order but not paid it, he still can do it from his account. This button sends him to processing page and allows to do payment

![Pay from customer account](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/account_pending.png)



### Transaction Captured

    When transaction is captured and approved in blockchain and magento, customer sees this message 

![Pay from customer account](https://raw.githubusercontent.com/torys877/crypto-metamask-eth-payment/main/docs/account_captured.png)


## Author

### Ihor Oleksiienko

* [Github](https://github.com/torys877)
* [Linkedin](https://www.linkedin.com/in/igor-alekseyenko-77613726/)
* [Facebook](https://www.facebook.com/torysua/)

## License

Metamask Ethereum extension for Magento 2 is licensed under the MIT License - see the LICENSE file for details