CashMate: A Web-Based Expense Tracker Management System
      CashMate is a web-based expense tracker management system designed to help users record, monitor, and analyze their financial activities efficiently. The system is ideal for students, working individuals, small business owners, and households, allowing them to understand their spending habits, manage budgets, and make better financial decisions, all in one secure and user-friendly platform.

Features implemented:
- User Authentication
- Transactions Management (Add, edit, and delete transactions)
- Categories Management (Create, edit, and delete expense or income categories.)
- Budgets Management (Add, edit, and delete budget limits)
- Reports and Analytics (Pie chart report for expense distributions, bar chart report for comparing monthly income and expenses, and line chart report for tracking actual spending versus budget limits 
- Search Feature
- Notifications and Alerts

Setup Instructions:
Step 1: Install and Configure Your Local Server
- Download and install XAMPP or Laragon (recommended)

Step 2: Place the System Files
- Locate your web server root folder: For XAMPP - C:\xampp\htdocs\ and For Laragon - C:\laragon\www\
- Copy the entire CashMate_Expense_Tracker system folder into the directory.

Step 3: Create the Database
- Open your browser and go to phpMyAdmin
- Click New and create a database named: cashmate_db
- Import the provided SQL file (cashmate_db.sql) into the new database.

Step 4: Configure the Database Connection
- Open the CashMate_Expense_Tracker folder → go to connection/dbconn.php

Step 5: Run the System
- In your browser, go to: http://localhost/CashMate_Expense_Tracker/index.html
- You should see a landing page where you can login or register there.

Database Structure:
Users Table
- user_id
- first_name
- middle_name
- last_name
- email
- password
- created_at

Categories Table 
- category_id
- category_name
- user_id

Transactions Table
- transaction_id
- type
- amount
- date
- description
- user_id
- category_id

Budgets Table
- budget_id
- amount_limit
- start_date
- end_date
- user_id
- category_id
