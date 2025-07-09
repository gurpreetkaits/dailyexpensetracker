<!--
title: Import Your Bank Statements into DailyExpenseTracker — Here's How
description: A step-by-step guide to effortlessly import bank statements (CSV, XLSX) into DailyExpenseTracker, choose wallets, auto-create categories, and avoid duplicate transactions.
date: 2025-07-09
-->

# Import Your Bank Statements into DailyExpenseTracker — Here's How

Managing personal finances across multiple bank accounts can be a headache—especially when you're juggling CSV exports, multiple currencies, and countless coffee purchases. **DailyExpenseTracker** now makes life simpler with a brand-new **Bank-Statement Import** workflow. In minutes you can upload a statement, map its columns, and watch your transactions appear neatly in your preferred wallet.

> **TL;DR** – Upload your statement → pick a wallet → map the columns → save the mapping for next time. Our smart importer creates missing categories, skips empty rows, and blocks duplicates automatically.

## Why Importing Matters for Everyone

1. **Works with Any Bank**: Whether it's Revolut, N26, Monzo, Chase, or your local savings bank, you can export a CSV/XLSX file and drop it straight in.
2. **Multi-Currency Friendly**: Track EUR, USD, GBP, CHF—DailyExpenseTracker converts and groups them by wallet.
3. **Data Privacy First**: All data stays encrypted on secure servers and is fully GDPR-compliant.

## Supported File Formats

| Format | Typical Bank Download Button | Notes |
|--------|------------------------------|-------|
| `.csv` | "Export CSV", "Download Spreadsheet" | Most common and recommended |
| `.xlsx` | "Excel" | Larger files supported |

> **Tip:** Always choose **comma-separated** CSV for the smoothest import.

## Preparing Your Statement

1. Log in to your online banking portal.
2. Export the **last month** (or any date range) as **CSV**.
3. Make sure columns like **Date**, **Description**, and **Amount** are visible.

No extra editing needed—DailyExpenseTracker handles the rest.

## Step-by-Step: Importing a Statement

### 1. Open the Import Modal

In the **Desktop Dashboard**, hit the **`↓` Import** button (bottom toolbar). A modal pops up.


### 2. Upload Your File

Drag & drop or click **Browse** to choose your CSV/XLSX file.

### 3. Choose Your Wallet After Upload

Select the wallet you'd like the transactions to land in—Savings, Credit Card, or your shiny new EUR Wallet. If you skip this step, the importer picks your first wallet automatically.


### 4. Map the Columns

Our smart mapper detects common headers (Date, Amount, Description). If something looks off, use the dropdowns to link your bank's column names to **DailyExpenseTracker** fields.

### 5. Save the Mapping (Optional)

Tick "Remember this mapping" once. Next time, the file from the same bank loads with everything pre-mapped and the button shows **Active ✅**.

### 6. Review & Import

Hit **Import**. When done, the transactions appear instantly with the wallet balance updated in brackets.

## Smart Features Under the Hood

- **Automatic Category Creation**: If your statement contains a new merchant (e.g., *IKEA*), we create a matching category just for you.
- **Duplicate Detection**: Same **user_id**, **amount**, **date**, and **note**? We skip it—no more double entries.
- **Empty Row Skipping**: Lines missing amount or date are ignored, keeping your data clean.
- **Soft-Delete & Restore**: Deleted a transaction by mistake? Head to **Trash** under Transactions, click **Restore**, and it reappears in seconds.

## Frequently Asked Questions

### Does it support Open Banking APIs?
For now, we focus on file uploads. Open Banking integration is on our roadmap—stay tuned!

### What about VAT splitting?
If your business expenses include VAT, simply create a separate **VAT** category. Future updates will automate this.

### Why is my CSV not recognised?
Make sure the file is encoded in UTF-8 and uses commas `,` as separators. You can quickly convert in Excel: *Save As → CSV (UTF-8)*.

## Pro Tips for Power Users

1. **Batch Imports**: Import multiple files back-to-back; each keeps its own mapping.
2. **Keyboard Shortcuts**: While the modal is focused, hit **Enter** to trigger Import, and **Esc** to close.
3. **Filter After Import**: Use the new **Trashed** filter to view soft-deleted items for quick recovery.

## Start Importing Today

Ready to cut manual entry time by 90 %? Log in to [DailyExpenseTracker](https://app.dailyexpensetracker.in) and give the import feature a spin.

1. Dashboard → Import
2. Pick wallet & file
3. Map → Save → Import

> **Your data, your rules**—and now, your statements imported in seconds.

*Happy budgeting!* 