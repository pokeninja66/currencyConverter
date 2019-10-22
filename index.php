<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <meta charset="utf-8">
    <title>Currency Converter</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

    <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/skeleton.css">

    <!-- JS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <script src="assets/script/jquery-3.4.1.js"></script>


</head>

<body>

    <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
    <div class="container">
        <div class="row">
            <div class="column">
                <h4>Hello there. This is a currency converter.</h4>
                <p>Here are the valid formats for conversion:</p>
                <ul>
                    <li>
                        Formats for BGN (лв)
                        <ul>
                            <li>10 BGN</li>
                            <li>10лв</li>
                        </ul>
                    </li>
                    <li>
                        Formats for USD ($)
                        <ul>
                            <li>10 USD</li>
                            <li>$10</li>
                        </ul>
                    </li>
                    <li>
                        Formats for GBP (£)
                        <ul>
                            <li>10 GBP</li>
                            <li>£10</li>
                        </ul>
                    </li>
                    <li>
                        Formats for CHF (Fr)
                        <ul>
                            <li>10 CHF</li>
                            <li>10Fr</li>
                        </ul>
                    </li>
                </ul>
                <h3>Here is a simple example.</h3>
                <p style="color:crimson">
                    This is the start of the text and lets add some $100 dolars to start and 13 USD. <br>
                    Lets add some dates 20-Oct-19 and some £15 or 20 GBP. Maybe some 10лв and some 23 BGN. <br>
                    Yeah I know this example is shit but here are some 55 CHF and 87Fr. Maybe some more numbers 131 2 31 3. <br>
                </p>
            </div>
            <div class="columns converter-text">
                <input type="hidden" name="action" value="convertTo" />

                <div class="row">
                    <div class="12-columns">
                        <textarea class="u-full-width" name="Form[inputText]" placeholder="Enter your text to convert" id="inputText" rows="4"></textarea>
                    </div>
                    <div class="six columns">
                        <label for="Currency">Currency</label>
                        <select class="u-full-width" id="Currency" name="Form[Currency]"></select>
                    </div>
                    <div class="six columns">
                        <a id="convertBTN" class="button button-primary" href="#">Convert</a>
                    </div>
                </div>

                <div class="row">
                    <div class="12-columns">
                        <textarea style="display:none;" class="u-full-width" id="converted" rows="4"></textarea>
                    </div>
                    <br />
                </div>
            </div>
            <script>
                $(document).ready(function() {

                    $("#convertBTN").click(function(e) {
                        e.preventDefault();


                        if ($("#inputText").val() === "") {
                            alert("You need to add a some text!");
                            return false;
                        }

                        $.post("./assets/inc/actions.php", $('.converter-text').find("input,select,textarea").serializeArray(), function(data) {
                            console.log("Data Loaded: " + data);
                            $("#converted").val(data.text);

                            if (!$("#converted").is(":visible")) {
                                $("#converted").show();
                            }
                        });

                    });
                    // get options
                    $.post("./assets/inc/actions.php", {
                        action: "getCurrencies"
                    }, function(data) {
                        //onsole.log("Data Loaded: " + data);
                        $.each(data, function(key, obj) {
                            //console.log(key + ": " + obj.currency + " val:" + obj.value);
                            $("#Currency").append(`<option value="` + obj.currency + `;` + obj.value + `">` + obj.currency + `</option>`);
                        });
                    });

                });
            </script>

        </div>
    </div>

    <!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
</body>

</html>