----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.8.8[a] (2019-10-02)

* Added the "menu-item" class to the list item that displays if the menu is empty
* Menu items are now properly reordered when a menu item's parent is set or unset (in most cases)
* Added more documentation to the Query class
* Widened text inputs
* Changed padding for buttons from pixels to ems

**Modified files:**
* admin/includes/class-menu.php
* admin/includes/css/style.css (M)
* includes/class-query.php
* includes/css/buttons.css (M)

----------------------------------------------------------------------------------------------------
## Version 1.8.7[a] (2019-09-29)

* Child menu items are now indented on the 'Edit Menu' page (up to 3 levels deep)
* Created a function that fetches the whole "family tree" of a menu item and returns the number of members
* Added a global variable to the Menu class to hold the member count of a menu item's "family tree"
* Created a function that fetches all descendants of a menu item
* Replaced an occurence of intval() with a casted integer in the installation file
* Added more documentation to the Query class

**Modified files:**
* admin/install.php (M)
* admin/includes/class-menu.php
* admin/includes/css/style.css
* includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.8.6[a] (2019-09-28)

* Tweaked a line of documentation in the adminNavMenuItem function
* Created a function that constructs a list of parent menu items
* Created a function that checks whether the current menu item is a descendant of other menu items
* Menu items can now be nested
* Created a function that determines the nested depth of a menu item

**Modified files:**
* admin/includes/class-menu.php
* admin/includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.8.5[a] (2019-09-20)

* Tweaked a previous entry in the changelog
* Added a line of documentation to the schema file
* Tweaked documentation and updated a constant in the initialization file
* A notice will now display if the content directory's index file is accessed directly
* Added more documentation to the content index file
* Added FontAwesome icons
* Tweaked documentation in the includes CSS stylesheet
* Included the FontAwesome stylesheet in the admin header file
* Added a parameter to the adminNavMenuItem function to include an icon
* Added styling for the icons
* Adjusted the width, font size, and margins of the admin nav menu and menu items
* Made the admin nav menu mobile responsive
* Made several other elements mobile responsive

**Modified files:**
* init.php
* admin/header.php
* admin/includes/functions.php
* admin/includes/css/style.css
* content/index.php
* includes/schema.php (M)
* includes/css/fa-icons.css (N)
* includes/css/style.css (M)
* includes/fonts/fa-solid.ttf (N)
* includes/fonts/fa-brands.ttf (N)
* includes/fonts/fa-regular.ttf (N)

----------------------------------------------------------------------------------------------------
## Version 1.8.4[a] (2019-09-15)

* Added more documentation to the root index file and removed a closing PHP tag
* Minor tweak to the content index file
* Added an extra parameter to the Menu::deleteMenuItem function
* When a menu item is deleted, the indexes of any other menu items are now reordered properly
* Added some documentation to the Query class
* The CMS now checks whether all database tables are accounted for on initialization
* Existing tables are now dropped during installation if there are tables missing (they will not be deleted in a proper installation)
* Tweaked the text on the installation form
* Tweaked some previous entries in the changelog

**Modified files:**
* index.php
* init.php
* admin/install.php
* admin/includes/class-menu.php
* content/index.php (M)
* includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.8.3[a] (2019-09-14)

* Added a disabled input field that displays the menu item's type (post/page, category, or custom)
* Added styling for disabled input fields
* Renamed the Menu::getPostsList function to Menu::getMenuItemsList
* Category menu items can now be edited
* Custom links can now be added to menus
* Custom menu items can now be edited
* Menu items can now be sorted up (to a lower index) and down (to a higher index)
* Removed an old, unused test function from the includes functions file
* Added an exit function after a redirect in the initialization file
* Updated some documentation in the login file
* Tweaked some previous entries in the changelog

**Modified files:**
* admin/includes/class-menu.php
* admin/includes/css/style.css
* includes/functions.php
* init.php (M)
* login.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.8.2[a] (2019-09-13)

