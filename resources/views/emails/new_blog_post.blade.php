<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daily Expense Tracker</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0" style="max-width:480px;width:100%;background:#fefefe;border-radius:10px;padding:24px;border:1px solid #10b981;box-shadow:0 2px 6px rgba(0,0,0,0.04);">
                <tr>
                    <td style="text-align:center;">
                        <a href="https://dailyexpensetracker.in" style="display:inline-block;">
                            <img src="https://dailyexpensetracker.in/images/dailyexpensetracker.png" alt="Daily Expense Tracker" style="height:36px;border-radius:8px;">
                        </a>
                        <h2 style="font-size:20px;color:#10b981;margin:20px 0 10px;">Hey {{ $user->name ?? 'there' }} 👋</h2>
                        <p style="font-size:15px;color:#1e1e1e;margin:0 0 16px;line-height:22px;">
                            We just published a fresh blog post on Daily Expense Tracker:<br>
                            <strong>"{{ $blogTitle }}"</strong>
                        </p>
                
                        <a href="{{ $blogUrl }}"
                           style="display:inline-block;margin:20px 0;background:#10b981;color:#fff;text-decoration:none;
                        padding:10px 24px;border-radius:6px;font-size:14px;font-weight:bold;">
                            Read the Article
                        </a>

                        <p style="font-size:13px;color:#666;margin-top:24px;line-height:20px;">
                            Got questions or feedback?<br>
                            Just reply to this email or reach me at <a href="mailto:gurpreetkait.codes@gmail.com" style="color:#10b981;text-decoration:none;">gurpreetkait.codes@gmail.com</a>.
                        </p>

                        <p style="font-size:13px;color:#aaa;margin-top:30px;">
                            — Gurpreet, Creator of Daily Expense Tracker
                        </p>
                    </td>
                </tr>
            </table>
            <p style="text-align:center;font-size:12px;color:#aaa;margin-top:18px;">
                <a href="https://dailyexpensetracker.in" style="color:#10b981;text-decoration:none;">
                    dailyexpensetracker.in
                </a>
            </p>
        </td>
    </tr>
</table>
</body>
</html> 