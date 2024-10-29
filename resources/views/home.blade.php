<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Daily Expense Tracker - Manage Your Money Effectively | dailyexpensetracker.in</title>
    <meta name="description" content="Take control of your finances with Daily Expense Tracker. Simple, intuitive expense tracking for better money management and financial insights.">
    <meta name="keywords" content="expense tracker, money management, budget tracking, personal finance, daily expenses">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="Daily Expense Tracker - Smart Money Management">
    <meta property="og:description" content="Simple and effective way to track your daily expenses and understand your spending habits.">
    <meta property="og:url" content="https://dailyexpensetracker.in">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Daily Expense Tracker">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <!-- Styles -->
    <style>
        /* Base styles */
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Navigation */
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid #eee;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
            text-decoration: none;
        }

        /* Hero Section */
        .hero {
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
            display: flex;
            align-items: center;
            gap: 4rem;
        }

        .hero-content {
            flex: 1;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        /* Features Section */
        .features {
            background-color: #f9fafb;
            padding: 4rem 2rem;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <a href="/" class="nav-brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 4H3a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM3 12h18"/>
            </svg>
            DailyExpense
        </a>
        <div>
            <a href="{{ config('app.frontend_url') }}/login" class="btn">Login</a>
            <a href="{{ config('app.frontend_url') }}/register" class="btn btn-primary">Get Started</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 style="font-size: 3rem; margin-bottom: 1.5rem; color: #333;">
                Track Your Money, <br>
                <span style="color: var(--primary);">Simplify Your Life</span>
            </h1>
            <p style="font-size: 1.25rem; color: #666; margin-bottom: 2rem;">
                Effortlessly track your expenses, understand your spending patterns, and make better financial decisions.
            </p>
            <a href="{{ config('app.frontend_url') }}/register" class="btn btn-primary">Start Tracking Free</a>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/dailyexpensetracker-mobile.png') }}" alt="Daily Expense Tracker Dashboard" width="200" height="200" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <div style="color: var(--primary); margin-bottom: 1rem;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20V10M18 20V4M6 20v-4"/>
                    </svg>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Expense Tracking</h3>
                <p style="color: #666;">Keep track of your daily expenses with an easy-to-use interface.</p>
            </div>
            
            <div class="feature-card">
                <div style="color: var(--primary); margin-bottom: 1rem;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Real-time Overview</h3>
                <p style="color: #666;">Get instant insights into your spending patterns and financial health.</p>
            </div>
            
            <div class="feature-card">
                <div style="color: var(--primary); margin-bottom: 1rem;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h3 style="margin-bottom: 0.5rem;">Category Management</h3>
                <p style="color: #666;">Organize expenses into categories for better tracking and analysis.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="text-align: center; padding: 4rem 2rem; background: #f9fafb;">
        <p style="color: #666;">© 2024 Daily Expense Tracker. All rights reserved.</p>
    </footer>

    <!-- Structured Data for SEO -->
    <script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "WebApplication",
        "name": "Daily Expense Tracker",
        "url": "https://dailyexpensetracker.in",
        "description": "A simple and effective expense tracking tool to manage your daily finances.",
        "applicationCategory": "FinanceApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "INR"
        }
    }
    </script>
</body>
</html>s