* Fixed a typo and escaped some special characters in the changelog
* Tweaked the documentation in all the admin files
* Renamed all public functions with 'entry' or 'entries' in their name to the name of their class (e.g., User::listEntries -> User::listUsers)
* Added a redirect from the 'List Posts' page to the 'List Menus' page if the requested post's type is 'nav_menu_item'
* Renamed the Menu::getMenuItemsList function to Menu::getMenuItemsLists and removed its optional parameter
* Cleaned up the Menu::getMenuItemsLists function and split pages and posts into separate fieldset lists
* Added styling to the menu items fieldsets
* Added a checkbox list for categories and fields for adding custom menu items
* Categories can now be added to menus

**Modified files:**
* admin/categories.php
* admin/index.php (M)
* admin/menus.php (M)
* admin/posts.php
* admin/settings.php (M)
* admin/users.php
* admin/widgets.php
* admin/includes/class-category.php
* admin/includes/class-menu.php (M)
* admin/includes/class-post.php
* admin/includes/class-user.php
* admin/includes/class-widget.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.8.1[a] (2019-09-09)

* Tweaked some documentation in the Post class
* The count value now increments when a new menu is created with menu items
* Switched a margin from the data form content block to the metadata block
* Fixed an issue where the 'Categories' block in the post editor still was captioned 'Attributes' (type: 'post' only)
* Added styling for new a 'item-list' class
* Updated the menu forms to more closely resemble the post forms (their functionality still remains different)
* Rebuilt the Menu::getMenuItems function for use on the menu forms pages
* Added extra validation to the Category::deleteEntry function
* Menus can now be edited and deleted
* Added a wrapper element for data forms (allows for floating blocks outside of the main form)
* Replaced occurrences of count() === 0 with empty() when checking if no entries exist on the list entries pages
* Set all form elements to use the Segoe UI font (including the installation forms)
* Styled the reset password button on the 'Edit User' form
* Added the admin footer to the menus page
* Renamed all public menu functions (e.g., Menu::createEntry -> Menu::createMenu)
* Renamed the Menu::validateData function to Menu::validateMenuData
* Created a function that constructs the 'Edit Menu Item' form
* Created a function that constructs a list of posts (Menu class)
* Created a function to fetch a menu item's metadata
* Menu items can now be edited and deleted
* A cancel button will now appear when a menu item is being edited

**Modified files:**
* admin/menus.php
* admin/includes/class-category.php
* admin/includes/class-menu.php
* admin/includes/class-post.php
* admin/includes/class-settings.php
* admin/includes/class-user.php
* admin/includes/class-widget.php
* admin/includes/css/install.css (M)
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.8.0[a] (2019-09-08)

* Fixed a bug with the pagerNav function that added too many 'paged' params
* Fixed the pagination on the 'List User Roles' page
* Created the Menu class
* Created admin 'List Menus' page and added a link to the nav menu
* Fixed an issue with the getCurrentPage function that prevented the 'Create Menu' page from displaying as the current page
* The 'nav_menu' taxonomy is now created during installation
* Created a function that constructs the 'Create Menu' form
* Created a function that constructs a list of nav menu items
* Renamed the 'privileges-list' id to the 'checkbox-list' class
* Created a function that checks whether the nav_menu slug already exists
* Created a function that constructs the 'Edit Menu' form
* Created a function to fetch the nav menu items
* Menus can now be created

**Modified files:**
* admin/header.php (M)
* admin/menus.php (N)
* admin/includes/class-menu.php (N)
* admin/includes/class-settings.php
* admin/includes/functions.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.7.5[a] (2019-09-07)

* Tweaked documentation in the users admin file
* The categories pages now check whether a logged in user has sufficient privileges to view the pages
* The posts pages now check whether a logged in user has sufficient privileges to view the pages
* The Category::getParent function now returns an em dash if a category has no parent
* The default user roles now display in a separate list below user-created roles
* Added styling for subheadings
* Renamed the 'current' parameter in the pagerNav function to 'page'
* Improved the functionality of the pagerNav function

