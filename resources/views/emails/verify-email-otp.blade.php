<!DOCTYPE html>
<html lang="sw" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Kodi ya Uhakiki wa Email - SKYpesa</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    
    <!-- Preheader Text -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        Kodi yako ya uhakiki ni {{ $otp }}. Itatumika kwa dakika 15 tu.
    </div>
    
    <!-- Main Container -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f7fa;">
        <tr>
            <td style="padding: 40px 20px;">
                
                <!-- Email Content -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">SKYpesa</h1>
                            <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Uhakiki wa Email</p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <!-- Message -->
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Habari,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Asante kwa kujiunga na SKYpesa. Ili kukamilisha usajili wako, tafadhali tumia kodi ifuatayo ya uhakiki (OTP):
                            </p>
                            
                            <!-- OTP Code Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="text-align: center; padding: 25px 0;">
                                        <div style="display: inline-block; background-color: #f0fdf4; border: 2px dashed #10b981; border-radius: 12px; padding: 20px 40px;">
                                            <span style="font-size: 36px; font-weight: 800; color: #059669; letter-spacing: 8px; font-family: 'Courier New', monospace;">{{ $otp }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Warning Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fef3c7; border-radius: 8px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
                                            <strong>Muhimu:</strong> Kodi hii itatumika kwa dakika 15 tu. Usimpe mtu yeyote kodi hii.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Help Text -->
                            <p style="margin: 0; color: #718096; font-size: 14px; line-height: 1.6;">
                                Kama hukuomba kujiunga na SKYpesa, puuza email hii.
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Divider -->
                    <tr>
                        <td style="padding: 0 30px;">
                            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 0;">
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px; text-align: center;">
                            <p style="margin: 0 0 10px 0; color: #a0aec0; font-size: 13px;">
                                &copy; {{ date('Y') }} SKYpesa. Haki zote zimehifadhiwa.
                            </p>
                            <p style="margin: 0; color: #a0aec0; font-size: 12px;">
                                Email hii imetumwa kwa sababu ya ombi la uhakiki wa email.
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>
