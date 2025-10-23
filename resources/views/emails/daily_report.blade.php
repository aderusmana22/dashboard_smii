<!DOCTYPE html>
<html>
<head>
    <title>Daily Finished Goods Stock Report</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td class="content" style="padding: 30px; font-size: 16px;">
                <p style="margin-top: 0;">Dear <strong>{{ $userName }}</strong>,</p>
                <p>Warm greetings,</p>
                <p>Attached to this email is the latest <strong>Daily Finished Goods Stock Report</strong> in Excel format. <br>
                   This report contains the stock data as of <strong>{{ $reportDate }}</strong>.
                </p>
                <p>Please download the attached file to view the complete details.</p>
                <p>If you have any further questions, please do not hesitate to contact us.</p>
            </td>
        </tr>
        <tr>
            <td class="footer"
                style="background-color: #f4f4f4; color: #888888; padding: 20px; text-align: center; font-size: 12px; border-top: 1px solid #dddddd;">
                <p style="margin: 0;">&copy; {{ date('Y') }} PT. Sinar Meadow International Indonesia. All rights reserved.</p>
                <p style="margin: 10px 0 0;">This is an automated email. Please do not reply to this message.</p>
                <p style="margin: 10px 0 0;">Pulogadung Industrial Estate, Jl. Pulo Ayang, Jatinegara,<br>Cakung District, East Jakarta City, DKI Jakarta</p>
            </td>
        </tr>
    </table>
</body>
</html>