**Modified files:**
* admin/categories.php
* admin/posts.php
* admin/users.php (M)
* admin/includes/class-category.php
* admin/includes/class-settings.php
* admin/includes/functions.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.7.4[a] (2019-09-03)

* Tweaked documentation in the Post class
* Adjusted the margins on list entries pages when a status message is displayed
* Added a default value for the '\_default' column in the user_roles table
* Added a full file path to the autoload class function
* Fixed an issue with the getCurrentPage function that prevented the 'Create Widget' page from displaying as the current page
* Added more documentation to the admin functions
* Tweaked documentation in the User class
* When creating a new user, the role dropdown now displays the default user role
* Changed an HTML id to a class
* Added the delete link for users on the 'List Users' page
* Tweaked the default site description in the populateTables function
* Tweaked the styling of form tables
* The widgets pages now check whether a logged in user has sufficient privileges to view the pages
* The users pages now check whether a logged in user has sufficient privileges to view the pages

**Modified files:**
* admin/users.php
* admin/widgets.php
* admin/includes/class-post.php (M)
* admin/includes/class-settings.php (M)
* admin/includes/class-user.php
* admin/includes/functions.php
* admin/includes/css/style.css
* includes/schema.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.7.3[a] (2019-09-02)

* Tweaked the deleted entry status message for all classes
* Replaced all occurrences of the header function with the new redirect function in the User and Widget classes
* Replaced a null comparison an empty with is_null in the User::listEntries function
* The 'Full Name' column on the 'List Users' page now displays an em dash if the user has no first name or last name
* The Post::getParent function now returns an em dash if a post has no parent
* Fixed some issues with the getCurrentPage function that caused some pages not to show as the current nav menu item
* Added a '\_default' column to the user_roles table (this will be used to protect default roles from tampering)
* Removed edit and delete actions from default user roles on the 'List User Roles' page
* Attempting to edit a default user role will now redirect the user to the 'List User Roles' page

**Modified files:**
* admin/includes/class-category.php (M)
* admin/includes/class-post.php
* admin/includes/class-settings.php
* admin/includes/class-user.php
* admin/includes/class-widget.php
* admin/includes/functions.php
* includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.7.2[a] (2019-09-01)

* Changed the pagination 'page' GET varible to 'paged' to differentiate it from the settings 'page' GET variable
* Added more documentation to the admin functions
* The 'can_view_user_roles' privilege is now created on installation
* Created a global function that checks whether a user has a specified privilege (to protect certain pages from users with limited privileges)
* Created a global function for easy url redirection
* Added a temporary $\_SESSION variable to the admin header (to test user roles/privileges)
* The settings pages now check whether a logged in user has sufficient privileges to view the pages
* Created a function to fetch the user privileges
* Tweaked some documentation in the Post class
* The Post::getCategories function now returns an em dash if a post has no categories
* Renamed the Settings::userRolesSettings function to Settings::listUserRoles
* Renamed the Settings::validateData function to Settings::validateSettingsData
* Created a function that constructs the 'Create User Role' form
* The formRow function now can accept string values in its 'args' parameters
* Cleaned up the formRow function's code and added more documentation
* Added styling for the user privilege list
* Tweaked styling of the post category list
* Created a function that constructs a list of user privileges
* Created a function that constructs the 'Edit User Role' form
* Created a function to validate the user role form data
* Replaced all occurrences of the header function with the new redirect function in the Post and Category classes
* Fixed an issue where categories would get deleted from a post unnecessarily
* Created a function to delete user roles

**Modified files:**
* admin/header.php (M)
* admin/settings.php
* admin/includes/class-category.php
* admin/includes/class-post.php
* admin/includes/class-settings.php
* admin/includes/class-user.php (M)
* admin/includes/class-widget.php (M)
* admin/includes/functions.php
* admin/includes/css/style.css
* includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.7.1[a] (2019-08-30)

