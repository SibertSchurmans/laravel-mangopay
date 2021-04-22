<?php


namespace SibertSchurmans\LaravelMangoPay\Interfaces;


interface HasMangoPay
{
    /**
     * Adds the model to Mango Pay and creates a user with a wallet
     * @return null
     */
    public function addToMangoPay(?string $walletDescription, ?string $walletCurrency);

    public function syncWithMangoPay();

    public function addFunds(int $amount, string $cardType, string $redirectUrl, ?string $currency, ?string $culture);

    public function asMangoPayUser();

    public function makePaymentWithWallet(int $amount, int $recipientWallet, ?string $currency);

    public function makeDirectPayment(int $amount, int $recipientWallet, string $cardType, string $redirectUrl, ?string $currency);

    public function wallet();
}
