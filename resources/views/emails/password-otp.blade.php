<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .otp-code { font-size: 32px; font-weight: 800; color: #10b981; text-align: center; letter-spacing: 5px; margin: 20px 0; padding: 10px; background: #f0fdf4; border-radius: 8px; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="color: #10b981;">SKYpesa</h2>
        </div>
        <p>Habari,</p>
        <p>Tumepokea ombi la kubadilisha nenosiri lako la SKYpesa. Tafadhali tumia kodi ifuatayo ya uhakiki (OTP) ili kuendelea:</p>
        
        <div class="otp-code">{{ $otp }}</div>
        
        <p>Kodi hii itatumika kwa dakika 15 tu. Kama hukuomba kubadilisha nenosiri, tafadhali puuza barua pepe hii.</p>
        
        <p>Asante,<br>Timu ya SKYpesa</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} SKYpesa. Haki zote zimehifadhiwa.
        </div>
    </div>
</body>
</html>
