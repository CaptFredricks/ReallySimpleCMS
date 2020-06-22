# ReallySimpleCMS Changelog (Beta)

----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - renamed file, X - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.0.0[b] (2020-06-21)

* Created content for the README file
* Renamed changelog.md to changelog-alpha.md
* Created a new changelog for Beta
* Improved mobile styling for the setup and installation pages
* Tweaked some of the text in the setup.php file
* Fixed a bug where a 'DROP TABLE' query was run on empty database installs
* Improved mobile styling for the log in and forgot password pages
* Created a function for registering widgets for a theme
* The Carbon theme now registers three widgets by default
* Created a function for registering menus for a theme
* The Carbon theme now registers two menus by default
* Created a global function that sanitizes text strings
* Created a global function that registers custom post types
* Moved the admin nav menu items to a new function that simply displays them
* The includes/functions.php and themes/<theme>/functions.php files are now included on the back end
* The admin nav menu now supports custom post types
* User privileges are now created when a custom post type is registered
* The Post::getPermalink function is now deprecated
* Added a 'type' parameter to the front end Post::getPostPermalink function
* Modified the way post permalinks are constructed so that custom post types have a base permalink before the slug
* Changed the inclusion order of the load-theme.php, class-post.php, class-category.php, and load-template.php files in the root index.php file
* The load-template.php file is no longer included in the load-theme.php file
* Fixed an error that occurred when attempting to move a menu item up or down when it is the only item on a given menu
* Tweaked how slugs are sanitized in several back end classes
* If the site's home page is accessed from its full permalink, it now redirects to the home URL (e.g., www.mydomain.com)
* Admin menu items are now hidden if a logged in user does not have sufficient privileges to view them

**Modified files:**
* README.md
* admin/header.php
* admin/includes/class-category.php (M)
* admin/includes/class-menu.php (M)
* admin/includes/class-post.php
* admin/includes/class-theme.php (M)
* admin/includes/class-widget.php (M)
* admin/includes/css/install.css
* admin/includes/css/install.min.css
* admin/includes/functions.php
* admin/install.php
* admin/setup.php
* content/themes/carbon/functions.php
* includes/class-post.php
* includes/css/style.css (M)
* includes/deprecated.php
* includes/functions.php
* includes/globals.php
* includes/load-theme.php (M)
* includes/logs/changelog-alpha.md (R)
* includes/logs/changelog-beta.md (N)
* index.php