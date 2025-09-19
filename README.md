# ROTAZ Filament Accounts
 Laravel authentication and authorization system designed for Filament, focusing on multi-tenant account management

# Introduction
ROTAZ Filament Accounts is a Laravel package that provides a robust authentication and authorization system tailored for Filament applications. It emphasizes multi-tenant account management, allowing users to belong to multiple accounts with distinct roles and permissions.

# Features
- Multi-tenant account management
- Role-based access control
- Seamless integration with Filament
- User-friendly interface for managing accounts and roles
- Secure authentication mechanisms
# Installation
To install the package, run the following command:
```
composer require rotazapp/filament-accounts
```

# Configuration
After installing the package, publish the configuration file using:
```
php artisan vendor:publish --tag=filament-accounts-config
```

You can then customize the configuration settings in config/filament-accounts.php.
# Usage
1. Migrate the database to create the necessary tables:     
2. php artisan migrate
3. Use the provided models and traits to set up your User model for multi-tenant support:
```php
use Rotaz\FilamentAccounts\Traits\HasAccounts;  
class User extends Authenticatable
{
    use HasAccounts;
}
```
4. Assign roles and permissions to users within different accounts using the provided methods.              
5. Manage accounts and roles through the Filament admin panel.
6. Refer to the documentation for detailed usage instructions and examples.
7. Documentation
For comprehensive documentation, including advanced usage and customization options, visit the [official documentation](https://github.io/rotazapp/filament-accounts).
9. Shell script helper
A shell script is provided to assist with common tasks. You can find it in the scripts directory of the package.
```bash 
  /bin/bash /home/devops/projects/laravel/cloud/filament-account/runner.sh
```