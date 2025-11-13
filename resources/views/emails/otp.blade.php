<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ env('APP_NAME') }} - Password Reset Request</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fa; font-family: Arial, sans-serif;">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f5f7fa">
    <tr>
      <td align="center" style="padding:30px 10px;">
        
        <!-- Main Container -->
        <table width="480" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" style="border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
          <tr>
            <td align="center" style="padding:30px 20px;">

              <!-- Logo -->
              <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="center">
                    <img src="{{asset('assets/img/logo.png')}}" alt="Homee Logo" style="width: 100px;" width="100px">
                
                  </td>
                </tr>
              </table>

              <!-- Title -->
              <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:20px;">
                <tr>
                  <td align="center" style="font-size:20px; font-weight:bold; color:#333333; padding-bottom:10px;">
                    Password Reset Request 🔑
                  </td>
                </tr>
              </table>

              <!-- Message -->
              <table border="0" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                  <td align="center" style="font-size:16px; color:#555555; line-height:1.6; padding:10px 0;">
                    Hi Homee,
                  </td>
                </tr>
                <tr>
                  <td align="center" style="font-size:16px; color:#555555; line-height:1.6;">
                    We received a request to OTP verification. Please use the verification code below to proceed. 
                    <br>(This code will expire in <strong>15 minutes</strong>):
                  </td>
                </tr>
              </table>

              <!-- Code Box -->
              <table border="0" cellspacing="0" cellpadding="0" style="margin:20px auto;">
                <tr>
                  <td align="center" bgcolor="#27ae60" style="color:#ffffff; font-size:28px; font-weight:bold; padding:14px 24px; border-radius:8px; letter-spacing:2px;">
                    {{ $otp }}
                  </td>
                </tr>
              </table>

              <!-- Footer -->
              <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:20px;">
                <tr>
                  <td align="center" style="font-size:14px; color:#777777; line-height:1.6;">
                    If you did not request this, please ignore this message.<br><br>
                    — <strong style="color:#000000;">The Homee Team</strong> 💛
                  </td>
                </tr>
              </table>

            </td>
          </tr>
        </table>
        <!-- End Container -->

      </td>
    </tr>
  </table>
</body>
</html>
