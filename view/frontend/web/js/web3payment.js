/**
 * Copyright © Ihor Oleksiienko (https://github.com/torys877)
 * See LICENSE for license details.
 */
define([
    'uiComponent',
    'jquery',
    'web3'
], function (Component, $, web3) {
    'use strict';

    return Component.extend({
        defaults: {
            merchantAddress: null,
            networkVersion: null,
            givenProvider: null,
            orderAmount: null,
            orderHash: null,
            addTxUrl: null,
            thCheckAndConfirmUrl: null,
            successUrl: null,
            web3client: null,
            requestIntervalSeconds: null,
            accounts: []
        },
        /** connect provider **/
        initialize: function () {
            this._super();
            if (!this.createWeb3()) {
                return;
            }

            this.connectWallet();
        },
        showMessage: function(message) {
            $('.message').html(message);
            $('.message').show();
        },
        createWeb3: function() {
            this.givenProvider = web3.givenProvider;
            if (
                this.givenProvider &&
                typeof this.givenProvider != 'undefined'
            ) {
                if (this.checkNetwork()) {
                    this.web3client = new web3(web3.givenProvider);
                    return true;
                } else {
                    this.showMessage('Chain is wrong. Please change chain in metamask to Ethereum chain.');
                    return false;
                }
            } else {
                this.showMessage('Metamask is not authorized or not installed.');
                return false;
            }
        },
        checkNetwork: function() {
            let givenProvider = this.givenProvider;
            if (this.web3client) {
                givenProvider = this.web3client.givenProvider;
            }

            if (givenProvider.networkVersion == this.networkVersion) {
                return true;
            }
            return false;
        },
        /** check is provider exist **/
        isWeb3: function() {
            if (!this.web3client) {
                if (this.createWeb3()) {
                    return true;
                }

                return false;
            }

            return true;
        },
        /** connect metamask wallet to website **/
        connectWallet: function() {
            if (!this.isWeb3()) {
                return;
            }
            let self = this;
            this.web3client.eth.requestAccounts().then(
                function(accs) {
                    self.accounts = accs;
                    if (accs.length) {
                        $('#connect_wallet_button').hide();
                        $('#pay_eth_button').show();
                    }
                }
            );
        },
        /** check is wallet connected to website **/
        isWalletConnected: function() {
            if (!this.isWeb3()) {
                return;
            }
            var result = this.accounts.length ? true:false;
            return result;
        },
        /** get all connected accounts **/
        getAccounts: function() {
            if (!this.isWeb3()) {
                return;
            }
            var self = this;
            this.web3client.eth.requestAccounts().then(
                function(result) {
                    self.accounts = result
                }
            );
        },
        /** get current account **/
        getCurrentAccount: function() {
            if (!this.isWeb3()) {
                return;
            }
            if (this.isWalletConnected()) {
                return this.accounts[0];
            }

            return false;
        },
        /** send metamask transaction **/
        sendTransaction: function() {
            if (!this.isWeb3()) {
                return;
            }
            let self = this;
            this.web3client.eth.sendTransaction({
                from: this.getCurrentAccount(),
                to: this.merchantAddress,
                value: web3.utils.toWei(this.orderAmount, "ether"),
            }, function(err, transactionHash) {
                if (err) {
                    self.showMessage(err.code + ' ' + err.message);
                } else {
                    //add transaction to magento with status isClosed = 0
                    self.addTransaction(self, transactionHash);
                }
            });
        },
        /** add transaction to magento with status isClosed = 0 **/
        addTransaction: function(currentComponentObject, transactionHash) {
            let self = currentComponentObject;
            $.ajax({
                type: 'POST',
                url: self.addTxUrl,
                showLoader: true,
                data: {
                    "txhash": transactionHash,
                    "order_hash": self.orderHash
                }
            })
            .done(function(addRresult) {
                //register transaction, not captured
                self.showMessage(addRresult.message);
                if (addRresult.error) {
                    return;
                }
                self.checkTransactionStatus(self, transactionHash)
            })
            .fail(function(result){
                self.showMessage('Sorry, there was a problem saving the settings.');
            });
        },
        /** check transaction status through web3 metamask connection **/
        checkTransactionStatus: function(currentComponentObject, transactionHash) {
            let self = currentComponentObject;
            //check registered transaction and capture if it is processed in blockchain
            var intervalVar = setInterval(function () {
                self.web3client.eth.getTransactionReceipt(transactionHash, function(error, obj) {
                    if (error) {
                        self.showMessage(err.code + ' ' + error.message);
                    }
                    if (!obj) {
                        return;
                    }
                    if (obj.status == true) {
                        //confirm transaction in magento
                        self.checkAndConfirmTransaction(self, transactionHash, intervalVar)
                    }
                })
            }, self.requestIntervalSeconds);
        },
        /** check transaction on backend(if enabled), confirm transaction, create invoice for order **/
        checkAndConfirmTransaction: function(currentComponentObject, transactionHash, intervalVar) {
            let self = currentComponentObject;
            $.ajax({
                url: self.thCheckAndConfirmUrl,
                type: 'post',
                dataType: 'json',
                data: {
                    "txhash": transactionHash,
                    "order_hash": self.orderHash
                },
                success: function(checkResult) {
                    self.showMessage(checkResult.message);
                    if (!checkResult.error) {
                        clearInterval(intervalVar);
                        window.location.replace(self.successUrl);
                    }
                }
            });
        }
    });
});
