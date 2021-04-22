<?php

namespace SibertSchurmans\LaravelMangoPay\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use MangoPay\Address;
use MangoPay\Libraries\Exception;
use MangoPay\Libraries\ResponseException;
use MangoPay\MangoPayApi;
use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsWeb;
use MangoPay\PayInPaymentDetailsCard;
use MangoPay\Transfer;
use MangoPay\UserNatural;
use MangoPay\Wallet;

trait InteractsWithMangoPay {
    public $mangoPayApi;

    public function initializeInteractsWithMangoPay()
    {
        $this->fillable[] = 'first_name';
        $this->fillable[] = 'last_name';
        $this->fillable[] = 'birthday';
        $this->fillable[] = 'mangopay_id';
        $this->fillable[] = 'wallet_id';
        $this->fillable[] = 'email';

        $this->fillable = array_unique($this->fillable);

        $this->mangoPayApi = app(MangoPayApi::class);
    }

    public function addToMangoPay(?string $walletDescription, ?string $walletCurrency) {
        $mangoUser = new UserNatural();
        $mangoUser->PersonType = "NATURAL";
        $mangoUser->FirstName = $this->first_name;
        $mangoUser->LastName = $this->last_name;
        $mangoUser->Birthday = Carbon::parse($this->birthday)->timestamp;
        $mangoUser->Nationality = $this->nationality;
        $mangoUser->CountryOfResidence = $this->country_of_residence;
        $mangoUser->Email = $this->email;

        $mangoUser = $this->mangoPayApi->Users->Create($mangoUser);

        $wallet = new Wallet();
        $wallet->Owners = array ($mangoUser->Id);
        $wallet->Description = $walletDescription ?? "Wallet from $this->first_name $this->last_name";
        $wallet->Currency = $walletCurrency ?? $this->getDefaultCurrency();

        $wallet = $this->mangoPayApi->Wallets->Create($wallet);

        $this->mangopay_id = $mangoUser->Id;
        $this->wallet_id = $wallet->Id;
        $this->save();

        return $mangoUser;
    }

    /**
     * Update Mangopay User
     */
    public function syncWithMangoPay() {
        $mangoUser = $this->mangoPayApi->Users->GetNatural($this->mangopay_id);

        $mangoUser->PersonType = "NATURAL";
        $mangoUser->FirstName = $this->first_name;
        $mangoUser->LastName = $this->last_name;
        $mangoUser->Birthday = $this->birthday;
        $mangoUser->Nationality = $this->nationality;
        $mangoUser->CountryOfResidence = $this->country_of_residence;
        $mangoUser->Email = $this->email;

        return $this->mangoPayApi->Users->Update($mangoUser);
    }

    /**
     * Get a MangoUser data
     */
    public function asMangoPayUser() {
        return $this->mangoPayApi->Users->GetNatural($this->mangopay_id);
    }

    /**
     * Create a Card Web Payin
     * @param int $amount
     * @param string $cardType
     * @param string $redirectUrl
     * @param string|null $currency
     * @param string|null $culture
     * @return PayIn
     */
    public function addFunds(int $amount, string $cardType, string $redirectUrl, ?string $currency, ?string $culture) {
        $webPayin = new PayIn();
        $webPayin->CreditedWalletId = $this->wallet_id;
        $webPayin->AuthorId = $this->mangopay_id;

        $webPayin->PaymentType = "CARD";
        $webPayin->PaymentDetails = new PayInPaymentDetailsCard();
        $webPayin->PaymentDetails->CardType = $cardType;

        $webPayin->DebitedFunds = new Money();
        $webPayin->DebitedFunds->Amount = $amount;
        $webPayin->DebitedFunds->Currency = $currency ?? $this->getDefaultCurrency();

        $webPayin->Fees = new Money();
        $webPayin->Fees->Amount = $this->getTopUpFee();
        $webPayin->Fees->Currency = $currency ?? $this->getDefaultCurrency();

        $webPayin->ExecutionType = "WEB";
        $webPayin->ExecutionDetails = new PayInExecutionDetailsWeb();
        $webPayin->ExecutionDetails->ReturnURL = $redirectUrl;
        $webPayin->ExecutionDetails->Culture = $culture ?? $this->getDefaultCulture();

        return $this->mangoPayApi->PayIns->Create($webPayin);
    }

    public function makePaymentWithWallet(int $amount, int $recipientWallet, ?string $currency) {
        $transfer = new Transfer();
        $transfer->AuthorId = $this->mangopay_id;

        $transfer->DebitedFunds = new Money();
        $transfer->DebitedFunds->Currency = $currency ?? $this->getDefaultCurrency();
        $transfer->DebitedFunds->Amount = $amount;

        $transfer->Fees = new Money();
        $transfer->Fees->Currency = $currency ?? $this->getDefaultCurrency();
        $transfer->Fees->Amount = $this->getWalletTransactionFee();

        $transfer->DebitedWalletID = $this->wallet_id;
        $transfer->CreditedWalletId = $recipientWallet;

        return $this->mangoPayApi->Transfers->Create($transfer);
    }

    public function makeDirectPayment(int $amount, int $recipientWallet, string $cardType, string $redirectUrl, ?string $currency) {
        $webPayin = new PayIn();
        $webPayin->CreditedWalletId = $recipientWallet;
        $webPayin->AuthorId = $this->mangopay_id;

        $webPayin->PaymentType = "CARD";
        $webPayin->PaymentDetails = new PayInPaymentDetailsCard();
        $webPayin->PaymentDetails->CardType = $cardType;

        $webPayin->DebitedFunds = new Money();
        $webPayin->DebitedFunds->Amount = $amount;
        $webPayin->DebitedFunds->Currency = $currency ?? $this->getDefaultCurrency();

        $webPayin->Fees = new Money();
        $webPayin->Fees->Amount = $this->getWalletTransactionFee();
        $webPayin->Fees->Currency = $currency ?? $this->getDefaultCurrency();

        $webPayin->ExecutionType = "WEB";
        $webPayin->ExecutionDetails = new PayInExecutionDetailsWeb();
        $webPayin->ExecutionDetails->ReturnURL = $redirectUrl;
        $webPayin->ExecutionDetails->Culture = $this->getDefaultCulture();

        return $this->mangoPayApi->PayIns->Create($webPayin);
    }

    public function wallet() {
        return $this->mangoPayApi->Wallets->Get($this->wallet_id);
    }

    private function getTopUpFee() {
        return config('mangopay.fees.top_up');
    }

    private function getWalletTransactionFee() {
        return config('mangopay.fees.wallet_transaction');
    }

    private function getDefaultCurrency() {
        return config('mangopay.currency');
    }

    private function getDefaultCulture() {
        return config('mangopay.culture');
    }
}
