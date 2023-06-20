<html lang="en">

<head>
    <meta charset="UTF-8">


    <link rel="apple-touch-icon" type="image/png"
        href="https://cpwebassets.codepen.io/assets/favicon/apple-touch-icon-5ae1a0698dcc2402e9712f7d01ed509a57814f994c660df9f7a952f3060705ee.png">

    <meta name="apple-mobile-web-app-title" content="CodePen">

    <link rel="shortcut icon" type="image/x-icon"
        href="https://cpwebassets.codepen.io/assets/favicon/favicon-aec34940fbc1a6e787974dcd360f2c6b63348d4b1f4e06c77743096d55480f33.ico">

    <link rel="mask-icon" type="image/x-icon"
        href="https://cpwebassets.codepen.io/assets/favicon/logo-pin-b4b4269c16397ad2f0f7a01bcdf513a1994f4c94b8af2f191c09eb0d601762b1.svg"
        color="#111">




    <title>Warranty Voucher</title>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">

    <style>
        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: 400;
            font-stretch: normal;
            src: url(https://fonts.gstatic.com/s/opensans/v35/memSYaGs126MiZpBA-UvWbX2vVnXBbObj2OVZyOOSr4dVJWUgsjZ0B4gaVc.ttf) format('truetype');
        }

        @font-face {
            font-family: 'Pinyon Script';
            font-style: normal;
            font-weight: 400;
            src: url(https://fonts.gstatic.com/s/pinyonscript/v18/6xKpdSJbL9-e9LuoeQiDRQR8WOXaPw.ttf) format('truetype');
        }

        @font-face {
            font-family: 'Rochester';
            font-style: normal;
            font-weight: 400;
            src: url(https://fonts.gstatic.com/s/rochester/v18/6ae-4KCqVa4Zy6Fif-UC2FHS.ttf) format('truetype');
        }

        .cursive {
            font-family: 'Pinyon Script', cursive;
        }

        .sans {
            font-family: 'Open Sans', sans-serif;
        }

        .bold {
            font-weight: bold;
        }

        .block {
            display: block;
        }

        .underline {
            border-bottom: 1px solid #777;
            padding: 5px;
            margin-bottom: 15px;
        }

        .margin-0 {
            margin: 0;
        }

        .padding-0 {
            padding: 0;
        }

        .pm-empty-space {
            height: 40px;
            width: 100%;
        }

        body {
            padding: 20px 0;
            background: #ccc;
        }

        .pm-certificate-container {
            position: relative;
            width: 800px;
            height: 600px;
            background-color: #618597;
            padding: 30px;
            color: #333;
            font-family: 'Open Sans', sans-serif;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            /*background: -webkit-repeating-linear-gradient(
      45deg,
      #618597,
      #618597 1px,
      #b2cad6 1px,
      #b2cad6 2px
    );
    background: repeating-linear-gradient(
      90deg,
      #618597,
      #618597 1px,
      #b2cad6 1px,
      #b2cad6 2px
    );*/
        }

        .pm-certificate-container .outer-border {
            width: 794px;
            height: 594px;
            position: absolute;
            left: 50%;
            margin-left: -397px;
            top: 50%;
            margin-top: -297px;
            border: 2px solid #fff;
        }

        .pm-certificate-container .inner-border {
            width: 730px;
            height: 530px;
            position: absolute;
            left: 50%;
            margin-left: -365px;
            top: 50%;
            margin-top: -265px;
            border: 2px solid #fff;
        }

        .pm-certificate-container .pm-certificate-border {
            position: relative;
            width: 720px;
            height: 520px;
            padding: 0;
            border: 1px solid #E1E5F0;
            background-color: #ffffff;
            background-image: none;
            left: 50%;
            margin-left: -360px;
            top: 50%;
            margin-top: -260px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-block {
            width: 650px;
            height: 200px;
            position: relative;
            left: 50%;
            margin-left: -325px;
            top: 70px;
            margin-top: 0;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-header {
            margin-bottom: 10px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-title {
            position: relative;
            top: 40px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-title h2 {
            font-size: 34px !important;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-body {
            padding: 20px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-body .pm-name-text {
            font-size: 20px;
        }

        .pm-certificate-container .pm-certificate-border .pm-earned {
            margin: 15px 0 20px;
        }

        .pm-certificate-container .pm-certificate-border .pm-earned .pm-earned-text {
            font-size: 20px;
        }

        .pm-certificate-container .pm-certificate-border .pm-earned .pm-credits-text {
            font-size: 15px;
        }

        .pm-certificate-container .pm-certificate-border .pm-course-title .pm-earned-text {
            font-size: 20px;
        }

        .pm-certificate-container .pm-certificate-border .pm-course-title .pm-credits-text {
            font-size: 15px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certified {
            font-size: 12px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certified .underline {
            margin-bottom: 5px;
        }

        .pm-certificate-container .pm-certificate-border .pm-certificate-footer {
            width: 650px;
            height: 100px;
            position: relative;
            left: 50%;
            margin-left: -325px;
            bottom: -105px;
        }
    </style>

    <script>
        window.console = window.console || function(t) {};
    </script>



</head>

<body translate="no" data-new-gr-c-s-check-loaded="14.1112.0" data-gr-ext-installed="">

    <div class="container pm-certificate-container">
        <div class="outer-border"></div>
        <div class="inner-border"></div>

        <div class="pm-certificate-border col-xs-12">
            <div class="row pm-certificate-header">
                <div class="pm-certificate-title cursive col-xs-12 text-center">
                    <h2>MooCommerce Warranty Voucher</h2>
                </div>
            </div>

            <div class="row pm-certificate-body">

                <div class="pm-certificate-block">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-2">
                                <!-- LEAVE EMPTY -->
                            </div>
                            <div class="pm-certificate-name underline margin-0 col-xs-8 text-center">
                                <span class="pm-name-text bold">Product SKU : {{$warranty->product_sku}}</span>
                            </div>
                            <div class="col-xs-2">
                                <!-- LEAVE EMPTY -->
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-2">
                                <!-- LEAVE EMPTY -->
                            </div>
                            <div class="pm-course-title col-xs-8 text-center">
                                <span class="pm-earned-text block cursive">5 year warranty assured</span>
                            </div>
                            <div class="col-xs-2">
                                <!-- LEAVE EMPTY -->
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-2">
                                <!-- LEAVE EMPTY -->
                            </div>
                            <div class="pm-course-title underline col-xs-8 text-center">
                                <span class="pm-credits-text block bold sans">{{$customer->email}}</span>
                                {{$warranty->blockchain_UUID}}
                            </div>
                            <div class="col-xs-2">
                                <!-- LEAVE EMPTY -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12">
                    <div class="row">
                        <div class="pm-certificate-footer">
                            <div class="col-xs-4 pm-certified col-xs-4 text-center">
                                <span class="pm-credits-text block sans">Owners Wallet</span>
                                <span class="pm-empty-space block underline"></span>
                                <span class="bold block">{{$warranty->blockchain_nft_owner_wallet_address}}</span>
                            </div>
                            <div class="col-xs-4">
                                <!-- LEAVE EMPTY -->
                            </div>
                            <div class="col-xs-4 pm-certified col-xs-4 text-center">
                                <span class="pm-credits-text block sans">Activation Date</span>
                                <span class="pm-empty-space block underline"></span>
                                <span class="bold block">{{$warranty->updated_at}}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>





</body>

</html>
