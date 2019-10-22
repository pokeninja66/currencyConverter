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
                self::$convershionRates[trim($matches[0])] = (float) $tempObj->value;
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
        #$convertValue = $valuesArr[1];
        $amount_array = [];
        // set mb encoding
        mb_internal_encoding('UTF-8');

        switch ($currency) {
                // BGN conversion
            case "BGN":
                return self::BGNmatch($str);
                break;
                // USD conversion
            case "USD":
                return self::USDmatch($str);
                break;
                // GBP conversion
            case "GBP":
                return self::GBPmatch($str);
                break;
                // CHF conversion
            case "CHF":
                return self::CHFmatch($str);
                break;

            default:
                return $str;
        }
    }

    private static function convertToNum($number, $delimiter = "")
    {
        return (float) str_replace(',', $delimiter, $number);
    }

    private static function formatNum($number, $delimiter = ".")
    {
        return number_format((float) $number, 2, $delimiter, '');
    }

    private static function formatNumUSD($number)
    {
        setlocale(LC_MONETARY, 'en_US.UTF-8');
        return self::money_format('%.2n', $number);
    }

    private static function formatNumGBP($number)
    {
        return '£' . number_format((float) $number, 2, '.', ',');
        # well this isnt working
        #setlocale(LC_MONETARY, 'en_GB.UTF-8');
        #print_r(self::money_format('%.2n', $number));
        #return self::money_format('%.2n', $number);
    }

    private static function formatNumCHF($number)
    {
        return  number_format((float) $number, 2, '.', ',') . 'Fr';
    }

    private static function calcRateToBGN($rate = "", $value, $toReplace = "")
    {
        return ($_SESSION["Rates"][$rate] * self::convertToNum(str_replace($toReplace, "", $value)));
    }

    // convert currency to BGN lv
    private static function BGNmatch($str = "")
    {

        // convert from USD $
        preg_match_all("/(?<usd>\\$[^ ]+)/", $str, $amount_array);
        #print_r($amount_array);
        if ($amount_array['usd']) {
            #print_r($amount_array['usd']);
            foreach ($amount_array['usd'] as $value) {
                $converted = self::calcRateToBGN("USD", mb_strtolower($value), "$");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
            /*
            foreach ($amount_array as $key => $oneVal) {
                foreach ($oneVal[$key] as $value) {
                    $converted = self::calcRateToBGN("USD", mb_strtolower($value), "$");
                    $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
                }
               
                  $converted = self::calcRateToBGN("USD", mb_strtolower($oneVal[$key]), "$");
                  $str = str_replace($oneVal[$key], self::formatNum($converted, ",") . "лв.", $str);
                 
                  $converted = ($convertValue * self::convertToNum(str_replace("$", "", $oneVal[$key])));
                  $str = str_replace($oneVal[$key], "$" . self::formatNum($converted), $str);
                  
            }
            */
        }

        preg_match_all("/(?<usd>[^ ]+\\$)/", $str, $amount_array);
        if ($amount_array['usd']) {
            foreach ($amount_array['usd'] as $value) {
                $converted = self::calcRateToBGN("USD", mb_strtolower($value), "$");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        preg_match_all("/(?<usd>[^ ]+ [USD]{3})/i", $str, $amount_array);
        if ($amount_array['usd']) {
            foreach ($amount_array['usd'] as $value) {
                $converted = self::calcRateToBGN("USD", mb_strtolower($value), "$");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        // convert form EUR
        // convert from GBP £
        # da se vidi posle tova
        #preg_match_all("/£[^ ]+/", $str, $amount_array);
        #if ($amount_array) {
        #    #echo "£ -//";
        #    #print_r($amount_array);
        #    foreach ($amount_array as $key => $oneVal) {
        #    
        #        $converted = self::calcRateToBGN("GBP", mb_strtolower($oneVal[$key]), "$");
        #        $str = str_replace($oneVal[$key], self::formatNum($converted, ",") . "лв.", $str);
        #        /*
        #        $converted = ($convertValue * self::convertToNum(str_replace("$", "", $oneVal[$key])));
        #        $str = str_replace($oneVal[$key], "$" . self::formatNum($converted), $str);
        #        */
        #    }
        #}

        preg_match_all("/(?<gbp>\\£[^ ]+)/u", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                $converted = self::calcRateToBGN("GBP", mb_strtolower($value), "£");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        preg_match_all("/(?<gbp>[^ ]+\\£)/u", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                $converted = self::calcRateToBGN("GBP", mb_strtolower($value), "£");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        preg_match_all("/(?<gbp>[^ ]+ [GBP]{3})/i", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                $converted = self::calcRateToBGN("GBP", mb_strtolower($value), "gbp");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        // convert to CHF f

        preg_match_all("/(?<chf>[^ ]+Fr)/u", $str, $amount_array);
        if ($amount_array['chf']) {
            #print_r($amount_array);
            foreach ($amount_array['chf'] as $value) {
                $converted = self::calcRateToBGN("CHF", mb_strtolower($value), "Fr");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        preg_match_all("/(?<chf>[^ ]+ [CHF]{3})/i", $str, $amount_array);
        if ($amount_array['chf']) {
            #print_r($amount_array);
            foreach ($amount_array['chf'] as $value) {
                $converted = self::calcRateToBGN("CHF", mb_strtolower($value), "chf");
                $str = str_replace($value, self::formatNum($converted, ",") . "лв ", $str);
            }
        }

        return $str;
    }
    // convert currency to USD $
    private static function USDmatch($str = "")
    {

        // convert from BGN

        preg_match_all("/(?<bgn>[^ ]+лв)/", $str, $amount_array);
        if ($amount_array['bgn']) {
            foreach ($amount_array['bgn'] as $value) {
                $converted = (self::convertToNum(str_replace("лв", "", $value), '.') / $_SESSION["Rates"]["USD"]);
                $str = str_replace($value, self::formatNumUSD($converted), $str);
            }
        }

        preg_match_all("/(?<bgn>[^ ]+ [BGN]{3})/i", $str, $amount_array);
        if ($amount_array['bgn']) {
            foreach ($amount_array['bgn'] as $value) {
                $converted = (self::convertToNum(str_replace("bgn", "", mb_strtolower($value)), '.') / $_SESSION["Rates"]["USD"]);
                $str = str_replace($value, self::formatNumUSD($converted), $str);
            }
        }

        // convert form GBP

        preg_match_all("/(?<gbp>\\£[^ ]+)/u", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("£", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["GBP"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["USD"]);
                $str = str_replace($value, self::formatNumUSD($converted), $str);
            }
        }

        preg_match_all("/(?<gbp>[^ ]+ [GBP]{3})/i", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("gbp", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["GBP"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["USD"]);
                $str = str_replace($value, self::formatNumUSD($converted), $str);
            }
        }

        // convert form CHF

        preg_match_all("/(?<chf>[^ ]+Fr)/u", $str, $amount_array);
        if ($amount_array['chf']) {
            foreach ($amount_array['chf'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("Fr", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["CHF"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["USD"]);
                $str = str_replace($value, self::formatNumUSD($converted), $str);
            }
        }

        preg_match_all("/(?<chf>[^ ]+ [CHF]{3})/i", $str, $amount_array);
        if ($amount_array['chf']) {
            foreach ($amount_array['chf'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("chf", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["CHF"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["USD"]);
                $str = str_replace($value, self::formatNumUSD($converted), $str);
            }
        }

        return $str;
    }
    // convert currency to GBP £
    private static function GBPmatch($str = "")
    {

        // convert from GBP

        preg_match_all("/(?<bgn>[^ ]+лв)/", $str, $amount_array);
        if ($amount_array['bgn']) {
            #print_r($amount_array['bgn']);
            foreach ($amount_array['bgn'] as $value) {
                $converted = (self::convertToNum(str_replace("лв", "", $value), '.') / $_SESSION["Rates"]["GBP"]);
                #echo $converted . " /";
                $str = str_replace($value, self::formatNumGBP($converted), $str);
            }
        }

        preg_match_all("/(?<bgn>[^ ]+ [BGN]{3})/i", $str, $amount_array);
        if ($amount_array['bgn']) {
            foreach ($amount_array['bgn'] as $value) {
                $converted = (self::convertToNum(str_replace("bgn", "", mb_strtolower($value)), '.') / $_SESSION["Rates"]["GBP"]);
                $str = str_replace($value, self::formatNumGBP($converted), $str);
            }
        }

        // convert form USD

        preg_match_all("/(?<usd>\\$[^ ]+)/u", $str, $amount_array);
        if ($amount_array['usd']) {
            foreach ($amount_array['usd'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("$", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["USD"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["GBP"]);
                $str = str_replace($value, self::formatNumGBP($converted), $str);
            }
        }

        preg_match_all("/(?<usd>[^ ]+ [USD]{3})/i", $str, $amount_array);
        if ($amount_array['usd']) {
            foreach ($amount_array['usd'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("usd", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["USD"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["GBP"]);
                $str = str_replace($value, self::formatNumGBP($converted), $str);
            }
        }

        // convert form CHF

        preg_match_all("/(?<chf>[^ ]+Fr)/u", $str, $amount_array);
        if ($amount_array['chf']) {
            foreach ($amount_array['chf'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("Fr", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["CHF"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["GBP"]);
                $str = str_replace($value, self::formatNumGBP($converted), $str);
            }
        }

        preg_match_all("/(?<chf>[^ ]+ [CHF]{3})/i", $str, $amount_array);
        if ($amount_array['chf']) {
            foreach ($amount_array['chf'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("chf", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["CHF"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["GBP"]);
                $str = str_replace($value, self::formatNumGBP($converted), $str);
            }
        }

        return $str;
    }
    // convert currency to CHF f
    private static function CHFmatch($str = "")
    {

        // convert from BGN

        preg_match_all("/(?<bgn>[^ ]+лв)/", $str, $amount_array);
        if ($amount_array['bgn']) {
            foreach ($amount_array['bgn'] as $value) {
                $converted = (self::convertToNum(str_replace("лв", "", $value), '.') / $_SESSION["Rates"]["CHF"]);
                $str = str_replace($value, self::formatNumCHF($converted), $str);
            }
        }

        preg_match_all("/(?<bgn>[^ ]+ [BGN]{3})/i", $str, $amount_array);
        if ($amount_array['bgn']) {
            foreach ($amount_array['bgn'] as $value) {
                $converted = (self::convertToNum(str_replace("bgn", "", mb_strtolower($value)), '.') / $_SESSION["Rates"]["CHF"]);
                $str = str_replace($value, self::formatNumCHF($converted), $str);
            }
        }

        // convert form USD

        preg_match_all("/(?<usd>\\$[^ ]+)/u", $str, $amount_array);
        if ($amount_array['usd']) {
            foreach ($amount_array['usd'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("$", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["USD"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["CHF"]);
                $str = str_replace($value, self::formatNumCHF($converted), $str);
            }
        }

        preg_match_all("/(?<usd>[^ ]+ [USD]{3})/i", $str, $amount_array);
        if ($amount_array['usd']) {
            foreach ($amount_array['usd'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("usd", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["USD"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["CHF"]);
                $str = str_replace($value, self::formatNumCHF($converted), $str);
            }
        }

        // convert form GBP

        preg_match_all("/(?<gbp>\\£[^ ]+)/u", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("£", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["GBP"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["CHF"]);
                $str = str_replace($value, self::formatNumCHF($converted), $str);
            }
        }

        preg_match_all("/(?<gbp>[^ ]+ [GBP]{3})/i", $str, $amount_array);
        if ($amount_array['gbp']) {
            foreach ($amount_array['gbp'] as $value) {
                // convert to bgn and then to usd
                $convertedBGN = (self::convertToNum(str_replace("gbp", "", mb_strtolower($value)), '.') * $_SESSION["Rates"]["GBP"]);
                $converted = ($convertedBGN / $_SESSION["Rates"]["CHF"]);
                $str = str_replace($value, self::formatNumCHF($converted), $str);
            }
        }

        return $str;
    }


    // format money https://www.php.net/manual/en/function.money-format.php
    /*
    That it is an implementation of the function money_format for the
    platforms that do not it bear. 

    The function accepts to same string of format accepts for the
    original function of the PHP. 

    (Sorry. my writing in English is very bad) 

    The function is tested using PHP 5.1.4 in Windows XP
    and Apache WebServer.
    */
    public static function  money_format($format, $number)
    {
        $regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' .
            '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
        if (setlocale(LC_MONETARY, 0) == 'C') {
            setlocale(LC_MONETARY, '');
        }
        $locale = localeconv();
        preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
        foreach ($matches as $fmatch) {
            $value = floatval($number);
            $flags = array(
                'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ?
                    $match[1] : ' ',
                'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
                'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                    $match[0] : '+',
                'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
                'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
            );
            $width      = trim($fmatch[2]) ? (int) $fmatch[2] : 0;
            $left       = trim($fmatch[3]) ? (int) $fmatch[3] : 0;
            $right      = trim($fmatch[4]) ? (int) $fmatch[4] : $locale['int_frac_digits'];
            $conversion = $fmatch[5];

            $positive = true;
            if ($value < 0) {
                $positive = false;
                $value  *= -1;
            }
            $letter = $positive ? 'p' : 'n';

            $prefix = $suffix = $cprefix = $csuffix = $signal = '';

            $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
            switch (true) {
                case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                    $prefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                    $suffix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                    $cprefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                    $csuffix = $signal;
                    break;
                case $flags['usesignal'] == '(':
                case $locale["{$letter}_sign_posn"] == 0:
                    $prefix = '(';
                    $suffix = ')';
                    break;
            }
            if (!$flags['nosimbol']) {
                $currency = $cprefix . ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                    $csuffix;
            } else {
                $currency = '';
            }
            $space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';

            $value = number_format(
                $value,
                $right,
                $locale['mon_decimal_point'],
                $flags['nogroup'] ? '' : $locale['mon_thousands_sep']
            );
            $value = @explode($locale['mon_decimal_point'], $value);

            $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
            if ($left > 0 && $left > $n) {
                $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
            }
            $value = implode($locale['mon_decimal_point'], $value);
            if ($locale["{$letter}_cs_precedes"]) {
                $value = $prefix . $currency . $space . $value . $suffix;
            } else {
                $value = $prefix . $value . $space . $currency . $suffix;
            }
            if ($width > 0) {
                $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                    STR_PAD_RIGHT : STR_PAD_LEFT);
            }

            $format = str_replace($fmatch[0], $value, $format);
        }
        return $format;
    }
}
