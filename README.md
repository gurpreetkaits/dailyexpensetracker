# Daily Expense Tracker

A simple, open-source expense tracking app. Use it with any AI — or just on its own.

## Features

- Track expenses and income
- Multiple wallets support
- Categories and tags
- Stats and insights
- Google OAuth login
- OTP-based passwordless login
- Works great with AI assistants via API

## Works with OpenClaw

Connect through WhatsApp and log expenses by just messaging:

```
> "500 groceries"
> done.
```

[Set up OpenClaw](https://x.com/gurpreetkait/status/2022571236568821895)

## Quick Start

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/MariaDB

### Installation

```bash
# Clone
git clone https://github.com/gurpreetkaits/dailyexpensetracker.git
cd dailyexpensetracker

# Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Frontend
cd frontend
npm install
cp .env.example .env
npm run build
```

### Environment Variables

Backend `.env`:
```
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_DATABASE=dailyexpensetracker
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# Email (optional)
MAIL_MAILER=resend
RESEND_KEY=
```

Frontend `.env`:
```
VITE_BACKEND_URL=http://127.0.0.1:8000
VITE_APP_URL=http://localhost:5173
VITE_GOOGLE_CLIENT=
```

### Run

```bash
# Backend
php artisan serve

# Frontend (separate terminal)
cd frontend
npm run dev
```

Visit `http://localhost:5173`

## API

Authentication via Laravel Sanctum. All endpoints require `Authorization: Bearer <token>` header.

### Transactions

```
GET    /api/transactions          # List transactions
POST   /api/transactions          # Create transaction
GET    /api/transactions/{id}     # Get transaction
PUT    /api/transactions/{id}     # Update transaction
DELETE /api/transactions/{id}     # Delete transaction
```

### Wallets

```
GET    /api/wallets               # List wallets
POST   /api/wallets               # Create wallet
```

### Categories

```
GET    /api/categories            # List categories
POST   /api/categories            # Create category
```

## Tech Stack

- **Backend:** Laravel 11, PHP 8.2
- **Frontend:** Vue 3, Vite, Tailwind CSS
- **Database:** MySQL/MariaDB
- **Auth:** Laravel Sanctum, Google OAuth

## Contributing

PRs welcome. For major changes, open an issue first.

## License

MIT

## Author

Built by [@gurpreetkait](https://x.com/gurpreetkait)
