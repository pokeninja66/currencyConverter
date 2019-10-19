<?php
/*
include_once('./simple_html_dom.php');
echo '<pre>';
$html = new simple_html_dom();
$url = 'http://www.unicreditbulbank.bg/en/rates-indexes/currency-rates/';
$url = str_replace(" ", "%20", $url);
// get DOM from URL or file
$html->load_file($url);
#print_r($html);
// find all link

print_r($html->find('.table--exchange'));
*/
$feed = 'http://www.bnb.bg/PressOffice/PORSS/index.htm?getRSS=1&lang=BG&cat=1';
$feed_Obj = simplexml_load_file($feed ,"SimpleXMLElement", LIBXML_NOCDATA);

  
#print_r($feed_Obj->channel->item);
echo '<pre>';

$arr = [];
include_once('./simple_html_dom.php');
$html = str_get_html($feed_Obj->channel->item->description);
foreach($html->find('ul') as $ul) {
    foreach($ul->find('li') as $li){
        #print_r($li);
        $tempObj = new stdClass();
        preg_match('/[A-Z]+/i', $li->find('em', 0)->plaintext,$matches);
        //print_r($matches);
        $tempObj->currency = trim($matches[0]);// $li->find('em', 0)->plaintext;//children('em')->innertext;
        $tempObj->value =  $li->find('strong', 0)->plaintext;//children('strong')->innertext;
        #print_r($tempObj);
        $arr[$matches[0]] = $tempObj;
    }
        #echo $li->innertext . '<br>';
}
print_r($arr);

$str = 'In My Cart : 11,12 items';
preg_match_all('!\d+!', $str, $matches);
print_r($matches);