# Expense Tracker Application

## Overview
A comprehensive expense tracking system with individual and family financial management capabilities. This web application allows users to track income, expenses, and budgets, with special features for family group collaboration.

## Features

### Core Functionality
- **User Authentication**: Secure login and registration system
- **Transaction Management**: Track income and expenses with categories
- **Budget Planning**: Set and monitor budgets with date ranges
- **Financial Summary**: View income, expense, and net balance totals

### Family Features
- **Family Groups**: Create or join family groups for shared financial tracking
- **Member Dashboard**: View family members' financial summaries
- **Shared Insights**: Track total family income and expenses

## Technologies Used

### Backend
- PHP 7.4+
- MySQL Database
- Server-side validation
- Password hashing (bcrypt)

### Frontend
- HTML5, CSS3
- Glassmorphic UI design
- Responsive layout
- Client-side form validation

### Security
- Prepared statements to prevent SQL injection
- Session-based authentication
- Password hashing
- Input sanitization

## Installation

### Requirements
- Web server (Apache/Nginx)
- PHP 7.4+
- MySQL 5.7+

### Setup Instructions
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/expense-tracker.git
   ```

2. Import the database:
   ```bash
   mysql -u username -p expense < database/expense_tracker.sql
   ```

3. Configure database connection:
   Edit the following files to update database credentials:
   - `includes/config.php`
   - All PHP files with database connections

4. Set up web server:
   - Point your web server to the project directory
   - Ensure mod_rewrite is enabled for clean URLs

## File Structure

```
expense-tracker/
├── assets/               # Static files
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── includes/             # PHP includes
│   ├── config.php        # Configuration file
│   └── functions.php     # Common functions
├── database/             # Database files
│   └── expense_tracker.sql  # Database schema
├── index.php             # Home page
├── login.php             # Login page
├── register.php          # Registration page
├── dashboard.php         # Individual dashboard
├── family_dashboard.php  # Family dashboard
└── logout.php            # Logout script
```

## Usage

1. **Registration**
   - Register as an individual user or create/join a family group
   - Set up your profile information

2. **Dashboard**
   - Add income and expense transactions
   - Create and monitor budgets
   - View financial summaries

3. **Family Features**
   - Switch between individual and family views
   - View family members' financial summaries
   - Track total family finances

## Security Considerations

- Always use HTTPS in production
- Regularly update PHP and MySQL
- Implement additional security measures like:
  - Rate limiting
  - CSRF protection
  - Input validation

## Screenshots

![Login Screen](screenshots/login.png)
*Modern glassmorphic login interface*

![Dashboard](screenshots/dashboard.png)
*Interactive financial dashboard*

![Family View](screenshots/family.png)
*Family financial overview*

## Contributing

Contributions are welcome! Please follow these steps:
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Future Enhancements

- [ ] Expense categorization and reports
- [ ] Data export (CSV/PDF)
- [ ] Mobile application
- [ ] Recurring transactions
- [ ] Financial goal tracking
