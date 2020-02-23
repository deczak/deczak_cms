
## Version

The version number is the release date. A letter as suffix indicates fixes on same day.

- master-branch contains the version that is tested so far
- dev-branch is the current progress, sometimes it could be broken

(the versions handling may get changed later)

## Version history	  


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

