<?php

namespace Converter;

use stdClass;

class CurrencyConverter extends stdClass
{

    private static $feed = 'http://www.bnb.bg/PressOffice/PORSS/index.htm?getRSS=1&lang=BG&cat=1';
    private static $feedObj = null;
    public static $arrObs = [];

    public static function getCurrencies()
    {
        // reset 
        self::$arrObs = [];
        // get rss feed
        self::$feedObj = simplexml_load_file(self::$feed, "SimpleXMLElement", LIBXML_NOCDATA);
        // include simple html dom for easier dom manipulation
        include_once('./simple_html_dom.php');

        #print_r(self::$feedObj);

        // set values
        $html = str_get_html(self::$feedObj->channel->item->description);
        foreach($html->find('ul') as $ul) {
            foreach($ul->find('li') as $li){
                #print_r($li);
                $tempObj = new stdClass();
                // get only the currency name!
                preg_match('/[A-Z]+/i', $li->find('em', 0)->plaintext,$matches);
                //print_r($matches);
                $tempObj->currency = trim($matches[0]);// $li->find('em', 0)->plaintext;//children('em')->innertext;
                $tempObj->value =  $li->find('strong', 0)->plaintext;//children('strong')->innertext;
                #print_r($tempObj);
                self::$arrObs[] = $tempObj;
            }
                #echo $li->innertext . '<br>';
        }
        #print_r(self::$arrObs);

        return self::$arrObs;
    }
}
