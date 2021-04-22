# Laravel MangoPay Integration

## Introduction
This package makes it easier to connect MangoPay to Laravel. The ServiceProvider is based on the one used in the [laravel-mangopay](https://github.com/cviebrock/laravel-mangopay) package.


## Installation

1. Install the package via composer:

    ```sh
    composer require sibertschurmans/laravel-mangopay
    ```

2.  Publish the configuration and migration file:

    ```sh
    php artisan vendor:publish --provider="SibertSchurmans\LaravelMangopay\ServiceProvider"
    ```

3.  Finally, migrate to add the required columns to the User model:

    ```sh
    php artisan migrate 
    ```


## Configuration

To use this package you'll need to set the key and secret in the .env file. You can also specify the MangoPay environment (defaults to sandbox).

```sh
MANGOPAY_ENVIRONMENT = <sandbox or production>
MANGOPAY_KEY = <your-client-id>
MANGOPAY_SECRET = <your-client-password>
```

You can find the published configuration file in `config/mangopay.php`. Here you can change the default values that are used in this package.

## Usage

Extend the user model with the `HasMangoPay` interface and include the trait `InteractsWithMangoPay` to set up the available functions.

From this point on you can use and set up MangoPay on every user.

```php
$user = User::find(1);

$user->addToMangoPay(?string $walletDescription, ?string $walletCurrency);
$user->syncToMangoPay();
$user->addFunds(int $amount, string $cardType, string $redirectUrl, ?string $currency, ?string $culture);
$user->asMangoPayUser();
$user->makePaymentWithWallet(int $amount, int $recipientWallet, ?string $currency);
$user->makeDirectPayment(int $amount, int $recipientWallet, string $cardType, string $redirectUrl, ?string $currency);
$user->wallet();
```

