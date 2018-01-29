# Attendance
## Lumen Powered

### Installation

This package uses composer. You will need to first install Composer and run the `install` command:

`php composer.phar install`

#### Server Configuration

Point the public directory to the `attendance/public` folder.  

#### Migrations

`php artisan migrate`

Note: If you do not have artisan installed, you can copy it from `vendor/Laravel/lumen` to `attendance`.

#### Authentication

By default this application requires no authentication. HTTP basic authentication should be fine for basic usage. 

##### Linux

`htpasswd -c /path/to/attendance/.htpasswd admin`

##### Apache2

Add the following to `.htaccess`: 

    AuthType Basic
    AuthName "Attendance Login"
    AuthUserFile "/path/to/attendance/.htpasswd"
    Require valid-user
    
    Order allow,deny
    Allow from all

##### Nginx

    location /path/to/attendance/public {                                       
        auth_basic           "Attendance Login";
        auth_basic_user_file /path/to/attendance/.htpasswd
    }