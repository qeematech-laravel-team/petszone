# 🛒 Multi-Vendor E-Commerce Platform

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

**A comprehensive, enterprise-grade multi-vendor e-commerce platform built with Laravel 12**

[Features](#-key-features) • [Installation](#-installation) • [Documentation](#-documentation) • [API](#-api-documentation) • [Contributing](#-contributing)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Technology Stack](#-technology-stack)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

This is a **production-ready multi-vendor e-commerce platform** that enables multiple vendors to manage their products, branches, and subscriptions through a unified marketplace system. The platform features separate dashboards for administrators, vendors, and customers, with comprehensive role-based access control and granular permissions.

### What Makes This Platform Special?

- ✅ **Enterprise-Grade Architecture**: Built with Laravel 12 following best practices and design patterns
- ✅ **Multi-Vendor Support**: Unlimited vendors with individual dashboards and inventory management
- ✅ **Multi-Branch Inventory**: Each vendor can manage multiple branches with separate stock tracking
- ✅ **Subscription-Based Access**: Flexible subscription plans with feature-based restrictions
- ✅ **Multi-Language Support**: Full English/Arabic support with RTL ready
- ✅ **RESTful API**: Complete API documentation for mobile app integration
- ✅ **Advanced Analytics**: Comprehensive reporting and analytics dashboards
- ✅ **Role-Based Permissions**: Granular permission system for vendor employees

---

## ✨ Key Features

### 🔐 Authentication & User Management

- **User Registration & Authentication**
  - Email/Phone-based registration
  - Email and phone verification system
  - Password reset with OTP codes
  - Profile management with image upload
  - Role-based access control (Admin, Vendor, User)

### 🏪 Vendor Management

- **Vendor Registration & Approval**
  - Vendor registration with admin approval workflow
  - Vendor profile management
  - Vendor status control (Active/Inactive)
  - Vendor featuring system
  - Commission rate management
  - Vendor balance tracking

- **Vendor Employees**
  - Create and manage vendor employees
  - Granular permission system
  - Role-based UI element visibility
  - Branch-specific access control

### 💳 Subscription & Plans System

- **Flexible Subscription Plans**
  - Duration-based plans (days)
  - Price configuration
  - Maximum products count per plan
  - Product featuring capability
  - Active/Featured status control
  - Subscription tracking and history
  - Days remaining calculation
  - Auto-expiration handling

### 📦 Product Management

- **Product Types**
  - **Simple Products**: Single SKU products with basic pricing
  - **Variable Products**: Products with multiple variants (Size, Color, etc.)

- **Product Features**
  - Multi-language support (English/Arabic)
  - Automatic SKU generation
  - SEO-friendly slug generation
  - Thumbnail and multiple image uploads
  - Pricing with discount (percentage/fixed)
  - Stock management per branch
  - Product status (Active/Inactive)
  - Featured products
  - New products flag
  - Admin approval workflow
  - Bookable products
  - Product categorization (many-to-many)
  - Related products (manual or auto-generated)

### 🏷️ Category & Variant Management

- **Hierarchical Category System**
  - Multi-level categories
  - Multi-language support
  - Category slug generation
  - Category request system (vendors can request new categories)
  - Admin approval/rejection workflow
  - Soft deletion

- **Product Variants**
  - Variant system (Size, Color, Material, etc.)
  - Variant options management
  - Variant request system
  - Admin approval workflow
  - Variant active/required toggle

### 🏢 Branch Management

- **Multi-Branch Support**
  - Multiple branches per vendor
  - Branch CRUD operations
  - Branch status control (Active/Inactive)
  - Branch-specific stock management
  - Branch product stock tracking
  - Branch variant stock tracking

### 📊 Inventory & Stock Management

- **Advanced Stock Tracking**
  - Stock tracking per branch
  - Simple product stock management
  - Variable product variant stock management
  - Stock availability checking
  - Stock quantity tracking
  - Low stock alerts

### 🛒 Shopping Cart & Orders

- **Shopping Cart**
  - Add/remove products
  - Update quantities
  - Apply coupon codes
  - Cart persistence
  - Stock validation

- **Order Management**
  - Order creation from cart
  - Shipping cost calculation
  - Multiple payment methods
  - Wallet and points integration
  - Order status workflow (Pending → Processing → Shipped → Delivered)
  - Order cancellation
  - Refund requests
  - Invoice generation (PDF)
  - Order reordering

### 💰 Payment & Transactions

- **Payment System**
  - Multiple payment methods
  - Wallet balance system
  - Points/rewards system
  - Coupon system
  - Commission calculation
  - Vendor earnings tracking
  - Withdrawal management

- **Transaction History**
  - Wallet transaction history
  - Points transaction history
  - Filtering and pagination

### 📈 Analytics & Reporting

- **Admin Analytics**
  - Revenue reports
  - Vendor performance metrics
  - Product performance analytics
  - Order analytics
  - Commission tracking

- **Vendor Analytics**
  - Sales reports
  - Earnings dashboard
  - Product performance
  - Customer insights
  - Commission breakdown

### 🎫 Support & Communication

- **Ticket System**
  - Create support tickets
  - Ticket status management
  - Message threading
  - File attachments
  - Ticket assignment to vendors

### ⭐ Ratings & Reviews

- **Product Ratings**
  - 1-5 star ratings
  - Review comments
  - Rating moderation
  - Average rating calculation

- **Vendor Ratings**
  - Vendor rating system
  - Review management
  - Rating visibility control

### 📱 RESTful API

- **Complete API Coverage**
  - Authentication endpoints
  - Product management
  - Cart operations
  - Order processing
  - User management
  - Address management
  - Ticket system
  - Ratings & reviews
  - Transactions
  - Full API documentation included

### 🌍 Internationalization

- **Multi-Language Support**
  - English/Arabic support
  - Locale switching
  - Translatable models
  - RTL support ready
  - Language-specific content

### 🔒 Security Features

- **Enterprise Security**
  - CSRF protection
  - XSS protection
  - SQL injection prevention (Eloquent ORM)
  - Role-based authorization
  - Permission-based access control
  - File upload validation
  - Input validation and sanitization
  - Password hashing
  - Email verification
  - Rate limiting
  - API token authentication (Sanctum)

---

## 🛠️ Technology Stack

### Backend

- **Framework**: Laravel 12
- **PHP Version**: 8.3.19
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Sanctum v4
- **Authorization**: Spatie Laravel Permission
- **Internationalization**: Spatie Laravel Translatable
- **Slug Generation**: Cviebrock Eloquent Sluggable
- **Excel Import/Export**: Maatwebsite Excel
- **PDF Generation**: Laravel PDF

### Frontend

- **CSS Framework**: Tailwind CSS v4
- **UI Components**: Bootstrap 5.3.8
- **Icons**: Bootstrap Icons 1.13.1
- **JavaScript**: Vanilla JavaScript (Alpine.js for some components)
- **Build Tool**: Vite 7.0.7
- **Styling**: Sass

### Development Tools

- **Code Formatter**: Laravel Pint v1
- **Testing**: PHPUnit v11
- **Development Server**: Laravel Sail v1
- **Debugging**: Laravel Pail v1.2.2

---

## 🏗️ Architecture

### Design Patterns

- **Repository Pattern**: Data access layer abstraction
- **Service Layer**: Business logic separation
- **Factory Pattern**: Product type handling (SimpleProduct, VariableProduct)
- **Form Request Validation**: Request validation classes

### Directory Structure

```
app/
├── Contracts/          # Interface definitions
├── Factories/          # Factory classes
├── Helpers/            # Helper functions
├── Http/
│   ├── Controllers/
│   │   ├── Admin/     # Admin-specific controllers
│   │   ├── Api/       # API controllers
│   │   ├── Auth/      # Authentication controllers
│   │   └── Vendor/    # Vendor-specific controllers
│   ├── Middleware/    # Custom middleware
│   ├── Requests/      # Form request validation
│   └── Resources/     # API resources
├── Models/             # Eloquent models
├── Repositories/      # Data access layer
└── Services/          # Business logic layer
    └── ProductTypes/  # Product type implementations
```

---

## 🚀 Installation

### Prerequisites

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- MySQL >= 8.0 or MariaDB >= 10.3
- Redis (optional, for caching and queues)

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-username/multi-vendor-e-commerce.git
cd multi-vendor-e-commerce
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file and configure:

```env
APP_NAME="Multi-Vendor E-Commerce"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multi_vendor_e_commerce
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### Step 5: Build Assets

```bash
# Build for production
npm run build

# Or run in development mode
npm run dev
```

### Step 6: Start Development Server

```bash
# Using Laravel's built-in server
php artisan serve

# Or using the dev script (includes queue worker and Vite)
composer run dev
```

The application will be available at `http://localhost:8000`

### Step 7: Create Admin User

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'is_verified' => true,
    'is_active' => true,
]);

