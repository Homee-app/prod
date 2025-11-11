<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <title>{{ env('APP_NAME') }}</title>

</head>
<style>
    a {
        color: #3cbf5f;
    }
</style>

<body style="margin:0px; padding:0px; background: #eee;">

    <table style="max-width: 100%; background: #fff;" class="deviceWidth" width="660" cellspacing="0" cellpadding="0"
        border="0" align="center">
        <tbody>
            <tr>
                <td style="padding:0px;" bgcolor="#ffffff">

                    <table
                        style="width:100%; border-collapse:collapse; font-family: 'Roboto', sans-serif; font-size: 14px;">
                        <tr>
                            <td
                                style="background:#fff1e9; background-color: #fff1e9; color: #000; font-weight: bold; font-size: 20px; text-align:center; padding:15px 0 15px;">
                                  <img src="{{ asset('assets/images/emaillogo.png') }}"
                                    style="height: 100px; width: 100px;" />
                            </td>
                        </tr>
                        <tr>
                            <td
                                style="text-align:left; padding:20px 20px; word-break: break-word; color: #000000; font-size: 14px; ">

                                {{-- <p style="line-height: 28px;margin: 0 0 20px 0;color: #000000;">
          Welcome to <strong> {{ env('APP_NAME') }}</strong>
        </p> --}}

                                <p style="line-height: 28px;margin: 0 0 20px 0;color: #000000;">
                                    Hi {{ $fullname }},
                                </p>

                                {{-- <p>Your One-Time Password (OTP) for verifying your {{ env('APP_NAME') }} account is:</p> --}}
                                <p> We wanted to let you know that your <strong> subscription is expired. </strong> </p>
                                <br>
                                <p>To avoid any interruption in your access and continue enjoying all features of
                                    FitPro360X, please make sure to renew your subscription in time.</p> <br>
                                <p> No action is needed if you’ve already renewed it.</p> <br>


                                <p>Thanks, <br>
                                    <strong>Team {{ env('APP_NAME') }}</strong><br>
                                </p>

                            </td>
                        </tr>


                    </table>
                </td>
            </tr>
        </tbody>
    </table>


</body>

</html>
