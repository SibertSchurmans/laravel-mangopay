<?php


namespace SibertSchurmans\LaravelMangoPay\Interfaces;


interface HasMangoPay
{
    /**
     * Adds the model to Mango Pay and creates a user with a wallet
     * @return null
     */
    public function addToMangoPay();

    public function syncWithMangoPay();

    public function addFunds(int $amount, string $cardType);

    public function asMangoPayUser();

    public function makePaymentWithWallet(int $amount, int $recipientWallet);

    public function makeDirectPayment(int $amount, int $recipientWallet, string $cardType);

    public function wallet();
}
