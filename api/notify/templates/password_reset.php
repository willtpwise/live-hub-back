<?php
function password_reset_template ($user, $data) {
  $first_name = $user['first_name'];
  $challenge = $data['challenge'];
  $id = $user['id'];

  $link = front_end_home() . "/app/login/reset-password/stage-2?challenge=$challenge&id=$id";

  return <<<HEREDOC
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <style type="text/css">
    #outlook a {
      padding: 0;
    }

    .ReadMsgBody {
      width: 100%;
    }

    .ExternalClass {
      width: 100%;
    }

    .ExternalClass * {
      line-height: 100%;
    }

    body {
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    table,
    td {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }

    img {
      border: 0;
      height: auto;
      line-height: 100%;
      outline: none;
      text-decoration: none;
      -ms-interpolation-mode: bicubic;
    }

    p {
      display: block;
      margin: 13px 0;
    }
  </style>
  <!--[if !mso]><!-->
  <style type="text/css">
    @media only screen and (max-width:480px) {
      @-ms-viewport {
        width: 320px;
      }
      @viewport {
        width: 320px;
      }
    }
  </style>
  <!--<![endif]-->
  <!--[if mso]>
<xml>
  <o:OfficeDocumentSettings>
    <o:AllowPNG/>
    <o:PixelsPerInch>96</o:PixelsPerInch>
  </o:OfficeDocumentSettings>
</xml>
<![endif]-->
  <!--[if !mso]><!-->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700" rel="stylesheet" type="text/css">
  <style type="text/css">
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700);
  </style>
  <!--<![endif]-->
  <style type="text/css">
    @media only screen and (min-width:480px) {
      .mj-column-per-100,
      * [aria-labelledby="mj-column-per-100"] {
        width: 100%!important;
      }
      .mj-column-per-30,
      * [aria-labelledby="mj-column-per-30"] {
        width: 30%!important;
      }
      .mj-column-per-8,
      * [aria-labelledby="mj-column-per-8"] {
        width: 8%!important;
      }
    }
  </style>
</head>

<body style="background: #f1f1f1;">
  <div style="background-color:#f1f1f1;">
    <div style="display:none;font-size:1px;color:#333333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;" id="pre-header" class="mktEditable"></div>
    <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
<tr>
<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->
    <table cellpadding="0" cellspacing="0" style="background:#ffc13c;font-size:0px;width:100%;" border="0">
      <tbody>
        <tr>
          <td>
            <div style="margin:0 auto;max-width:600px;">
              <table cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
                <tbody>
                  <tr>
                    <td style="text-align:center;vertical-align:top;font-size:0px;padding:20px 0px 40px;">
                      <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td style="vertical-align:top;width:600px;">
      <![endif]-->
                      <div aria-labelledby="mj-column-per-100" class="mj-column-per-100" style="vertical-align:top;display:inline-block;font-size:13px;text-align:left;width:100%;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0">
                          <tbody>
                            <tr>
                              <td style="word-break:break-word;font-size:0px;padding:10px 50px 20px;" align="center">
                                <table cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0px;" align="center" border="0">
                                  <tbody>
                                    <tr>
                                      <td style="width:80px;" class="mktEditable" id="header-logo">
                                        <a href="http://www.siteminder.com/?utm_source=email_prospect&utm_medium=email&utm_campaign=-2017---SM-EMAIL-HEADER-&brand=&noredirect=1&country=&productinterest=" target="_blank">
                                          <img alt="SiteMinder" title="" height="80" src="https://s3-ap-southeast-2.amazonaws.com/live-hub-static/square-160x160.png" style="border:none;outline:none;text-decoration:none;height:80px;" width="80">
                                        </a>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                            <tr>
                              <td style="word-break:break-word;font-size:0px;padding:10px 50px;" align="center">
                                <div class="mktEditable" id="heading" style="cursor:auto;color:#5d5c63;font-family:Open Sans, Helvetica, Sans-Serif;font-size:24px;font-weight:500;line-height:26px;">
                                  Your password reset link
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
    <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
