----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.5.2[a] (2019-08-01)

* Changed the default value for datetime columns in the schema to avoid major errors during installation in newer versions of MySQL
* Tweaked some documentation in the Post class
* Created a function that checks whether a category slug exists in the database
* Categories can now be created
* Added more documentation to the User class
* Cleaned up the code in the User::validateData function
* The $id param for the User::usernameExists function is no longer optional
* Categories can now be edited
* Added styling for the categories list
* Removed parent list and added categories list to 'Create Post' page
* Categories can now be added to posts (they cannot yet be removed)

**Modified files:**
* admin/includes/class-category.php
* admin/includes/class-post.php
* admin/includes/class-user.php
* admin/includes/css/style.css
* includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.5.1[a] (2019-07-30)

* Created a function that constructs the 'Edit Category' form
* Created a function to delete categories from the database
* Tweaked validation in Post::deleteEntry function
* Added more documentation to the User class
* The category's parent can now be set to 'none' on the 'Create Category' form

**Modified files:**
* admin/categories.php
* admin/includes/class-category.php
* admin/includes/class-post.php (M)
* admin/includes/class-user.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.5.0[a] (2019-07-28)

* Created admin 'List Categories' page
* Added the categories page to the admin navigation
* Created the Category class
* The 'Create <classname>' buttons have been relabeled as 'Create New'
* Modified the logic of the status message display on the list entries pages
* Fixed some erroneous documentation in the functions file
* Created a function to populate the taxonomies table
* Created a function to fetch a taxonomy's id based on its name
* Created a function to populate the terms table
* Created a function that constructs a list of all categories in the database
* A sample blog post is now created on CMS install in addition to the sample home page
* Created a function to populate the term_relationships table
* Created a function to fetch the post categories
* Created a function to fetch a category's parent
* Created a function that constructs the 'Create Category' form
* Created a function that constructs a list of parent categories
* Created a function that checks whether the current category is a descendant of other categories
* Removed 'parent' column from 'List Posts' page (the 'post' post type is not meant to be hierarchical)

**Modified files:**
* admin/categories.php (N)
* admin/header.php
* admin/install.php
* admin/includes/class-category.php (N)
* admin/includes/class-post.php
* admin/includes/class-user.php (M)
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.10[a] (2019-07-26)

* Added terms, taxonomies, and term_relationships tables to the schema
* Renamed 'rp_link' table to 'rp_relationships'
* Added a line of documentation to the CMS installation file
* Added form validation to Post::editEntry function
* Changed the link color on status messages
* Added table column for categories on 'post' post type page
* Published pages now have proper permalinks for the 'view' link
* The site url is now set during installation
* Required field labels now have a red asterisk next to them instead of '(required)'
* Improved styling on form pages using the form table layout
* Improved documentation for the User class
* Improved validation in the User::editEntry function
* Created a function to retrieve post metadata

**Modified files:**
* admin/install.php
* admin/includes/class-post.php
* admin/includes/class-user.php
* admin/includes/functions.php
* admin/includes/css/style.css
* includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.4.9[a] (2019-07-23)

* Improved styling on 'Create Post' form
* Created a function that checks whether a post is in the trash
* Created a function that constructs a post permalink
* Created a function that checks whether the current post is a descendant of other posts
* Built 'Edit Post' page (posts cannot be submitted yet)

**Modified files:**
* admin/includes/class-post.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.8[a] (2019-07-22)

* Added form validation to Post::createEntry function
* Created a function to check whether a post slug already exists in the database
* Added styling to 'Create Post' form
* Tweaked styling on the admin footer
* Trashed posts will no longer appear in post parent dropdowns

**Modified files:**
* admin/footer.php (M)
* admin/includes/class-post.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.7[a] (2019-07-21)

* Improved styling on list entries pages
* Improved exception handling in the following Post class functions: trashEntry, restoreEntry, getParent
* A post's status will now display next to the post title on the list posts page (unless the post is published)
* Added a column to display the post's parent (if it has one) on the list posts page
* Posts can now be deleted
* Tweaked a previous entry in the changelog
* Posts can now be created (no validation yet)

**Modified files:**
* admin/includes/class-post.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.6[a] (2019-06-20)

* Buttons will no longer have underlined text on mouse hover
* The 'All Posts' table now tells whether metadata has been provided
* Posts can now be trashed and restored
* A post slug postmeta entry will no longer be created during the CMS install

**Modified files:**
* admin/includes/class-post.php
* admin/includes/functions.php (M)
* includes/css/buttons.css (M)

----------------------------------------------------------------------------------------------------
## Version 1.4.5[a] (2019-05-29)

* Finished building the 'Create Post' form
* Styled list entries pages
* Added an optional parameter to the tableCell function to allow a cell to span multiple columns
* Added a notice to be shown if no posts can be retrieved from the database on the 'List Posts' page

**Modified files:**
* admin/includes/class-post.php
* admin/includes/functions.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.4[a] (2019-05-03)

* Continued building the 'Create Post' form
* Added a new parameter to the formTag function and functionality for building a label tag
* Fixed some issues in the formRow function caused by updates to the formTag function
* Created a function that constructs a list of post authors
* Created a function that constructs a list of parent posts
* Created a function to fetch a post's parent

**Modified files:**
* admin/includes/class-post.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.3[a] (2019-04-22)

* Added 'button' class to form submit buttons
* Added more documentation
* Added placeholder to input tag in formTag function
* Fixed a minor bug in the formRow function
* Continued building the 'Create Post' form

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

* Created admin 'List Posts' page and Post class
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
* Created admin settings page

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
* Created admin 'List Users' page

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