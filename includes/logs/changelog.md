----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.4.3[a] (2019-04-22)

* Added 'button' class to form submit buttons
* Added more documentation
* Added placeholder to input tag in formTag function
* Fixed a minor bug in the formRow function
* Continued work on 'Create Post' form

**Modified files:**
* admin/includes/class-post.php
* admin/includes/class-settings.php (M)
* admin/includes/class-user.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.2[a] (2019-04-10)

* Added more styling to the admin navigation
* Cleaned up adminNavItem function
* Added 'current' functionality to admin nav items (doesn't work for subnav items)

**Modified files:**
* admin/includes/functions.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.1[a] (2019-04-09)

* Minor tweak to the changelog's formatting
* Created a function to contstruct the 'Create Post' form (form is empty)
* Cleaned up User::createEntry function
* Added and styled the admin header
* Styled the admin navigation
* Put admin page heading inside a wrapper
* Added more documentation

**Modified files:**
* admin/header.php
* admin/index.php (M)
* admin/includes/class-post.php
* admin/includes/class-settings.php (M)
* admin/includes/class-user.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.0[a] (2019-04-05)

* Created admin posts page and posts class
* Replaced intval with int type cast on users page
* Updated and added documentation to the user class
* Included buttons stylesheet in admin dashboard
* Moved getStylesheet and getScript functions to globals.php
* Added links to post page on admin navigation
* User::getPageList now only retrieves published pages from the database
* Created a function to retrieve post count based on post status (Post class)
* Created a function that constructs a table row
* Updated and added more documentation
* Created a function that constructs a list of all posts in the database by post type
* Change.log has been renamed to changelog.md (it will henceforth be ommitted from list of modified files)
* Converted the change log to markdown format
* Created a function to retrieve a post's author
* Cleaned up User::listEntries function

**Modified files:**
* admin/header.php
* admin/posts.php (N)
* admin/users.php
* admin/includes/class-post.php (N)
* admin/includes/class-settings.php (M)
* admin/includes/class-user.php
* admin/includes/functions.php
* includes/functions.php
* includes/globals.php
* includes/logs/changelog.md

----------------------------------------------------------------------------------------------------
## Version 1.3.8[a] (2019-03-29)

* Robots.txt is now created on install (added it to .gitignore)
* Replaced "\n" with chr(10) in the logError function
* Added error_log to .gitignore (home and admin directories)
* Robots.txt is updated if the 'do_robots' setting is changed
* The intval function will now be removed in favor of int type casting
* Added and updated more documentation
* Removed LICENSE.md from .gitignore

**Modified files:**
* .gitignore
* admin/install.php
* admin/setup.php
* admin/includes/class-settings.php
* includes/debug.php (M)
* includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.3.7[a] (2019-03-28)

* Added and updated documentation in various places
* Cleaned up some admin files
* Created Settings admin class
* Version numbers will now be denoted as "Version <version><[a/b]>" or "@since <version><[a/b]>"
* Added 'maxlength' attribute to input tag in formTag function
* Added '*' attribute to input tag in formTag function (this is for miscellaneous attributes like 'readonly' or 'checked')
* Allowed 0 to be passed as a legitimate value to an input
* Fixed a bug in the formRow function that prevented adding a label to a single input argument
* Initialization now terminates if database is not installed (prevents an error from generating in error_log)
* A sample page will now be created on installation (it is set as the default home page)
* Cleaned up the change log a bit (mostly rewording and adding a few things that had been ommitted)

**Modified files:**
* index.php
* init.php (M)
* admin/index.php
* admin/install.php
* admin/settings.php
* admin/users.php
* admin/includes/class-settings.php (N)
* admin/includes/class-user.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.3.6[a] (2019-03-27)

* Added a line of documentation to init.php
* Removed PATH from UPLOADS filepath
* Added __DIR__ to admin index.php require statements

**Modified files:**
* init.php (M)
* admin/index.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.3.5[a] (2019-03-26)

* Moved Query object initialization to init.php
* Added config.php to .gitignore (prevents issues arising with db configs on different clients)
* Built the login form
* Styled the login page
* Created login captcha
* Added some documentation
* Rebranding "Really Simple CMS" to "ReallySimpleCMS"
* Minor styling tweaks to the installation page

**Modified files:**
* .gitignore
* init.php
* login.php
* admin/install.php
* admin/setup.php (M)
* admin/includes/functions.php (M)
* admin/includes/css/install.css (M)
* includes/captcha.php (N)
* includes/functions.php (M)
* includes/globals.php (M)
* includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.3.4[a] (2019-03-24)

* Created LICENSE and README files
* Created .gitignore file (added LICENSE and todo.txt)

**Modified files:**
* .gitignore (N)
* LICENSE.md (N)
* README.md (N)

----------------------------------------------------------------------------------------------------
## Version 1.3.3[a] (2019-03-22)

* Created content directory
* Created CSS and JS files in content directory for later use
* Added error if the CMS has already been installed to prevent multiple installs
* Created Query function to show all tables in the database
* Created login page (without form)
* Install page will now redirect to setup page if config.php doesn't exist
* Buttons now have the cursor hand if hovered over
* Created functions to fetch stylesheets and scripts for the front end
* Moved getSetting, trimWords, and trailingSlash functions to globals.php
* Created front end styles file
* Front end init file now loads all required files
* Fixed a bug in the installation that allowed users to navigate to the login screen before they're supposed to
* Initialization and functions files are included in the header files of both the front end and admin dashboard
* Removed PATH constant from config setup (PATH is defined in the initialization file)

**Modified files:**
* header.php
* init.php
* login.php (N)
* admin/header.php
* admin/install.php
* content/script.js (N)
* content/style.css (N)
* includes/class-query.php
* includes/config-setup.php (M)
* includes/functions.php
* includes/globals.php
* includes/css/buttons.css (M)
* includes/css/style.css (N)

----------------------------------------------------------------------------------------------------
## Version 1.3.2[a] (2019-03-20)

* Moved change.log to its own directory (includes/logs/)

**Modified files:**
* n/a

----------------------------------------------------------------------------------------------------
## Version 1.3.1[a] (2019-03-19)

* Fixed a bug in the setup process that allowed the config.php file to be created without data
* Fixed a file path issue in functions file
* Created a function that adds a trailing slash to text
* Cleaned up the admin header
* Code cleanup
* Created a function to populate the users table
* Created admin stylesheet
* Added basic styles to the admin area

**Modified files:**
* admin/header.php
* admin/install.php
* admin/setup.php (M)
* admin/includes/functions.php
* admin/includes/css/style.css (N)
* includes/config-setup.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.3.0[a] (2019-03-18)

* Created file to initialize the CMS
* Added __DIR__ to require statements
* Created file to set up the database connection
* Created file for config setup
* Added stylesheet for the setup and install pages
* Added stylesheet for buttons
* Created file for installing the CMS
* Added a variable to test the db connection
* Added exception handling to Query class constructor
* Created and tested installation functionality
* Created function for running generic SQL queries in the Query class
* Added more documentation

**Modified files:**
* header.php (M)
* index.php (M)
* init.php (N)
* admin/install.php (N)
* admin/setup.php (N)
* admin/includes/functions.php
* admin/includes/css/install.css (N)
* includes/class-query.php
* includes/config-setup.php (N)
* includes/debug.php (M)
* includes/schema.php
* includes/css/buttons.css (N)

----------------------------------------------------------------------------------------------------
## Version 1.2.6[a] (2019-03-12)

* Created a function to construct the database schema
* Created settings admin page

**Modified files:**
* admin/settings.php (N)
* includes/schema.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.2.5[a] (2019-03-08)

* Created a function to retrieve statistics data
* Created a function for trimming words from a set of text
* Created a function for retrieving settings from the database
* Created a function to construct admin nav items

**Modified files:**
* admin/header.php
* admin/index.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.2.4[a] (2019-02-26)

* Created function to verify passwords
* Created function to retrieve user avatar
* Fixed a small bug that was triggered if a query returns null
* Created a function to create and display a statistics bar graph

**Modified files:**
* admin/includes/class-user.php
* admin/includes/functions.php
* includes/class-query.php (M)
* includes/globals.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.2.3[a] (2019-02-22)

* Added validation for edit user form data
* Created a function to delete users
* Added reset password form
* Added validation for reset password form data

**Modified files:**
* admin/users.php
* admin/includes/class-user.php

----------------------------------------------------------------------------------------------------
## Version 1.2.2[a] (2019-02-21)

* Added more documentation
* Created a function to retrieve user metadata

**Modified files:**
* admin/includes/class-user.php
* admin/includes/functions.php (M)
* includes/class-query.php (M)
* includes/globals.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.2.1[a] (2019-02-20)

* Added more documentation
* Created a function to list all users in a table
* Created admin pagination function
* Created functions for assembling admin tables
* Created a function to format date strings
* Created a function to implement admin page navigation
* Added edit user form

**Modified files:**
* admin/users.php
* admin/includes/class-user.php
* admin/includes/functions.php
* includes/class-query.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.2.0[a] (2019-02-19)

* Added ReallySimpleCMS copyright and version to admin footer
* Created a file to hold global functions
* Created a function for assembling form tags (works with the formRow function)
* Finished create user form and added validation
* Created functions for loading admin scripts and stylesheets
* Added documentation for numerous functions

**Modified files:**
* admin/footer.php
* admin/includes/class-user.php
* admin/includes/functions.php
* includes/config.php (M)
* includes/globals.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.1.2[a] (2019-02-18)

* Created function for assembling form rows
* Added create user form

**Modified files:**
* admin/users.php
* admin/includes/class-user.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.1.1[a] (2019-02-11)

* Minor updates to SELECT function
* Added function to select single rows from the database

**Modified files:**
* includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.1.0[a] (2019-02-07)

* Rebuilt functions for SELECT, INSERT, and UPDATE statements
* Created file to store deprecated functions (for potential future use)
* Extended Query class' range to work on back end
* Created admin users page

**Modified files:**
* admin/users.php (N)
* admin/includes/class-user.php (N)
* admin/includes/functions.php
* includes/class-query.php
* includes/config.php (M)
* includes/deprecated.php (N)
* includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.3[a] (2019-02-04)

* Renamed some functions in Query class
* Created a function to check for query errors
* Rebuilt function for DELETE statement

**Modified files:**
* includes/class-query.php
* includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.2[a] (2019-01-17)

* Created and tested basic functionality for UPDATE statement
* Created class for retrieving post data
* Added class autoloading
* Added basic HTML to header and footer files
* Created admin directory and basic files

**Modified files:**
* footer.php
* header.php
* index.php (M)
* admin/footer.php (N)
* admin/header.php (N)
* admin/index.php (N)
* admin/includes/functions.php (N)
* includes/class-post.php (N)
* includes/class-query.php
* includes/config.php
* includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.1[a] (2019-01-15)

* Created changelog for tracking project milestones (change.log will henceforth be ommitted from list of modified files)
* Created file with basic debugging functions
* Created and tested basic functionality for query SELECT, INSERT, and DELETE statements

**Modified files:**
* change.log (N)
* includes/class-query.php
* includes/config.php
* includes/debug.php (N)
* includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.0[a] (2019-01-14)

* Set up database and tables
* Created most of the necessary core files
* Created includes directory
* Created core database connection class
* Built basic query functionality

**Modified files:**
* .htaccess (N)
* 404.php (N)
* footer.php (N)
* header.php (N)
* index.php (N)
* page.php (N)
* robots.txt (N)
* includes/class-query.php (N)
* includes/config.php (N)
* includes/functions.php (N)