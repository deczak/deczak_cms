
## The project

I create this content management system primarily for myself and my requirements, but make it available to the general public. The abbreviation cos stands for cooking own soup, based on the fact that someone cooks their own soup instead of using others. I have no objection to participating in the project. Notes on errors and the like are welcome.

### Current status

This version is an early, if you like to call it that, alpha version. You can do that:

+ Create, edit, delete, but not move pages
+ Text and headline insert, edit, delete
+ Create, edit, delete user for backend
+ Create, edit, delete rights groups for backend users

Due to the fact that I still have to rebuild a lot, I do without a corresponding documentation of the internal processes.

I write it, although it should be clear, but better: This is an early version and a lot is missing. Do not use this version in live stage.

### Planned functions and properties (provisional)

+ User authentication across multiple databases. As an example, you have several projects, but users should be central in one place.
+ Implementation of the MVC concept but in a flat structure for easy maintenance and extensibility.
+ Implementation of fixed CORE modules and project related MANTLE modules.
+ Expandability of the CORE with the possibility to supply existing projects with the new CORE without breaking the function of the existing MANTLE modules.
+ Simple uncomplicated procedure to install a new module.
+ Vanilla Javascript ES6 and above
+ No support for the Internet Explorer

### Properties that will not happens

+ Call home
+ Automatic updates
+ Image and video editing

## Requirements

+ A web server with PHP7 +, HTAccess support (Apache style) including URL rewrite and working sendmail configuration in PHP.
+ A MySQL compatible database server

## Install information

In the install directory there is an index.php that does everything. For successful installation, the database information is generally required, an email address to which system messages are sent and other information that is explained there.

A manual installation is not possible, you could do this to the extent that you can access the public area, but for the backend (administration) you need a user whose access data is hashed separately. To explain that is too complicated to be put in a manual. And if I would create a helper script for it.
