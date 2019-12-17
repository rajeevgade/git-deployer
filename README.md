# Git Auto Deployer (Github-only)

Push changes to your linux server automatically (after setting up webhook for your repository)

## Helpful Links

- [How to setup Github SSH Keys](https://help.github.com/en/github/authenticating-to-github/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)
- [Github Webhooks](https://developer.github.com/webhooks)

## Requirements

- PHP 7.2+ 
- MySQL
- Composer

## Installation

```bash
//get repo
$ git clone https://github.com/rajeevgade/git-deployer.git

//enter the directory
$ cd git-deployer

//install dependencies
$ composer install

//create and configure your config file
$ cp .env.example .env
$ vi .env
—- change your db credentials

//create an encryption key unique for your app
$ php artisan key:generate

//run database migration to create required schemas along with default seed values
$ php artisan migrate --seed

//clear and cache config files - need to run this every time you make any changes to config file(s)
$ php artisan config:cache 

//install node dependencies
$ npm install
```

## Configuration

- Copy/Edit `.env.example` file and rename it to `.env` (skip if you already did it!)
- Update Database Credentials in .env file (skip if you already did it!)

## Demo Login

- Email: rajeevgade@gmail.com
- Password: adminuser

## Running (Skip this step if you have Laravel Valet or if you're running this app on Server)

```bash
$ php artisan serve

//to run on different port (for example 80)
$ php artisan serve --port=80
```

## Required Permissions

### Localhost

If you are facing any issues with the permissions on your local machine, try running the following command in your project directory:

```bash
$ sudo chmod -R 775 bootstrap/cache

$ sudo chmod -R 775 storage
```

### Server

If you are facing any issues with the permissions on your server, try running the following command in your project directory:

```bash
$ sudo chown -R $USER:www-data storage

$ sudo chown -R $USER:www-data bootstrap/cache
```

## Maintenance Mode

### Enable Maintenance Mode

```bash
$ php artisan down
```
### Disable Maintenance Mode

```bash
$ php artisan up
```
