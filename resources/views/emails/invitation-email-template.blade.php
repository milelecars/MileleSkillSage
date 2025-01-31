<!DOCTYPE html>
<html>
<head>
    <title>Test Invitation from Milele Motors</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #0056b3;">Test Invitation from Milele Motors</h1>
        
        <p>Dear Candidate,</p>
        
        <p>We hope this email finds you well.</p>
        
        <p>On behalf of Milele Motors, we are delighted to invite you to take part in our assessment process.</p>
        
        <p>You have been selected to complete the <strong>"{{ $testName }}"</strong> assessment as part of our evaluation process for the role of <strong>"{{ $role }}"</strong>. This test will assist us in evaluating your skills and determining your potential fit within our organization.</p>
        
        <p>To begin the test, please click on the link below:</p>

        <p style="text-align: center;">
            <a href="{{ $invitationLink }}" 
            style="background-color: #0056b3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Start the Test
            </a>
        </p>

        <div style="background-color: #ffeeee; border: 1px solid #ff0000; padding: 10px; margin: 10px 0; text-align: center; border-radius: 5px;">
            ⚠️This test can ONLY be accessed from a desktop or laptop computer.
        </div>
        
        <p>Important Information:</p>
        <ul>
            <li>Make sure to use this email address to sign in</li>
            <li>The test is timed, so please ensure you have a quiet, uninterrupted period to complete it.</li>
            <li>Make sure you have a stable internet connection before starting.</li>
            <li>Read all instructions carefully before beginning each section.</li>
            <li>The test link will remain active for 48 hours, so please ensure you complete it within this period.</li>
        </ul>
        
        <p>For any technical issues or inquiries, please feel free to reach out to our HR department at <a href="mailto:testsupport@milele.com">testsupport@milele.com</a></p>
        
        <p>We appreciate your participation and look forward to reviewing your results.</p>
        
        <p>
            Best regards,<br>
            Human Resources Team<br>
            Milele Motors
        </p>
    </div>
</body>
</html>