<tr>
<td style="font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->
    <table cellpadding="0" cellspacing="0" style="background:#ffffff;font-size:0px;width:100%;" border="0">
      <tbody>
        <tr>
          <td>
            <div style="margin:0 auto;max-width:600px;">
              <table cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
                <tbody>
                  <tr>
                    <td style="text-align:center;vertical-align:top;font-size:0px;padding:35px 50px;">
                      <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td style="vertical-align:undefined;width:600px;">
      <![endif]-->
                      <div style="margin:0 auto;max-width:600px;background:#ffffff;">
                        <table cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;background:#ffffff;" align="center" border="0">
                          <tbody>
                            <tr>
                              <td style="text-align:center;vertical-align:top;font-size:0px;padding:0px;">
                                <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td style="vertical-align:top;width:600px;">
      <![endif]-->
                                <div aria-labelledby="mj-column-per-100" class="mj-column-per-100" style="vertical-align:top;display:inline-block;font-size:13px;text-align:left;width:100%;">
                                  <table cellpadding="0" cellspacing="0" width="100%" border="0">
                                    <tbody>
                                      <tr>
                                        <td style="word-break:break-word;font-size:0px;padding:0px 0px 35px 0px;" align="left">
                                          <div style="cursor:auto;color:#000000;font-family:Open Sans, Helvetica, Sans-Serif;font-size:14px;font-weight:200;line-height:22px;">
                                            <div class="mktEditable" id="bodyCopy">
                                              Hey $first_name<br><br>
                                              You recently requested a new password for your LiveHUB account.<br><br>

                                              Follow the link below to reset your password. Heads up, it's only valid for one hour.<br><br>

                                              <strong>Thanks!</strong><br>
                                              The LiveHUB Team
                                            </div>
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td style="word-break:break-word;font-size:0px;padding:10px;" align="center">
                                          <table cellpadding="0" cellspacing="0" align="center" border="0">
                                            <tbody>
                                              <tr>
                                                <td style="border-radius:4px;color:#ffffff;cursor:auto;padding:0;" align="center" valign="middle" bgcolor="#ffc13c">
                                                  <div class="mktEditable" id="bodyCopyCTA"><a href="$link" style="display:inline-block;text-decoration:none;background:#ffc13c;border-radius:4px;color:#5d5c63;font-family:Open sans, Helvetica, Arial, Sans serif;font-size:14px;font-weight:500;margin:0px;padding:10px
25px;letter-spacing:1px;border:1px solid #5d5c63;" target="_blank">RESET PASSWORD</a></div>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </div>
                                <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
    <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0" width="600" align="center" style="width:600px;">
<tr>
<td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
      <![endif]-->
    <table cellpadding="0" cellspacing="0" style="background:#5d5c63;font-size:0px;width:100%;" border="0">
      <tbody>
        <tr>
          <td>
            <div style="margin:0 auto;max-width:600px;">
              <table cellpadding="0" cellspacing="0" style="font-size:0px;width:100%;" align="center" border="0">
                <tbody>
                  <tr>
                    <td style="text-align:center;vertical-align:top;font-size:0px;padding:20px 0px;">
                      <!--[if mso | IE]>

<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td style="vertical-align:top;width:600px;">
      <![endif]-->
                      <div aria-labelledby="mj-column-per-100" class="mj-column-per-100" style="vertical-align:top;display:inline-block;font-size:13px;text-align:left;width:100%;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0">
                          <tbody>
                            <tr>
                              <td style="word-break:break-word;font-size:0px;padding:0px;" align="center">
                                <div style="cursor:auto;color:#ffffff;font-family:Arial, sans-seif;font-size:10px;font-weight:200;line-height:22px;">
                                  You are receiving this email because you are LiveHUB user. <br></bvr>Contact <a href="mailto:info@livehub.com.au" style="color:#ffffff;">info@livehub.com.au</a> for more information about system notifications.
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    <!--[if mso | IE]>
      </td>
</tr>
</table>
<![endif]-->
  </div>
</body>

</html>
HEREDOC;
}
