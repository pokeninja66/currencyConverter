<?php

if (!isset($_REQUEST['action'])) {
    header("Location: ../../");
    exit();
} else {
    $action = trim($_REQUEST['action']);
    header("Content-Type: aplication/json");
    session_start();
    //
    include "./converter.php";
    ini_set("display_errors", 0);
}

if ($action == "getCurrencies") {

    #print_r($_SESSION);

    if (isset($_SESSION['CurrenciesArr'])) {
        echo json_encode($_SESSION['CurrenciesArr']);
        exit();
    }

    $_SESSION['CurrenciesArr'] = Converter\CurrencyConverter::getCurrencies();
    $_SESSION['Rates'] = Converter\CurrencyConverter::$convershionRates;
    echo json_encode($_SESSION['CurrenciesArr']);
    exit();
}

if ($action == "convertTo") {
    #print_r($_REQUEST);
    #exit();
    $Form = $_REQUEST['Form'];

    #print_r($_SESSION);

    $responceObj = new stdClass;
    $responceObj->text = Converter\CurrencyConverter::matchAndReplace($Form);
    echo json_encode($responceObj);
    exit;

    /*
    preg_match_all("/([^ ]+ [{USD}]{3})/i", $Form['inputText'], $amount_array);
    preg_match_all("/\\$[^ ]+/", $Form['inputText'], $amount_array);
    print_r($amount_array);
    exit();
    */
}


exit();
