<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Thanks for Your Feedback</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; padding: 20px; color: #333;">
  <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;justify-content:center;margin-bottom: 20px;">
        <img src="https://dailyexpensetracker.in/images/dailyexpensetracker.png" alt="Daily Expense Tracker" style="height:36px;border-radius:8px;">
        <span style="font-size:22px;font-weight:bold;color:#10b981;vertical-align:middle;">Daily Expense Tracker</span>
    </div>
    <h2 style="color: #111827;">Thank you for your feedback 🙌</h2>
    <p style="font-size: 16px; line-height: 1.6;">
      {{ $message }}
    </p>
    <p style="font-size: 16px; line-height: 1.6;">
      Meanwhile, feel free to continue using the app for free. We truly appreciate you being part of this journey!
    </p>
    <a href="{{ config('app.frontend_url') . '/login' }}" style="color: #10b981; text-decoration: none;">Login to your account</a>
    <p style="margin-top: 32px; font-size: 14px; color: #6b7280;">
      — Gurpreet, Creator of DailyExpenseTracker.in
    </p>
  </div>
</body>
</html>