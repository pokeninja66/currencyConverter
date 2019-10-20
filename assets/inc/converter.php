<?php

namespace Converter;

use stdClass;

class CurrencyConverter extends stdClass
{

    private static $feed = 'http://www.bnb.bg/PressOffice/PORSS/index.htm?getRSS=1&lang=BG&cat=1';
    private static $feedObj = null;
    public static $arrObs = [];
    public static $convershionRates = [];


    public static function getCurrencies()
    {
        // reset 
        self::$arrObs = [];
        // get rss feed
        self::$feedObj = simplexml_load_file(self::$feed, "SimpleXMLElement", LIBXML_NOCDATA);
        // include simple html dom for easier dom manipulation
        include_once('./simple_html_dom.php');

        #print_r(self::$feedObj);

        // add BGN
        $tempObj = new stdClass();
        $tempObj->currency = "BGN";
        $tempObj->value = 1;
        self::$arrObs[] = $tempObj;
        // add EUR?

        // set values
        $html = str_get_html(self::$feedObj->channel->item->description);
        foreach ($html->find('ul') as $ul) {
            foreach ($ul->find('li') as $li) {
                #print_r($li);
                $tempObj = new stdClass();
                // get only the currency name!
                preg_match('/[A-Z]+/i', $li->find('em', 0)->plaintext, $matches);
                //print_r($matches);
                $tempObj->currency = trim($matches[0]); // $li->find('em', 0)->plaintext;//children('em')->innertext;
                $tempObj->value =  $li->find('strong', 0)->plaintext; //children('strong')->innertext;
                #print_r($tempObj);
                self::$arrObs[] = $tempObj;
                self::$convershionRates[trim($matches[0])] = $tempObj->value;
            }
            #echo $li->innertext . '<br>';
        }
        #print_r(self::$arrObs);

        return self::$arrObs;
    }

    public static function matchAndReplace($arrInfo)
    {

        $str = trim($arrInfo['inputText']);

        $valuesArr = explode(';', $arrInfo['Currency']);

        $currency = $valuesArr[0];
        $convertValue = $valuesArr[1];

        // set mb encoding
        mb_internal_encoding('UTF-8');

        // BGN test
        if ($currency == "BGN") {

            // convert from USD $
            preg_match_all("/\\$[^ ]+/", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as $key => $oneVal) {

                    $converted = self::calcRateToBGN("USD", mb_strtolower($oneVal[$key]), "$");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                    /*
                    $converted = ($convertValue * self::convertToNum(str_replace("$", "", $oneVal[$key])));
                    $str = str_replace($oneVal[$key], "$" . self::formatNum($converted), $str);
                    */
                }
            }

            preg_match_all("/[^ ]+\\$/", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as  $key => $oneVal) {
                    $converted = self::calcRateToBGN("USD", mb_strtolower($oneVal[$key]), "$");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                }
            }

            preg_match_all("/([^ ]+ [USD]{3})/i", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as  $key => $oneVal) {
                    $converted = self::calcRateToBGN("USD", mb_strtolower($oneVal[$key]), "usd");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                }
            }

            // convert form EUR
            // convert from GBP £
            # da se vidi posle tova
            preg_match_all("/\\£[^ ]+/", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as $key => $oneVal) {

                    $converted = self::calcRateToBGN("GBP", mb_strtolower($oneVal[$key]), "$");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                    /*
                    $converted = ($convertValue * self::convertToNum(str_replace("$", "", $oneVal[$key])));
                    $str = str_replace($oneVal[$key], "$" . self::formatNum($converted), $str);
                    */
                }
            }

            preg_match_all("/[^ ]+\\£/u", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as  $key => $oneVal) {
                    $converted = self::calcRateToBGN("GBP", mb_strtolower($oneVal[$key]), "$");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                }
            }

            preg_match_all("/([^ ]+ [GBP]{3})/i", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as  $key => $oneVal) {
                    $converted = self::calcRateToBGN("GBP", mb_strtolower($oneVal[$key]), "usd");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                }
            }

            // convert to CHF f

            preg_match_all("/[^ ]+f/u", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as  $key => $oneVal) {
                    $converted = self::calcRateToBGN("GBP", mb_strtolower($oneVal[$key]), "$");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                }
            }

            preg_match_all("/([^ ]+ [CHF]{3})/i", $str, $amount_array);
            if ($amount_array) {
                foreach ($amount_array as  $key => $oneVal) {
                    $converted = self::calcRateToBGN("GBP", mb_strtolower($oneVal[$key]), "usd");
                    $str = str_replace($oneVal[$key], self::formatNum($converted) . " лв.", $str);
                }
            }
        }
        /*
        switch ($currency) {
            case "USD":

                preg_match_all("/\\$[^ ]+/", $str, $amount_array);
                if ($amount_array) {
                    foreach ($amount_array as $key => $oneVal) {
                        $converted = ($convertValue * self::convertToNum(str_replace("$", "", $oneVal[$key])));
                        $str = str_replace($oneVal[$key], "$" . self::formatNum($converted), $str);
                    }
                }

                preg_match_all("/[^ ]+\\$/", $str, $amount_array);
                if ($amount_array) {
                    foreach ($amount_array as  $key => $oneVal) {
                        $converted = ($convertValue * self::convertToNum(str_replace("$", "", $oneVal[$key])));
                        $str = str_replace($oneVal[$key], self::formatNum($converted) . "$", $str);
                    }
                }

                preg_match_all("/([^ ]+ [USD]{3})/i", $str, $amount_array);
                if ($amount_array) {
                    foreach ($amount_array as  $key => $oneVal) {
                        $converted = ($convertValue * self::convertToNum(str_replace("usd", "", mb_strtolower($oneVal[$key]))));
                        $str = str_replace($oneVal[$key], self::formatNum($converted) . " USD", $str);
                    }
                }

                break;
            default: // BGN
                break;
        }
        */
        return $str;
    }

    private static function convertToNum($number)
    {
        return (float) str_replace(',', '', $number);
    }

    private static function formatNum($number, $delimiter = ".")
    {
        return number_format((float) $number, 2, $delimiter, '');
    }

    private static function calcRateToBGN($rate = "", $value, $toReplace = "")
    {
        return ($_SESSION["Rates"][$rate] * self::convertToNum(str_replace($toReplace, "", $value)));
    }
}
