<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background:#f2f4f6;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
               style="background:#ffffff;border-radius:12px;overflow:hidden;
                      box-shadow:0 2px 8px rgba(16,185,129,0.07);
                      border:2px solid #10b981;">
          <tr>
            <td style="padding:32px 40px 24px;text-align:left;">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                <img src="https://dailyexpensetracker.in/images/dailyexpensetracker.png" alt="Daily Expense Tracker" style="height:36px;border-radius:8px;">
                <span style="font-size:22px;font-weight:bold;color:#10b981;vertical-align:middle;">Daily Expense Tracker</span>
              </div>
              <h2 style="margin:0 0 16px;font-size:22px;color:#222;">Hi {{ $user->name }},</h2>

              <p style="margin:0 0 18px;font-size:16px;line-height:24px;color:#333;">
                I just launched a new feature: <strong style="color:#10b981;">{{ $feature }}</strong>
              </p>

              <p style="margin:0 0 24px;font-size:15px;line-height:22px;color:#555;">
                Would love to hear what you think—just reply to this email!
              </p>

              <p style="margin:0;font-size:14px;line-height:22px;color:#777;">
                Thanks for being part of the journey,<br>
                Gurpreet (Creator of Daily Expense Tracker)
              </p>
            </td>
          </tr>
          <tr>
            <td style="background:#f1f5fb;text-align:center;padding:16px 0;">
              <a href="https://dailyexpensetracker.in" style="color:#10b981;text-decoration:none;font-size:15px;">
                dailyexpensetracker.in
              </a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>