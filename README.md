# currencyConverter
This is a simple currency converter using php.

### How it works

The program first gets the current rates for the currencies from [BNB.bg](https://www.bnb.bg/)
using their own rss feed found [here](https://www.bnb.bg/PressOffice/PORSS/index.htm?getRSS=1&lang=BG&cat=1).

The feed has 3 currencies:

* USD ($)
* GBP (£)
* CHF (Fr)

When you enter text in the textarea and have selected your currency to convert to pres **Convert**

The script will then send a POST with the text string.

After that the text is scanned  using the **PHP** preg_match() function for text that matches a curtain currency and then replaces it with the converted value.

## Regular expression example for BGN
```php 
preg_match_all("/(?<bgn>[^ ]+лв)/", $str, $amount_array); 
```

## Accepted formats for currencies:

* BGN
  * 100лв
  * 100 BGN
* USD ($)
  * $100
  * 100 USD
* GBP (£)
  * $100
  * 100 USD
* CHF (Fr)
  * 100Fr
  * 100 CHF
