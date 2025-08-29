<?
require __DIR__  . '/vendor/autoload.php';
// MercadoPago\SDK::setAccessToken("ACCESS_TOKEN");      // On Production
MercadoPago\SDK::setAccessToken("TEST-862135565198034-071901-ebeec53a8c5a75abba816d8904a9fcba-245646762"); // On Sandbox
$payment = new MercadoPago\Payment();

    $payment->transaction_amount = 141;
    $payment->token = "YOUR_CARD_TOKEN";
    $payment->description = "Ergonomic Silk Shirt";
    $payment->installments = 1;
    $payment->payment_method_id = "visa";
    $payment->payer = array(
      "email" => "larue.nienow@hotmail.com"
    );

    $payment->save();

    echo $payment->status;

  ?>