* Tweaked some documentation in the Post class
* Tweaked how the category post count is calculated when a post is created
* The user_privileges table is now populated during installation
* The user_relationships table is now populated during installation
* Added the 'clear' class to all admin page wrapper elements (prevents page content from overflowing into the footer)
* Removed a line of documentation from the categories file
* Renamed the Settings::listSettings function to Settings::generalSettings
* Created the 'User Roles' settings page
* Added a nav menu item for the user roles settings page
* Added an extra check in the getCurrentPage function to look for the page GET parameter (for settings pages)

**Modified files:**
* admin/categories.php (M)
* admin/header.php
* admin/index.php (M)
* admin/posts.php (M)
* admin/settings.php
* admin/users.php (M)
* admin/widgets.php (M)
* admin/includes/class-post.php
* admin/includes/class-settings.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.7.0[a] (2019-08-24)

* Changed possible statuses for widgets from 'draft' and 'published' to 'active' and 'inactive'
* Added a warning to all admin pages if the user has JavaScript disabled in their browser
* Added a redirect from the 'List Posts' page to the 'List Widgets' page if the requested post's type is 'widget'
* Added a redirect from the 'Edit Post' page to the 'Edit Widget' page if the requested id corresponds to a widget
* Renamed the 'roles' table to 'user_roles', the 'privileges' table to 'user_privileges', and the 'rp_relationships' table to 'user_relationships'
* The user_roles table is now populated during installation
* Consolidated all database table populate functions into one and moved the old functions to the deprecated functions file
* The user created on installation is now given the administrator user role
* Replaced the UPL_DIR constant with UPLOADS in the User::getAvatar function (the former is no longer used)
* Created a function that constructs a list of user roles (Settings class)
* Tweaked the username column's styling on the 'List Users' page
* Created a function that fetches a specified user's role (User class)
* Created a function that constructs a list of user roles (User class)
* The Post::getAuthorList function no longer calls the Post::getAuthor function
* The Post::getParentList function no longer calls the Post::getParent function
* User roles now appear on the 'Create User' and 'Edit User' forms
* Added a missing class to a button on the 'Reset Password' form
* Fixed the alignment of the pass_saved labels on the 'Create User' and 'Reset Password' forms
* The Category::getParentList function no longer calls the Category::getParent function

**Modified files:**
* admin/header.php
* admin/install.php
* admin/posts.php
* admin/includes/class-category.php
* admin/includes/class-post.php
* admin/includes/class-settings.php
* admin/includes/class-user.php
* admin/includes/class-widget.php
* admin/includes/functions.php
* admin/includes/css/style.css
* includes/deprecated.php
* includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.6.3[a] (2019-08-22)

* Tweaked documentation in the Post class
* Improved validation for the 'Edit Post' form
* Widgets can no longer be edited via the 'Edit Post' form
* Fixed some errors that would pop up if the post id was invalid
* Tweaked the pagination code for the User class
* A notice is now displayed on the 'List Users' page if no users exist in the database
* Tweaked documentation in the User class
* Improved some styling of elements on the installation form
* Added and renamed some CSS classes on the installation page
* Minor CSS cleanup in the admin stylesheet
* The page.php file is now deprecated
* Updated documentation in the config setup and captcha files
* Updated documentation in the Query class

**Modified files:**
* page.php (D)
* admin/install.php
* admin/includes/class-post.php
* admin/includes/class-user.php
* admin/includes/functions.php
* admin/includes/css/install.css
* admin/includes/css/style.css (M)
* includes/captcha.php (M)
* includes/class-query.php
* includes/config-setup.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.6.2[a] (2019-08-20)

