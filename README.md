
# The project

This is a content management system as you can find them dozen times. 

## Current status

This version is an early, if you like to call it that, alpha version. You can do that:

+ Create, edit, delete, but not move pages
+ Text and headline insert, edit, delete
+ Create, edit, delete user for backend
+ Create, edit, delete rights groups for backend users

Due to the fact that I still have to rebuild a lot, I do without a corresponding documentation of the internal processes. No one washes his car when he knows it starts raining two hours later.

I write it, although it should be clear, but better. This is an early version and a lot is missing. Do not use this version in live stage.

## Planned functions and properties (provisional)

+ User authentication across multiple databases. As an example, you have several projects, but users should be central in one place.
+ Implementation of the MVC concept but in a flat structure for easy maintenance and extensibility.
+ Implementation of fixed CORE modules and project related MANTLE modules.
+ Expandability of the CORE with the possibility to supply existing projects with the new CORE without breaking the function of the existing MANTLE modules.
+ Simple uncomplicated procedure to install a new module.
+ Vanilla Javascript ES6 and above
+ No support for the Internet Explorer

## Properties that will not happens

+ To lean too far out of the window
+ Call home
+ Automatic updates

# Requirements

+ A web server with PHP7 +, HTAccess support (Apache style) including URL rewrite and working sendmail configuration in PHP.
+ A MySQL compatible database server

# Install information

In the directory install there is an install.php which takes over everything. For successful installation, the database information is generally required, an eMail address to which system messages are sent as well as further details which are explained there.

A manual installation is not possible, you could access the public area, but for the backend (the administration) you need a user whose access data has to be hashed separately. To explain that is too complicated as that it could be useful to pack in a guide. And if I would create a helper script for it .. God damn for what did I create then the install.php  ...

## Todo

+ damn much
+ finding a name for the CMS
+ maybe do some sports

I can not give a timetable, but the next pre-release will come later this year.