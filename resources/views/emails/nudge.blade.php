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
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;justify-content:center;margin-bottom: 20px;">
                <img src="https://dailyexpensetracker.in/images/dailyexpensetracker.png" alt="Daily Expense Tracker" style="height:36px;border-radius:8px;">
                <span style="font-size:22px;font-weight:bold;color:#10b981;vertical-align:middle;">Daily Expense Tracker</span>
              </div>
              <h2 style="margin-bottom:16px;font-size:22px;color:#222;margin-top: 15px;">Hey {{ $user->name ?? 'there' }} 👋</h2>

              <p style="margin:0 0 24px;font-size:16px;line-height:24px;color:#333;">
                I need your feedback, so that i can know what mistakes I'm doing or what you love using.
                <b>I want to build this app the way so that you love using it.</b>
              </p>

              <p style="margin:0 0 32px;text-align:center;">
                <a href="https://app.dailyexpensetracker.in/settings"
                   style="display:inline-block;background:#10b981;color:#ffffff !important;text-decoration:none;
                          padding:12px 28px;border-radius:4px;font-weight:bold;font-size:15px;">
                  Help me to Improve the app (Click for feedback)
                </a>
              </p>

              <p style="margin:0;font-size:14px;line-height:22px;color:#777;">
                If you can't send feedback over app, just reply to the mail (gurpreetkait.codes@gmail.com). Thanks in advance! <br><br>— Gurpreet (Creator of DailyExpenseTracker)
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