* Fixed an issue with the Search Engine Visibility checkbox label that prevented the checkbox from being checked
* Removed the jQuery library and script from the installation page (no longer needed since clicking the checkbox now works)
* Renamed a CSS class on the installation page
* Changed the default value for the getStylesheet and getScript functions' version parameter to the VERSION constant
* Added more documentation to the global functions
* Cleaned up the trimWords function
* Changed the default value for the getAdminStylesheet and getAdminScript functions' version parameter to the VERSION constant
* Added more documentation to the admin functions
* Tweaked the styling of data form tables
* An error will no longer display if no content is specified for a select or textarea in the formTag function
* Added a status field to the Widget class forms
* Tweaked documentation in the Post class
* Added form validation for widgets
* Modified date is now set when a post is edited
* Removed a line of documentation from the widgets file
* Tweaked the pagination code for the Post class
* Removed a few lines of documentation from the Category class
* Tweaked the styling of some elements on the 'General Settings' page

**Modified files:**
* admin/install.php
* admin/widgets.php (M)
* admin/includes/class-category.php (M)
* admin/includes/class-post.php
* admin/includes/class-settings.php
* admin/includes/class-widget.php
* admin/includes/functions.php
* admin/includes/css/install.css (M)
* admin/includes/css/style.css
* includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.6.1[a] (2019-08-18)

* Fixed an issue with form submissions where old data would be fetched before the new data was submitted
* The text for the Search Engine Visibility checkbox on the installation form can now be used to check the checkbox
* Included the jQuery library on the installation page and added a custom script
* Added more documentation to the Post class
* Created a function that constructs the 'Edit Widget' form

**Modified files:**
* admin/install.php
* admin/widgets.php
* admin/includes/class-category.php
* admin/includes/class-post.php
* admin/includes/class-user.php
* admin/includes/class-widget.php
* admin/includes/css/install.css

----------------------------------------------------------------------------------------------------
## Version 1.6.0[a] (2019-08-16)

* The admin nav menu now properly displays the current page, even if it's in a submenu
* Reorganized the admin nav menu items
* Improved styling of the admin nav menu
* The getCurrentPage function now adds any action in the url to the end of the current page
* Added more documentation to the User class and fixed the styling on the reset password form
* Renamed the data form's hr class from 'divider' to 'separator' and added styling for it
* Created the Widget class
* Created admin 'List Widgets' page and added a link to the nav menu
* Fixed some documentation in the Category class
* Created a function that constructs the 'Create Widget' form
* Tweaked the documentation in the installation stylesheet

**Modified files:**
* admin/header.php
* admin/widgets.php (N)
* admin/includes/class-category.php
* admin/includes/class-settings.php (M)
* admin/includes/class-user.php
* admin/includes/class-widget.php (N)
* admin/includes/functions.php
* admin/includes/css/install.css (M)
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.5.7[a] (2019-08-15)

* Improved styling of the statistics graph
* Reordered the bars in the statistics graph
* Rebuilt the admin submenu item functionality
* Removed a closing PHP tag in the admin settings file
* Tweaked some code in the setup file
* Tweaked some code and fixed some documentation in the installation file
* The CMS version constant now only displays the version number (e.g., 1.5.7); the RSVersion function now includes the long form
* Added the CMS version to the admin stylesheets and scripts
* Fixed some documentation in the global functions file
* Removed a closing PHP tag in the admin index file
* Tweaked some code in the Settings and User classes

**Modified files:**
* admin/footer.php (M)
* admin/header.php
* admin/index.php (M)
* admin/install.php
* admin/settings.php (M)
* admin/setup.php (M)
* admin/includes/class-settings.php (M)
* admin/includes/class-user.php (M)
* admin/includes/functions.php
* admin/includes/css/style.css
* includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.5.6[a] (2019-08-14)

* A notice will now be displayed on the List Categories page if there are no categories in the database
* Minor code tweak in the Post class
* Adjusted the margins for status messages
* Removed some deprecated code from the admin functions file
* Changed some class and id names for the statistics graph
* Added styling to the statistics graph
* Created a file for admin scripts
* Created a file for the jquery library
* Added admin scripts and jquery to the admin footer file
* Added optional version parameter to stylesheet and script fetching functions
* Created a script that generates the bars for the statistics graph

