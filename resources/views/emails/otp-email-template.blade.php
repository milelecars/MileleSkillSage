<!DOCTYPE html>
<html>
<head>
    <title>Your OTP Code - Milele SkillSage</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #0056b3;">One-Time Password (OTP) - Milele SkillSage</h1>
        
        <p>Dear {{ $admin->name }},</p>
        
        <p>Your OTP code for authentication is:</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <span style="font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #0056b3;">{{ $otp }}</span>
        </div>
        
        <p>Please note that this code will expire in 5 minutes for security purposes.</p>
        
        <p>If you did not request this OTP, please ignore this email and ensure your account security.</p>
        
        <p>
            Best regards,<br>
            Milele SkillSage Team<br>
            Milele Motors
        </p>
    </div>
</body>
</html>