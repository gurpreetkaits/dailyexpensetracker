<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>We miss you!</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background:#f2f4f6;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
          <tr>
            <td style="padding:32px 40px 24px;text-align:left;">
              <h2 style="margin:0 0 16px;font-size:22px;color:#333;">Hey {{ $user->name ?? 'there' }} 👋</h2>

              <p style="margin:0 0 24px;font-size:16px;line-height:24px;color:#555;">
                It’s been a while since your last expense entry. I’d love to know what would make
                <strong>Daily&nbsp;Expense&nbsp;Tracker</strong> more valuable for you. Could you share a quick thought?
              </p>

              <p style="margin:0 0 32px;text-align:center;">
                <a href="https://app.dailyexpensetracker.in/settings"
                   style="display:inline-block;background:#2563eb;color:#ffffff !important;text-decoration:none;
                          padding:12px 28px;border-radius:4px;font-weight:bold;font-size:15px;">
                  Tell Me in 30 sec
                </a>
              </p>

              <p style="margin:0;font-size:14px;line-height:22px;color:#777;">
                Thanks a ton for helping shape the app!<br><br>— Gurpreet
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