$user->assignRole('admin');
```

---

## ⚙️ Configuration

### Queue Configuration

For production, configure queue workers:

```bash
# Supervisor configuration
php artisan queue:work --tries=3 --timeout=90
```

### Cron Jobs

Add to your crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Storage Link

```bash
php artisan storage:link
```

### Cache Configuration

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📖 Usage

### Admin Dashboard

Access the admin dashboard at: `http://localhost:8000/admin/dashboard`

**Admin Capabilities:**
- Manage vendors (approve, activate, deactivate)
- Manage products (approve, edit, delete)
- Manage categories and variants
- Manage subscription plans
- View analytics and reports
- Manage orders
- System settings

### Vendor Dashboard

Access the vendor dashboard at: `http://localhost:8000/vendor/dashboard`

**Vendor Capabilities:**
- Manage own products
- Manage branches
- Manage inventory
- View orders
- View analytics
- Manage subscription
- Manage employees

### API Access

The platform provides a complete RESTful API. See [API Documentation](#-api-documentation) for details.

---

## 📚 API Documentation

Complete API documentation is available in [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md).

### Quick API Overview

**Base URL**: `http://localhost:8000/api`

**Authentication**: Bearer Token (Laravel Sanctum)

**Key Endpoints:**
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `GET /api/products` - List products
- `GET /api/products/{id}` - Get product details
- `POST /api/cart/{product}` - Add to cart
- `POST /api/orders` - Create order
- And many more...

### Postman Collection

A complete Postman collection is included:
- `postman_collection.json` - API collection
- `postman_environment.json` - Environment variables

---

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=ProductTest

# Run with coverage
php artisan test --coverage
```

### Test Structure

```
tests/
├── Feature/          # Feature tests
├── Unit/            # Unit tests
└── TestCase.php     # Base test case
```

---

## 🔒 Security

### Security Features

- ✅ CSRF protection on all forms
- ✅ XSS protection
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ Password hashing (bcrypt)
- ✅ Rate limiting on sensitive endpoints
- ✅ File upload validation
- ✅ Input validation and sanitization
- ✅ Role-based access control
- ✅ Permission-based authorization
- ✅ API token authentication

### Security Best Practices

1. **Never commit `.env` file**
2. **Use strong passwords**
3. **Keep dependencies updated**
4. **Enable HTTPS in production**
5. **Regular security audits**
6. **Use environment-specific configurations**

---

## 📝 Code Quality

### Code Standards

- **Laravel Pint**: Automatic code formatting
- **PSR-12**: PHP coding standards
- **Type Hints**: Full type declarations
- **PHPDoc**: Comprehensive documentation
- **Repository Pattern**: Clean data access
- **Service Layer**: Business logic separation

### Running Code Formatter

```bash
# Format all files
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty
```

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Run tests** (`php artisan test`)
5. **Format code** (`vendor/bin/pint`)
6. **Commit your changes** (`git commit -m 'Add amazing feature'`)
7. **Push to the branch** (`git push origin feature/amazing-feature`)
8. **Open a Pull Request**

### Contribution Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Write clear commit messages
- Ensure all tests pass

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 📞 Support

For support, email support@example.com or open an issue in the repository.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Spatie](https://spatie.be) - Laravel Permission & Translatable packages
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Bootstrap](https://getbootstrap.com) - Frontend framework

---

## 📊 Project Status

**Version**: 1.1.0  
**Status**: Active Development  
**Last Updated**: January 2026

### Completed Features ✅

- ✅ User & Vendor Management
- ✅ Product Management (Simple & Variable)
- ✅ Multi-Branch Inventory
- ✅ Subscription System
- ✅ Order Management
- ✅ Payment Integration
- ✅ Analytics & Reporting
- ✅ RESTful API
- ✅ Multi-Language Support
- ✅ Role-Based Permissions

### Roadmap 🗺️

- [ ] Mobile App (React Native/Flutter)
- [ ] Advanced Analytics Dashboard
- [ ] AI-Powered Recommendations
- [ ] Multi-Currency Support
- [ ] Advanced Shipping Options
- [ ] Marketplace Commission System

---

<div align="center">

**Built with ❤️ using Laravel 12**

[⬆ Back to Top](#-multi-vendor-e-commerce-platform)

</div>
