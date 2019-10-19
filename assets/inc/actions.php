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
}

if ($action == "getCurrencies") {

    #print_r($_SESSION);

    if (isset($_SESSION['CurrenciesArr'])) {
        echo json_encode($_SESSION['CurrenciesArr']);
        exit();
    }

    $_SESSION['CurrenciesArr'] = Converter\CurrencyConverter::getCurrencies();
    echo json_encode($_SESSION['CurrenciesArr']);
    exit();
}

if ($action == "convertTo") {
    print_r($_REQUEST);
    exit();
    include_once('./simple_html_dom.php');
}


exit();
