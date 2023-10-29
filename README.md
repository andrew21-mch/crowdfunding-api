# Crowdfunding API

The Crowdfunding API is a web application built with Laravel that provides a platform for crowdfunding projects. It allows users to create and manage crowdfunding campaigns, make donations, and track the progress of campaigns.

## Table of Contents

- [Crowdfunding API](#crowdfunding-api)
  - [Table of Contents](#table-of-contents)
  - [Features](#features)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Configuration](#configuration)
  - [Usage](#usage)
  - [API Documentation](#api-documentation)
  - [Testing](#testing)
  - [Contributing](#contributing)
  - [License](#license)
  - [Contact](#contact)

## Features

- User registration and authentication
- Campaign creation, editing, and deletion
- Donation management
- Campaign search and filtering
- User profile management
- Email notifications for campaign updates
- Admin panel for managing campaigns and users

## Prerequisites

- PHP 7.4 or higher
- Laravel 8.x
- MySQL or any other supported database
- Composer (for dependency management)

## Installation

Open your terminal or command prompt.

Change the current directory to the location where you want to clone the repository.

Run the following command to clone the repository:
```
git clone https://github.com/andrew21-mch/crowdfunding-api.git
cd crowdfunding-api
```

3. Install the dependencies:
```
composer install
```
4. Copy the `.env.example` file to `.env`:
```
cp .env.example .env
```
5. Generate an application key:
```
php artisan key:generate
```
6. Configure the database connection in the `.env` file:
```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_username
    DB_PASSWORD=your_database_password
```
7.  Run the database migrations and seed the database:
```
php artisan migrate --seed
```
8.  Start the development server:
```
php artisan serve
```
9. Open your browser and navigate to `http://localhost:8000` to access the API.

## Configuration

- Update the `.env` file with any additional configuration settings, such as mail configuration, cache management, or third-party API keys.

## Usage

-

## API Documentation

[Documentation][https://documenter.getpostman.com/view/17184783/2s9YRGxoij#691f1709-15a4-4c4a-9d8d-1e6058fb6867]

## Testing

-Please ensure that the necessary environment variables are set up for testing in the `.env.testing` file.


after , run

```
php artisan test --env=testing
```

## Contributing

Contributions are welcome! If you encounter any issues or have suggestions for enhancements, please open an issue or submit a pull request. Please refer to the [Contribution Guidelines](/CONTRIBUTING.md) for more information.

## License

This project is licensed under the [MIT License](/LICENSE).

## Contact

For any questions or inquiries, please contact Nfon Andrew at andy@skye8.tech