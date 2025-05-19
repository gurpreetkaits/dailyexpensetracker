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
              <div style="display:flex;align-items:center;gap:10px;justify-content:center;margin-bottom: 20px;">
                <img src="https://dailyexpensetracker.in/images/dailyexpensetracker.png" alt="Daily Expense Tracker" style="height:36px;border-radius:8px;">
                <span style="font-size:22px;font-weight:bold;color:#10b981;vertical-align:middle;">Daily Expense Tracker</span>
              </div>

              <h2 style="margin-bottom:16px;font-size:22px;color:#222;margin-top: 15px;">Hey {{ $user->name ?? 'there' }} 👋</h2>

              <p style="margin:0 0 24px;font-size:16px;line-height:24px;color:#333;">
                First off, thank you for using Daily Expense Tracker. Whether you've been logging every chai or just exploring the app — it means a lot. 💚
              </p>

              <p style="margin:0 0 24px;font-size:16px;line-height:24px;color:#333;">
                I’ve recently moved the app to a <strong>Premium model</strong>. It was a tough call, but it helps me keep building features for the people who genuinely care about managing money better (like you).
              </p>

              <p style="margin:0 0 24px;font-size:16px;line-height:24px;color:#333;">
                For less than the cost of a pizza 🍕 each month, you now get:
                <ul style="padding-left:20px;margin:16px 0;">
                  <li>Unlimited Categories & Wallets</li>
                  <li>AI-powered spending insights</li>
                  <li>Weekly & Monthly reports in your inbox</li>
                  <li>Reminders + Calendar sync (coming soon!)</li>
                  <li>Bank Account Sync (coming soon!)</li>
                </ul>
              </p>

              <p style="margin:0 0 32px;font-size:16px;line-height:24px;color:#333;">
                If you've found the app useful even once, I'd love for you to give Premium a try. I'm building this with care, and your support keeps it going — feature by feature.
              </p>

              <p style="margin:0 0 32px;text-align:center;">
                <a href="https://app.dailyexpensetracker.in/overview"
                   style="display:inline-block;background:#10b981;color:#ffffff !important;text-decoration:none;
                          padding:12px 28px;border-radius:4px;font-weight:bold;font-size:15px;">
                  Try Premium Today →
                </a>
              </p>

              <p style="margin:0;font-size:14px;line-height:22px;color:#777;">
                And if you ever want to share feedback, ideas, or just chat — hit reply, I actually read and respond. 😊<br><br>
                Thanks again for being on this journey. <br><br>— Gurpreet (just a solo dev trying to build something useful)
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