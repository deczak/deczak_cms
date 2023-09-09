I stopped the development of this project and started a new one that is  not public at this time.
It would cost me to much time change the code to this where i want to be.

## The project

I create this content management system primarily for myself and my requirements, but make it available to the general public. I don't mind if you want to take part in the project. Notes on errors and the like are welcome. Some parts need to be rewritten, so there is no technical documentation about it.

There are two branches:

+ master-branch, the version that was tested so far and should tend to work (errors are of course not excluded)
+ dev-branch, the version that contains the newest features but it could contains bugs

### Current status

The CMS is still in an early stage of development. There may be errors that can lead to inoperability or data loss in the data entered in the CMS. There is no CMS own backup or restore function of the data. There is also no update function at the moment, so new versions may require a new installation. Therefore, please do not use the CMS **productively**, an update function will follow in the course of the year.

**Functions that exists**
+ Create, edit, delete, but not move pages
+ Text, Headline and source module
+ Create and manage users for backend and frontend (the areas have their own user tables)
+ Add languages for frontend
+ Restrict site access for Authed users
+ Various visibility settings of the pages
+ Automated blocking of access under certain conditions
+ Block access based on IP/CIDR notation
+ Block access using the user agent

### Properties of this CMS

+ User authentication across multiple databases. As an example: You have several projects, but the users should be in one place.
+ Modules as fixed core and project-related shell modules, shell modules can (in concept) overload core modules.
+ Vanilla Javascript in the backend from ES6
+ No support for Internet Explorer

## Requirements

+ A web server with PHP8+, HTAccess support (Apache style) including URL rewrite and working sendmail configuration in PHP.
+ A MySQL compatible database server

## Install information

In the install directory there is an index.php that does everything, keep the necessary data such as database information ready. A manual installation is not possible, a new installation is not possible until you have deleted the database tables and the htaccess file.

If the CMS is used productively, the cronjobs must be created as these on the server. There are 3 files in the / cron directory:
+ cron_24_hours.php, this file every 24 hours
+ cron_7_days.php, this file once a week
+ cron_6_hours.php, this file every 6 hours

During the installation, it is checked whether the CMS was installed using SSL transport encryption. If this state changes, the value under $COOKIE_HTTPS must be changed in the configuration under /config/standard.php. Otherwise, a login in the backend could fail.

The module Blog, as mantle module, needs to be installed in the module managment. There is currently an error that the response is wrong. After hitting the Button install, wait 2 seconds and hit reload. The module should be now listed as installed. Now you must set the user rights for Adminstrators to use this module in the page edit.

###	Third party content

The content management system contains the following third-party sources:

+ Font Awesome
+ Flatpickr
