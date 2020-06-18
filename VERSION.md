
## Version

The version number is the release date. A letter as suffix indicates fixes on same day.

- master-branch contains the version that is tested so far
- dev-branch is the current progress, sometimes it could be broken

(the versions handling may get changed later)


## Version history	 

### 20200617

**Changes**
- added edit lock for modules module
- fixed cronjobs
- added handling for denied remote if htaccess is not supported


### 20200531

**Changes**
- updated handling of edit lock for data if multiple users are working with them
- added edit lock for tags module
- added edit lock for categories module
- added edit lock for language module
- added edit lock for users module
- added edit lock for backend users module
- added option in configuration.json to disable denied remote system


### 20200507

**Changes**
- Switched from myslqi to pdo, still only mysql as supported DB. Old mysqli connection class removed.
- Created a wrapper for all database calls, some modules/classes are still on todo for this wrapper.
- Refactored model system for the new wrapper.

**Notes**
- Maybe some parts still broken


### 20200414

**Modules**
- Added frontend sitemap module for child pages, early version, templates are stored in data/modules

**Changes**
- Added test of edit lock to modules user-agent and denied-remote
- Moved some settings from static config to dynamic config, some of them are no in environment module
- Added prototype (first step) of multiple content sections per page page


### 20200326

**Changes**
- Removed print_r in user-agent module that leads in non valid xhr result
- Various changes on module handling for module overloading
- Various changes on Login-Objects and handling


### 20200225

**Changes**
- Changed backend module for login-objects to add additional databases to the backend login-object
- Added cron job file for remote users
- Added settings to environment module to set remote users configuration


### 20200223

**Changes**
- Fixed reading of nested set structure thats returned in wrong order and resulted in wrong urls


### 20200219

**Modules**
- Added backend module for remote users to assign them rights

**Changes**
- Changed various table columns types

**Notes**
- Remote users for backend requires to edit the login-object, but this is atm not possible. After I got some additional stuff done with this, I will create a documentation for it.


### 20200219

**Changes**
- bugfix on adding module on page edit
- New sub page set with locked for all as default value
- Various changes and bug fixes in modules after audit

### 20200216

**Modules**
- Added frontend/mantle Blog Post Headline Modul, creates a headline similar to blog-module
- Various changes and bug fixes in modules

**Changes**
- Rework of user-rights system, the part for remote users is still on todo


### 20200209

**Changes**
- Added page edit toolbar option for internal redirect, loads content from other node-id and sets canonical to this
- Changes page content that comes with the fresh install, also added missing german root page
- Various bug fixes (page edit toolbar)

**Notes**
- Removed previous project name as is was already in use by an other project
- Installing Blog Module requires the rights, see right groups after install


### 20200207

**Modules**
- Added backend module for frontent user management
- Added backend module for manage page categories
- Added backend module for manage page tags
- Added optional frontend module with blog functionality

**Changes**
- Added option in page edit toolbar for login object restriction
- Added animation in save buttons
- Refactored some model* files, 3 sections still not update to date
- Various bugs fixed, maybe added new bugs


### 20200126

**Changes**
- Add two new fields in page edit toolbar for publish from / until
- Add constraints support to Shemes handling


### 20200120

**Modules**
- Added backend module for manually update of htaccess and sitemap.xml if needed


### 20200118

**Notes**
- Early version
- Deleting a languages does not deletes added objects

**Modules**
- Added backend module for frontend languages, removed previous settings from config file

**Changes**
- Switched error message box styles
- Added error message if module install fails
- Added missing encryption base key replacement on install
- Added backend module for frontend languages
- Fixed wrong column name when calling moduleSitemap, which led to wrong URLs in sitemap.xml and .htaccess
		

### 20200112
  
**Notes**
- Early version
- Mantle module mod_blogBackend unfinished (maybe will be dropped)


### 20190922

**Notes**
- Early version

