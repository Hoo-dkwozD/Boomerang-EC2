# Boomerang Electronics E-Commerce Website

## Getting Started
To run this project locally, you will need to have WampServer installed on your machine. Follow these steps to get started:

1. Start WampServer and ensure that it is running.
2. Create a database titled "BoomerangElectronics" and import data into it using the included .sql file. You can do this by opening phpMyAdmin, selecting the "Databases" tab, typing in the Database name and clicking "Create". Then go to the "Import" tab, choose the .sql file, and click "Go."
3. You will also need to install the Stripe and PHPMailer libraries for the payment and notification codes to work. After installing [composer](https://getcomposer.org/download/), run these two commands in the e-commerce directory:
   * `composer require stripe/stripe-php`
   * `composer require phpmailer/phpmailer`
4. A `vendor` folder containing the libraries should have been created.
5. Now, you can navigate to localhost/e-commerce/login.php in your web browser to access the login page.

## Usage
- Use the login page to log in as either an admin or a regular user.
- Once logged in, you can explore the different features of the e-commerce website.

## Troubleshooting
- If you encounter any issues, ensure that WampServer is running correctly and that the database has been imported successfully.
