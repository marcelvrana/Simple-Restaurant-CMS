Makawa Restaurant CMS
=================
 
Simple CMS for restaurant or your website

    www/dashboard - webpack, style, js for admin part
    app/FrontModule - For frontend part
    app/AdminModule - for administration part
    app/Model/Repository - connections for database
    database - complete database DUMP
    deployment - for deployment to FTP

Requirements
------------

- Nette 3.1 requires PHP 7.2


Installation
------------
    
    composer i
	cd www/dashboard
    npm i
    npm run prod


Make directories `temp/` and `log/` writable.


Web Server Setup
----------------

The simplest way to get started is to start the built-in PHP server in the root directory of your project:

	php -S localhost:8000 -t www

Then visit `http://localhost:8000` in your browser to see the welcome page.

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you
should be ready to go.

**It is CRITICAL that whole `app/`, `config/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).**