**Modified files:**
* admin/footer.php
* admin/includes/class-category.php
* admin/includes/class-post.php (M)
* admin/includes/functions.php
* admin/includes/css/style.css
* admin/includes/js/script.js (N)
* includes/globals.php
* includes/js/jquery.min.js (N)

----------------------------------------------------------------------------------------------------
## Version 1.5.5[a] (2019-08-05)

* Created an index page for the content directory
* Created functions to include theme header and footer files
* Moved the front end header and footer files to the content directory
* The root index file now includes the content index file
* Created a new defined constant for the content directory
* Updated documentation in the deprecated functions file

**Modified files:**
* footer.php (R)
* header.php (R)
* index.php
* init.php (M)
* content/footer.php (N)
* content/header.php (N)
* content/index.php (N)
* includes/deprecated.php
* includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.5.4[a] (2019-08-04)

* When a post is deleted, its term relationships are also deleted and the category counts are updated
* Added the 'button' class to the pager nav links
* Styled the pager nav buttons
* Tweaked styling to the entry count
* Created a function to fetch the current admin page
* The admin body tag is now assigned a class based on the current page
* Changed some class names relating to the admin nav menu
* Renamed the adminNavItem function to adminNavMenuItem
* The admin nav menu now properly displays the current nav menu item
* Submenus are now visible if they are children of the current nav menu item

**Modified files:**
* admin/header.php (M)
* admin/includes/class-post.php
* admin/includes/functions.php
* admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.5.3[a] (2019-08-02)

* Categories can now be removed from posts
* The Settings form now has styling
* Tweaked the documentation of the Settings class
* Tweaked the styling of form tables

**Modified files:**
* admin/includes/class-post.php
* admin/includes/class-settings.php (M)
* admin/includes/css/style.css

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
* Change.log has been renamed to changelog.md (it will henceforth be omitted from list of modified files)
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
* Replaced '\n' with chr(10) in the logError function
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
* Added '\*' attribute to input tag in formTag function (this is for miscellaneous attributes like 'readonly' or 'checked')
* Allowed 0 to be passed as a legitimate value to an input
* Fixed a bug in the formRow function that prevented adding a label to a single input argument
* Initialization now terminates if database is not installed (prevents an error from generating in error_log)
* A sample page will now be created on installation (it is set as the default home page)
* Cleaned up the change log a bit (mostly rewording and adding a few things that had been omitted)

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

* Added ReallySimpleCMS copyright and version to the admin footer
* Created a file to hold global functions
* Created a function for assembling form tags (works with the formRow function)
* Finished the 'Create User' form and added validation
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

* Created a function for assembling form rows
* Created a function that constructs the 'Create User' form

**Modified files:**
* admin/users.php
* admin/includes/class-user.php
* admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.1.1[a] (2019-02-11)

* Minor updates to the SELECT function
* Created a function to select single rows from the database

**Modified files:**
* includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.1.0[a] (2019-02-07)

* Rebuilt the functions for the SELECT, INSERT, and UPDATE statements
* Created a file to store deprecated functions (for potential future use)
* Extended the Query class' scope so it works on the back end
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

* Renamed some functions in the Query class
* Created a function to check for query errors
* Rebuilt the function for the DELETE statement

**Modified files:**
* includes/class-query.php
* includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.2[a] (2019-01-17)

* Created and tested basic functionality for the UPDATE statement
* Created a class for retrieving post data
* Added class autoloading
* Added basic HTML to the header and footer files
* Created the admin directory and basic files

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

* Created a changelog for tracking project milestones (change.log will henceforth be omitted from list of modified files)
* Created a file with basic debugging functions
* Created and tested basic functionality for query SELECT, INSERT, and DELETE statements

**Modified files:**
* change.log (N)
* includes/class-query.php
* includes/config.php
* includes/debug.php (N)
* includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.0[a] (2019-01-14)

* Set up the database and tables
* Created most of the necessary core files
* Created the includes directory
* Created the core database connection class
* Built the basic query functionality

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