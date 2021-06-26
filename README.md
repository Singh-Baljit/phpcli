# PHP Cli

## Table of contents
* [Intro](#intro)
* [Technologies](#technologies)
* [Run](#run)
* [Help](#help)
* [Bonus : Foobar](#foobar)

## Intro
* PHP script
* Executed from the command line only
* Accepts a CSV file as input and processes the CSV file
* Parsed file data and inserted into a MySQL database
	
## Technologies
This script is created with:
* PHP 7.4
* MariaDB 10.4
	
## Run
To run this script, run cli with command:

```
$ php user_upload.php -hlocalhost -uroot -p --create_table --file="users.csv"
```

## Help
The PHP script should include these command line options (directives):

```
--file          [csv file name] – this is the name of the CSV to be parsed
--create_table  this will cause the MySQL users table to be built (and no further action will be taken)
--dry_run       this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
-u              MySQL username
-p              MySQL password
-h              MySQL host
--help          which will output the above list of directives with details
```

## Foobar
* This script is a logic test.
* Executed from the command line only without any cli options
```
$ php foobar.php
```
