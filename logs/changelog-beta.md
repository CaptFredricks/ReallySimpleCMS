# ReallySimpleCMS Changelog (Beta)

**Legend**
- N = new file
- D = deprecated file
- R = renamed file
- X = removed file
- M = minor change

**Versions**
- X.x.x (major release)
- x.X.x (feature update release)
- x.x.X (standard/minor release)
- x.x.x.X (bug fix/emergency patch release)
- x.x.x_snap-xx (snapshot release)

**Other**
- [a] = alpha
- [b] = beta

## Version 1.3.13.1[b] (2023-12-25)

- Incremented the CMS version

**Modified files:**
- n/a

## Version 1.3.13[b] (2023-12-24)

- Cleaned up documentation in multiple files
  - A new, detailed comment about the loading order of core files has been added to the top of the file
- Reamed the `backward-compat.php` file to `polyfill-functions.php`
- Post types and taxonomies now support having a slug that differs from their name
- Post types now support having multiple associated taxonomies
- Cleaned up code in the admin `Post` class
- Tweaked styling of the Carbon theme
- Cleaned up code in various functions
- New functions:
  - Admin `Post` class (`pageHeading`)
  - `critical-functions.php` (`checkDBStatus`)
- Renamed functions:
  - Admin `Post` class (`validateData` -> `validateSubmission`)

**Modified files:**
- admin/includes/bulk-actions.php (M)
- admin/includes/class-post.php
- admin/includes/functions.php
- admin/posts.php
- content/themes/carbon/category.php (M)
- content/themes/carbon/footer.php (M)
- content/themes/carbon/functions.php
- content/themes/carbon/style.css (M)
- includes/class-post.php
- includes/class-term.php
- includes/critical-functions.php
- includes/functions.php
- includes/global-functions.php
- includes/polyfill-functions.php (R,M)
- includes/schema.php (M)
- includes/theme-functions.php
- index.php (M)
- init.php

## Version 1.3.12.1[b] (2023-12-13)

- Tweaked a previous entry in the changelog
- Tweaked title tags on all front end pages
- The maintenance page now has a more descriptive title tag
- Removed some redundant code in the initialization file

**Bug fixes:**
- The front end doesn't properly display due to a faulty logic check during initialization

**Modified files:**
- 404.php (M)
- includes/global-functions.php (M)
- includes/maintenance.php (M)
- includes/theme-functions.php (M)
- init.php (M)
- login.php (M)

## Version 1.3.12[b] (2023-12-13)

- Added the following to the admin about page:
  - Added a link to the changelog on the GitHub repo
  - Added DOMtags version information
  - Added links to sources and documentation for included libraries
- Comments with `unapproved` status are now changed to `pending`
- Removed old scripts from the `update-db.php` file for versions prior to `1.2.0[b]`
- Cleaned up code in the `dashboardWidget` and `statsBarGraph` functions
- Updated jQuery to v3.7.1
- Added a new global constant for the `resources` directory
- Moved core and admin CSS and JS files to the `resources` directory
- Moved the `logs` directory to the root
- Tweaked the directives in the `.htaccess` file
- Added functionality to tag deprecated functions that outputs a notice when in debug mode
- Removed several very old functions from the `deprecated.php` file
  - This file itself is now deprecated in favor of the new deprecation system
- Added a new setting that defines the path of the login URL
  - This adds an extra layer of security to keep `/login.php` from being directly accessed
- Added a new global constant for the DOMtags version
- Cleaned up the database schema file
- Renamed the following database columns:
  - `postmeta` and `usermeta` tables (`_key` -> `datakey`)
  - `user_roles` table (`_default` -> `is_default`)
  - The database will automatically update to accommodate these changes
- Changed the format of snapshot versions from `x.x.x[x]{ss-xx}` to `x.x.x[x]_snap-xx`
- New functions:
  - `debug.php` (`deprecated`, `errorHandler`)
  - `functions.php` (`handleSecureLogin`)

**Bug fixes:**
- Custom post types and taxonomies are not loaded due to improper initialization order in `init.php`
- If an integer value is returned in the database fetch, it triggers a fatal error in `Query::selectField`

**Modified files:**
- .htaccess (M)
- admin/about.php
- admin/header.php (M)
- admin/includes/ajax.php (M)
- admin/includes/class-comment.php
- admin/includes/class-login.php (M)
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-notice.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-term.php (M)
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/functions.php
- content/themes/carbon/functions.php (M)
- includes/class-comment.php
- includes/class-login.php
- includes/class-menu.php (M)
- includes/class-post.php (M)
- includes/class-query.php (M)
- includes/constants.php
- includes/debug.php
- includes/deprecated.php (D)
- includes/functions.php
- includes/global-functions.php
- includes/schema.php
- includes/update-db.php
- init.php
- login.php
- resources/css/font-awesome-rules.min.css (M)
- resources/js/jquery.min.js

## Version 1.3.11.2[b] (2023-11-06)

- Tweaked a previous entry in the changelog

**Bug fixes:**
- The `bodyClasses` function throws an error when the first parameter is a string

**Modified files:**
- includes/functions.php (M)

## Version 1.3.11.1[b] (2023-11-06)

- Incremented the CMS version

**Modified files:**
- n/a

## Version 1.3.11[b] (2023-11-06)

- Only admins can now see the Software and Database sections of the admin About page
- Removed a deprecated constant
- The minimum PHP version that the CMS supports is now `8.0` and the recommended version is `8.1` or higher
- Code cleanup in several admin files and the front end `functions.php`
- Renamed `upload.php` to `ajax-upload.php`
- Added DOMtags library (all files associated with it will not be added to the changelog henceforth)
  - Replaced raw HTML with DOMtags in many places on the admin dashboard
  - The `formTag` and `tag` functions are now deprecated in favor of the new `domTag` function
- Began process of moving static resources to their own directory
- Added exception handling for the `order_by` clause in queries
- The update script files are now only run if the current session is from a logged in user
- Code cleanup in the `init.php` file
- Added a new post status: `private`, which allows for privately publishing pages (they will be accessible to logged in visitors only)
- Improved validation in the admin `Post` class
- New functions:
  - Admin `Post` class (`getStatusList`)
  - `functions.php` (`guessPageType`)

**Bug fixes:**
- A method in the admin `Comment` class is trying to pass the wrong data type as one of its args
- Leading slashes aren't always added to files being autoloaded
- Two scripts are leading to corruptions in `config.php`:
  - The `update.php` script that updates old database constants runs even if no change needs to be made
  - The `Query::setCharset` method runs even if no change needs to be made
- Renamed class methods are still being called in the `bulk-actions.php` file

**Modified files:**
- admin/about.php
- admin/includes/ajax-upload.php (R,M)
- admin/includes/bulk-actions.php (M)
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-post.php
- admin/includes/class-term.php
- admin/includes/class-user.php
- admin/includes/css/style.css (M)
- admin/includes/functions.php
- admin/includes/modal-upload.php (M)
- admin/install.php (M)
- includes/class-dom-tag.php (N, *DOMtags*)
- includes/class-post.php
- includes/class-query.php
- includes/constants.php (M)
- includes/critical-functions.php (M)
- includes/css/font-awesome-rules.min.css (M)
- includes/css/style.css (M)
- includes/dom-tags.php (N, *DOMtags*)
- includes/dom-tags/class-\*.php (N, *DOMtags*)
- includes/dom-tags/interface-dom-tag.php (N, *DOMtags*)
- includes/functions.php
- includes/global-functions.php
- includes/logs/changelog-dom-tags.md (N, *DOMtags*)
- includes/update.php
- init.php

## Version 1.3.10.2[b] (2023-09-21)

- n/a

**Bug fixes:**
- Old installations using the `DB_CHAR` constant break on any pages that make use of it

**Modified files:**
- admin/setup.php (M)
- includes/update.php

## Version 1.3.10.1[b] (2023-09-20)

- Incremented the CMS version
- Tweaked a previous entry in the changelog

**Modified files:**
- n/a

## Version 1.3.10[b] (2023-09-20)

- Tweaked a previous entry in the changelog
- When the record search button is clicked, the form field now receives focus
- Admin classes cleanup
- Implemented the new admin interface
- Removed the admin `Category` class
- Added various new data to the admin About page
- Extensive cleanup of the `Query` class
- All variations of the `select` query can now make use of the `BETWEEN` and `NOT BETWEEN` operators
- Added support for database character sets and collations
- Extensive cleanup of the setup and installation files
- Tweaked styling of the database setup and installation pages
- The `DB_CHAR` constant has been renamed to `DB_CHARSET`
- Added a new database constant, `DB_COLLATE`, which stores the database collation
- New functions:
  - `Query` class (`getAttr`, `hasCap`, `initCharset`, `setCharset`)
  - `AdminInterface` interface (`createRecord`, `deleteRecord`, `editRecord`, `listRecords`)
    - Applied to the following classes: `Comment`, `Menu`, `Post`, `Term`, `UserRole`, `User`, `Widget`

**Bug fixes:**
- A minor styling tweak from the last version was not served in the minified version of the CSS file
- The timezone is not properly set in the `php.ini` config file
- The database setup and installation pages can't be viewed due to certain functions being moved away from `global-functions.php`
- Menu items appear at the top when first added to a menu

**Modified files:**
- admin/about.php
- admin/categories.php
- admin/comments.php (M)
- admin/includes/class-category.php (X)
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-notice.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-term.php
- admin/includes/class-theme.php (M)
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/install.css
- admin/includes/functions.php (M)
- admin/includes/interface-admin.php
- admin/includes/js/install.js (M)
- admin/includes/js/script.js (M)
- admin/includes/run-install.php
- admin/install.php
- admin/menus.php (M)
- admin/posts.php (M)
- admin/settings.php (M)
- admin/setup.php
- admin/terms.php (M)
- admin/users.php (M)
- admin/widgets.php (M)
- includes/class-login.php (M)
- includes/class-post.php (M)
- includes/class-query.php
- includes/config-setup.php (M)
- includes/global-functions.php (M)
- init.php (M)

## Version 1.3.9[b] (2023-09-01)

- Tweaked the readme file
- General code cleanup
- Improving internal documentation
- Renamed a parameter of the `Comment::loadComments` method
- Renamed a parameter of the `Login::isBlacklisted` method
- Added a new file that loads critical functions early on in the CMS initialization
- Rearranged and updated some of the global constants
- Added a new global constant for the new critical functions file
- Streamlined and improved class autoloading to allow for interface support
- Cleaned up the initialization file
- Tweaked the core front end styles
- Added notices if comments are disabled or if logging tracking is disabled
- Added a notice if the site is using a broken theme
- Broken themes can no longer be activated through the dashboard
- Individual posts can now be noindexed
- Added an interface for admin pages (non-functional)
- New functions:
  - `critical-functions.php` (`checkPHPVersion`, `formatPathFragment`, `getClassFilename`)
- Renamed functions and methods:
  - `Login` class (`statusMessage` -> `statusMsg`)

**Bug fixes:**
- Parent dropdown resets to 'none' after saving a page
- The `exitNotice` method wasn't passing the `is_exit` parameter

**Modified files:**
- README.md (M)
- admin/includes/class-comment.php (M)
- admin/includes/class-login.php (M)
- admin/includes/class-media.php (M)
- admin/includes/class-post.php
- admin/includes/class-theme.php
- admin/includes/css/style.css (M)
- admin/includes/functions.php
- admin/includes/interface-admin.php (N)
- admin/index.php (M)
- includes/class-comment.php
- includes/class-login.php
- includes/class-menu.php
- includes/class-query.php
- includes/constants.php
- includes/critical-functions.php (N)
- includes/css/style.css
- includes/debug.php
- includes/functions.php
- includes/global-functions.php
- includes/load-theme.php (M)
- includes/theme-functions.php (M)
- includes/update-db.php
- init.php

## Version 1.3.8[b] (2023-01-27)

- Cleaned up internal documentation throughout the CMS
- Removed some test code from the `Query` class
- Updated a link in the admin footer
- Users can now choose their display name
- Renamed the Media 'author' column to 'uploader'
- Added an introduction paragraph to the top of the admin dashboard page
- The `generateHash` function is now globally accessible
- The `generatePassword` function now uses the `generateHash` function to generate a password
- Removed an optional parameter from the `generatePassword` function
- Status messages are now called notices
  - Added notices functionality to the dashboard page
  - Overhauled notices throughout the entire admin area
- Henceforth, minified versions of CSS and JS files will not be explicitly included in the changelog
- When a user replies to a comment, a note indicating which comment is being replied to now displays above the comment box
- Updated Font Awesome to v6.2.1
- Updated jQuery to v3.6.3
- New functions:
  - Admin `Notice` class (`defaultMsg`, `isDismissed`, `msg`, `unhide`)
  - Admin `Profile` class (`getDisplayNames`)
  - Admin `functions.php` (`ctDraft`, `exitNotice`, `isDismissedNotice`)
  - `theme-functions.php` file (`queryDelete`, `queryInsert`, `querySelect`, `querySelectField`, `querySelectRow`, `queryUpdate`)
- Renamed functions:
  - Admin `functions.php` (`statusMessage` -> `notice`)

**Bug fixes:**
- Undefined errors occur when bulk updating
- Post comment counts match the original post even though the comments are not duplicated (they're now set to zero on the new post)

**Modified files:**
- admin/about.php
- admin/categories.php
- admin/comments.php
- admin/header.php
- admin/includes/ajax.php (N)
- admin/includes/bulk-actions.php
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-notice.php (N)
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php (M)
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/script.js
- admin/includes/load-media.php
- admin/includes/run-install.php
- admin/includes/upload.php (M)
- admin/index.php
- admin/install.php
- admin/logins.php
- admin/media.php
- admin/menus.php
- admin/posts.php
- admin/profile.php
- admin/settings.php
- admin/setup.php
- admin/terms.php
- admin/themes.php
- admin/users.php
- admin/widgets.php
- content/themes/carbon/category.php (M)
- content/themes/carbon/functions.php (M)
- content/themes/carbon/script.js
- content/themes/carbon/style.css (M)
- includes/ajax.php (M)
- includes/class-comment.php
- includes/class-post.php
- includes/class-query.php (M)
- includes/css/font-awesome-rules.min.css
- includes/css/font-awesome.min.css
- includes/fonts/fa-brands.ttf
- includes/fonts/fa-regular.ttf
- includes/fonts/fa-solid.ttf
- includes/fonts/fa-v4compatibility.ttf
- includes/functions.php
- includes/global-functions.php
- includes/js/jquery.min.js
- includes/js/script.js
- includes/theme-functions.php
- includes/update-db.php

## Version 1.3.7.1[b] (2022-12-18)

- Tweaked a previous entry in the changelog
- Removed an unused block of admin CSS
- Optimized some code in the admin `functions.php`
- Set a class variable of the `Query` class back to private (it was changed during testing and was not meant to remain public)

**Modified files:**
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- includes/class-query.php (M)

## Version 1.3.7[b] (2022-12-18)

- Cleaned up internal documentation throughout the CMS
- Posts of any type can now be duplicated
- Most records can now be searched on the dashboard
- The `button` function now supports the `id` field
- Comments can now be sent to spam and spam can be mass deleted, no questions asked
- Renamed a param in the `actionLink` function
- New functions:
  - Admin `Comment` class (`deleteSpamComments`, `spamComment`)
  - Admin `Post` class (`duplicatePost`)
  - Admin `functions.php` (`recordSearch`, `termExists`)

**Bug fixes:**
- Duplicate values are being added to the SQL data in the `Query::select` function, breaking some queries
- Comment counts are not being updated for posts when using bulk update

**Modified files:**
- admin/categories.php
- admin/comments.php
- admin/footer.php (M)
- admin/header.php (M)
- admin/includes/bulk-actions.php
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php (M)
- admin/includes/class-term.php
- admin/includes/class-theme.php (M)
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/includes/js/script.js
- admin/index.php (M)
- admin/logins.php
- admin/media.php
- admin/menus.php
- admin/posts.php
- admin/profile.php (M)
- admin/settings.php
- admin/terms.php
- admin/themes.php
- admin/users.php
- admin/widgets.php
- includes/class-query.php (M)
- includes/fallback-theme.php
- includes/global-functions.php
- init.php (M)

## Version 1.3.6.1[b] (2022-09-09)

- Tweaked a previous entry in the changelog
- Incremented the CMS version

**Modified files:**
- n/a

## Version 1.3.6[b] (2022-09-09)

- Tweaked a previous entry in the changelog
- Tweaked styles in the default front end stylesheet
- Removed a deprecated parameter from the `getThemeStylesheet` function
- The `trailingSlash` function is now deprecated, and all future uses will be replaced with the `slash` function
- Underscores are now replaced with hyphens in file uploads
- Added a new named constant: `THEME_VERSION` (uses the `CMS_VERSION` constant if none is defined; can be overridden in the active theme's `functions.php` file)
- The `headerScripts` function now uses unminified CSS files when the CMS is in debug mode
- Tweaked styling of the fallback theme
- Added a new maintenance mode feature with a basic maintenance page that will only display on the front end for logged out users (the text on the screen cannot be customized as of now)
- New functions:
  - `global-functions.php` (`slash`, `unslash`)

**Bug fixes:**
- The `getUniqueFilename` function loops infinitely if a filename exists in the database
- Double quotes in meta descriptions aren't properly escaped

**Modified files:**
- admin/header.php (M)
- admin/includes/class-media.php
- admin/includes/class-theme.php (M)
- admin/includes/functions.php
- content/themes/carbon/functions.php (M)
- includes/class-post.php (M)
- includes/css/style.css
- includes/css/style.min.css
- includes/fallback-theme.php (M)
- includes/functions.php
- includes/global-functions.php
- includes/load-theme.php (M)
- includes/maintenance.php (N)
- includes/update-db.php (M)
- init.php

## Version 1.3.5[b] (2022-07-07)

- Updated Font Awesome to v6.1.1
- Cleaned up internal documentation throughout the CMS
- Moved all code out of the `update.php` file and into a new file called `update-db.php`
  - The `update.php` file will be used for the upcoming auto-update feature
- Added an optional `rowspan` parameter to the `tableCell`, `thCell`, and `tdCell` functions
- Cleaned up code in the `formTag` function and whitelisted additional HTML tags
- Uploaded files are now stored in year subdirectories of the main `uploads` directory
  - `/content/uploads/<filename>` -> `/content/uploads/<year>/<filename>`
  - All existing media on the site is retroactively updated
- Created a new global constant named `CMS_VERSION` which will eventually replace `VERSION`
- Updated the URL that permanently blacklisted users are redirected to ;)
- New functions:
  - `Query` class (`columnExists`)

**Modified files:**
- admin/about.php (M)
- admin/header.php
- admin/includes/class-media.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php (M)
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- admin/includes/js/install.js
- admin/includes/js/install.min.js (M)
- admin/includes/js/modal.js
- admin/includes/modal-delete.php (M)
- admin/includes/modal-upload.php
- content/themes/carbon/footer.php (M)
- content/themes/carbon/header.php (M)
- content/themes/carbon/post.php
- content/themes/carbon/script.js
- includes/class-comment.php (M)
- includes/class-login.php (M)
- includes/class-query.php
- includes/constants.php (M)
- includes/css/font-awesome-rules.min.css
- includes/css/font-awesome.min.css
- includes/css/style.css (M)
- includes/css/style.min.css (M)
- includes/fonts/fa-brands.ttf
- includes/fonts/fa-regular.ttf
- includes/fonts/fa-solid.ttf
- includes/fonts/fa-v4compatibility.ttf (N)
- includes/functions.php (M)
- includes/global-functions.php (M)
- includes/update-db.php (N)
- includes/update.php
- init.php

## Version 1.3.4.1[b] (2022-05-31)

**Bug fixes:**
- Certain items are not displaying on the admin menu and the admin bar

**Modified files:**
- includes/global-functions.php (M)

## Version 1.3.4[b] (2022-05-31)

- Cleaned up internal documentation throughout the CMS
- Added a new arg to the `registerTaxonomy` function for specifying the post type the taxonomy links to
- Posts can now be filtered by the term they belong to, by clicking on the `count` field on the term page
- Terms now link to their associated archive page in the post data tables
- Added support for default terms (new posts will have this term selected by default)
- Added some error checking for the bulk actions AJAX submission
- View/preview links on the "Edit \<post_type\>" form now opens in a new tab
- Themes with missing `index.php` files are now shown on the "List Themes" page, but a warning is included
- Tweaked and simplified some of the admin form checkbox CSS
- Renamed a CSS class
- Tweaked the structure and styling of the "Create/Edit Menu" forms
- The `actionLink` function now returns an error message if no args are provided
- Added a new optional parameter to the `actionLink` function that allows further query parameters to be specified and placed after the action in the query string
- Users can no longer blacklist themselves via the "Login Attempts" or "Create Login Blacklist" pages
- Logged in users are now logged out if their username is blacklisted via the "Create Login Blacklist" form

**Bug fixes:**
- Bulk edit doesn't work for custom post types

**Modified files:**
- admin/includes/bulk-actions.php
- admin/includes/class-login.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/includes/js/script.js
- content/themes/carbon/functions.php (M)
- includes/constants.php
- includes/global-functions.php

## Version 1.3.3.1[b] (2022-05-26)

- Incremented the CMS version

**Bug fixes:**
- Removed the inclusion of a file that doesn't exist

**Modified files:**
- includes/global-functions.php (M)

## Version 1.3.3[b] (2022-05-26)

- Cleaned up internal documentation throughout the CMS (not every line needs to be documented, so this will be cut back quite a bit from here on)
- The `role` parameter of the `userHasPrivilege` and `userHasPrivileges` functions are now optional (the functions will default to the currently active user's role)
- Cleaned up code in the `Login::validateForgotPasswordData` function
- Added the admin "About" page to the admin bar

**Bug fixes:**
- Custom post types aren't properly loaded on the front end
- If a user tries to view a post of an unrecognized type, the CMS will redirect them to the 404 Not Found page instead of throwing an error (the same goes for terms of unrecognized taxonomies)

**Modified files:**
- 404.php (M)
- admin/categories.php
- admin/comments.php
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/functions.php
- admin/logins.php
- admin/media.php
- admin/menus.php
- admin/posts.php
- admin/settings.php
- admin/terms.php
- admin/themes.php
- admin/users.php
- admin/widgets.php
- includes/ajax.php
- includes/class-comment.php
- includes/class-login.php
- includes/class-menu.php
- includes/class-post.php
- includes/class-query.php
- includes/class-term.php
- includes/debug.php (M)
- includes/functions.php
- includes/global-functions.php
- includes/load-template.php
- includes/load-theme.php
- includes/schema.php (M)
- includes/sitemap-index.php
- includes/sitemap-posts.php
- includes/sitemap-terms.php
- includes/theme-functions.php
- index.php (M)
- init.php
- login.php (M)

## Version 1.3.2[b] (2022-02-15)

- Cleaned up some internal documentation
- Added bulk actions to the "List Users" page
- Added the `IS NULL` and `IS NOT NULL` operators to the list of allowed operators in the `Query::select` function
- Users can now be filtered by online or offline status on the "List Users" page
- Login attempts can now be filtered by success or failure status on the "Login Attempts" page
- Bumped the minimum PHP version to 7.4 and the recommended version to 8.0
- Tweaked the styling of admin dashboard widgets
- Added an admin page that displays information about the CMS
- Updated several custom property names in the Harvest admin theme
- Replaced all old instances of the `tableCell` function with the new `tdCell` function
- Cleaned up code in several functions
- The login page's password toggle button's height should now match the password field's height on mobile
- New functions:
  - Admin `Login` class (`getLoginCount`)
  - Admin `User` class (`updateUserRole`, `getUserCount`, `bulkActions`)
  - Admin `functions.php` (`thCell`, `tdCell`)

**Modified files:**
- admin/about.php (N)
- admin/includes/bulk-actions.php
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-term.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- content/admin-themes/harvest.css (M)
- includes/class-query.php
- includes/css/style.css (M)
- includes/css/style.min.css (M)
- includes/js/script.js

## Version 1.3.1[b] (2022-02-14)

- Fixed a typo in the README file
- The default menu link arg in the `registerPostType` function is now `'posts.php?type='.$name` (previously it was simply `'posts.php'`)
- The default menu link arg in the `registerTaxonomy` function is now `'terms.php?taxonomy='.$name` (previously it was simply `'terms.php'`)
- Categories and terms of custom taxonomies can now be edited via the admin bar
- Created a file that will hold backward compatible functions to support installations running on PHP versions below the recommended version
- Cleaned up some code in the admin `Media` class
- Replaced most instances of the `strpos` function being used to check for the existence of a string in another string with the new `str_contains` and `str_starts_with` functions introduced in PHP 8.0
- Simplified some code in the `Menu` class
- Added an optional parameter to the `sanitize` function that determines whether the text should be converted to lowercase
- Usernames are now sanitized before being submitted to the database
- The admin "information" text no longer displays inline when the icon is clicked
- Text in the admin header, nav menu, and statistics bar graph can no longer be selected with the mouse cursor

**Bug fixes:**
- Sitemaps for custom post types and taxonomies are not being generated

**Modified files:**
- README.md (M)
- admin/includes/class-media.php
- admin/includes/class-post.php (M)
- admin/includes/class-profile.php (M)
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- includes/backward-compat.php (N)
- includes/class-login.php (M)
- includes/class-menu.php (M)
- includes/class-post.php (M)
- includes/class-query.php (M)
- includes/class-term.php (M)
- includes/functions.php
- includes/global-functions.php
- includes/sitemap-index.php (M)
- init.php

## Version 1.3.0[b] (2022-02-10)
*Feature Update: QoL Improvements*

- Tweaked the README file
- Tweaked a previous entry in the changelog
- When a media item is replaced, the modified date is no longer set to `null` if the filename and date are updated
- The `Comment::approveComment` and `Comment::unapproveComment` now use the new `updateCommentStatus` function for all of the heavy lifting
- Cleaned up documentation in several admin files
- Cleaned up code in the `Settings` and `User` classes
- Added the caching param to the site logo and site icon on the "Design Settings" page
- Added type declarations to various functions
- Renamed the `getAdminScript`, `getAdminStylesheet`, and `getAdminTheme` functions and removed their option to echo *or* return (now they exclusively echo their content)
- Reorganized the admin `functions.php` file so that similar functions are grouped together
- Added an optional parameter to the `sanitize` function that allows custom regex to be specified
- Improved the sanitization of slugs in the `Media::validateData`, `Menu::validateData`, `Post::validateData`, `Term::validateData`, and `Widget::validateData` functions
- Renamed `globals.php` to `global-functions.php`
- Created several new named constants for commonly included files
- Restructured the content of the `setup.php` file
- Moved the `load-media.php` and `upload.php` files to the admin `includes` directory
  - Both files now make use of the `BASE_INIT` named constant
- Reorganized the `functions.php` and `global-functions.php` files so that similar functions are grouped together
- Renamed `fallback.php` to `fallback-theme.php`
- The fallback theme file now elaborates further on creating a new theme directory
- Reordered the file loading sequence in `init.php` so that the page content can be shown on the fallback theme
- Added new term-related functions for theme creators
- Tweaked code in the `getPermalink` function
- The `Comment::getCommentStatus` now actually returns a value
- New functions:
  - `functions.php` (`putThemeScript`, `putThemeStylesheet`)
  - `global-functions.php` (`putScript`, `putStylesheet`)
  - `theme-functions.php` (`getTermTaxName`, `putTermTaxName`, `putTermPosts`)
- Renamed functions:
  - `theme-functions.php` (`getPostsWithTerm` -> `getTermPosts`)
  - Admin `functions.php` (`getAdminScript` -> `adminScript`, `getAdminStylesheet` -> `adminStylesheet`, `getAdminTheme` -> `adminThemeStylesheet`)

**Bug fixes:**
- There are two undefined index notices on the "Login Rules" admin page caused by an unnecessary code snippet
- The front end comment feed displays undefined errors when the feed is updated
- An undefined error is triggered if a comment is submitted to an empty comment feed
- A JavaScript error occurs if a user tries to click on an anchor link to a comment that has been deleted
- A variable in the admin `Menu::isNextSibling` tries to access a non-existent array index when a menu item is moved down
- The "Login Blacklist" admin page displays an empty table if the only existing blacklist expires as the page is loaded

**Modified files:**
- 404.php (M)
- README.md (M)
- admin/header.php
- admin/includes/bulk-actions.php (M)
- admin/includes/class-category.php
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/functions.php
- admin/includes/js/modal.js (M)
- admin/includes/js/script.js (M)
- admin/includes/load-media.php
- admin/includes/modal-delete.php (M)
- admin/includes/modal-upload.php (M)
- admin/includes/run-install.php
- admin/includes/upload.php
- admin/index.php (M)
- admin/install.php
- admin/setup.php
- content/themes/carbon/functions.php
- content/themes/carbon/header.php (M)
- content/themes/carbon/script.js (M)
- content/themes/carbon/taxonomy.php (M)
- includes/ajax.php (M)
- includes/class-comment.php
- includes/class-login.php
- includes/class-menu.php
- includes/class-post.php
- includes/class-query.php
- includes/class-term.php
- includes/constants.php
- includes/debug.php (M)
- includes/deprecated.php (M)
- includes/fallback-theme.php
- includes/functions.php
- includes/global-functions.php
- includes/js/script.js (M)
- includes/load-template.php (M)
- includes/load-theme.php (M)
- includes/schema.php (M)
- includes/theme-functions.php
- init.php
- login.php

## Version 1.2.9[b] (2022-02-04)

- Added bulk actions to the "List \<post_type>" page
  - Post statuses can be changed between `published`, `draft`, and `trash`
- Added a `modified` class variable to the `Post` class
- Media links can now be created in the admin dashboard
- Added a caching param to all images so they refresh properly if the image is replaced
- Improved code readability in several files
- Post excerpts can now be dynamically created from post content (the default length is 25 words)
- Added bulk actions to the `Widget` class
  - Widget statuses can be changed between `active` and `inactive`
- Cleaned up code in the `Comment` class
- Improved type checking in the `Post` class
- The `Post::trashPost` and `Post::restorePost` now use the new `updatePostStatus` function for all of the heavy lifting
- When a new post (of any type) is first submitted to the database, its modified date is now set
- If a published post is set to a draft, its publish date is now set to `null`
- The publish date and modified date values will be dynamically updated upon installation of this update (making a database backup is highly recommended!)
- Improved the logic of the "Replace Media" functionality
  - A media item's filename is no longer updated when it's replaced and the 'update filename and date' checkbox is left unchecked (unless the file type changes)
- New functions:
  - Admin `Comment` class (`updateCommentStatus`)
  - Admin `Post` class (`updatePostStatus`, `bulkActions`)
  - Admin `Widget` class (`updateWidgetStatus`, `bulkActions`)
  - Admin `functions.php` (`mediaLink`)
  - `theme-functions.php` (`getPostExcerpt`, `putPostExcerpt`)

**Bug fixes:**
- There are two undefined errors in the `Media::replaceMedia` function when the "Replace Media" page is viewed

**Modified files:**
- admin/includes/bulk-actions.php
- admin/includes/class-comment.php
- admin/includes/class-media.php
- admin/includes/class-menu.php (M)
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- admin/posts.php
- includes/ajax.php (M)
- includes/globals.php
- includes/theme-functions.php
- includes/update.php

## Version 1.2.8.1[b] (2022-02-02)

- Incremented the version from 1.2.7 to 1.2.8

**Modified files:**
- n/a

## Version 1.2.8[b] (2022-02-02)

- Significantly revised changelog formatting
  - Functions are now listed at the bottom of the updates section (not complete)
  - Bug fixes are now listed in their own section of each update, above the list of modified files
- Fixed erroneous version numbers in several pieces of internal documentation
- Improved code readability in several files
- Created a file to hold functions specifically used for theme-building (custom functions made by theme creators will still be located in the theme's directory)
- Cleaned up code in the `Post` class
- The `Post::getPostDate` function now uses the post's modified date in the event that the post is still a draft
- The `Post::getPostUrl` function now checks whether the currently viewed page is the home page and returns the home URL if so
- Moved the following existing functions to the `theme-functions.php` file:
  - `templateExists`
  - `getHeader`
  - `getFooter`
  - `getPostsWithTerm`
  - `pageTitle`
  - `metaTags`
- The `getCategory` function has been converted into an alias for the `getTerm` function
- The front end `Category` class has been removed, as it is no longer necessary
- The `Comment` class' bulk actions are now hidden if there are no comments in the database
- New functions:
  - `theme-functions.php` (`isPost`, `getPostId`, `putPostId`, `getPostTitle`, `putPostTitle`, `getPostAuthor`, `putPostAuthor`, `getPostDate`, `putPostDate`, `getPostModDate`, `putPostModDate`, `getPostContent`, `putPostContent`, `getPostStatus`, `putPostStatus`, `getPostSlug`, `putPostSlug`, `getPostParent`, `putPostParent`, `getPostType`, `putPostType`, `getPostFeaturedImage`, `putPostFeaturedImage`, `getPostMeta`, `putPostMeta`, `getPostTerms`, `putPostTerms`, `getPostComments`, `getPostUrl`, `putPostUrl`, `postHasFeaturedImage`, `isTerm`, `getTermId`, `putTermId`, `getTermName`, `putTermName`, `getTermSlug`, `putTermSlug`, `getTermTaxonomy`, `putTermTaxonomy`, `getTermParent`, `putTermParent`, `getTermUrl`, `putTermUrl`, `getCategoryId`, `putCategoryId`, `getCategoryName`, `putCategoryName`, `getCategorySlug`, `putCategorySlug`, `getCategoryParent`, `putCategoryParent`, `getCategoryUrl`, `putCategoryUrl`)
- Renamed functions:
  - `Post` class (`getPostFeatImage` -> `getPostFeaturedImage`, `postHasFeatImage` -> `postHasFeaturedImage`)

**Modified files:**
- admin/includes/class-comment.php (M)
- content/themes/carbon/category.php (M)
- content/themes/carbon/index.php
- content/themes/carbon/post.php
- content/themes/carbon/taxonomy.php (M)
- includes/ajax.php (M)
- includes/class-category.php (X)
- includes/class-comment.php
- includes/class-login.php
- includes/class-menu.php
- includes/class-post.php
- includes/class-query.php
- includes/class-term.php
- includes/deprecated.php
- includes/functions.php
- includes/globals.php
- includes/load-template.php
- includes/sitemap-posts.php
- includes/sitemap-terms.php (M)
- includes/theme-functions.php (N)
- includes/update.php
- init.php

## Version 1.2.7[b] (2021-11-27)

- Tweaked the `install.css` styles
- Optimized some code in the admin `Post` class
- Improved code readability in several files
- Tweaked the styling of admin data tables
- Added headings to the bottom of data tables
- Created an alias for the `formTag` function
- Created a named constant for the CMS' name
  - Replaced all instances of 'ReallySimpleCMS' throughout with the named constant
- Tweaked the message that displays in the fallback theme (this only displays if no themes are installed)
- Added some styling to the fallback theme page
- The current theme is now included in the `bodyClasses` function's output
- Buttons can now be created dynamically
- Comments can now be approved/unapproved in bulk (bulk delete is not enabled yet)
- Tweaked the Carbon theme's `script.js` code
- New functions:
  - Admin `Comment` class (`bulkActions`)
  - Admin `functions.php` (`tag`)
  - `globals.php` (`button`)

**Modified files:**
- admin/header.php (M)
- admin/includes/bulk-actions.php (N)
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/install.css (M)
- admin/includes/css/install.min.css (M)
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/includes/js/script.js
- admin/install.php
- admin/setup.php
- content/themes/carbon/script.js (M)
- includes/class-login.php
- includes/constants.php
- includes/css/style.css
- includes/css/style.min.css
- includes/fallback.php
- includes/functions.php
- includes/globals.php
- index.php (M)
- init.php (M)

## Version 1.2.6[b] (2021-10-24)

- Tweaked a previous entry in the changelog
- Completely overhauled the database installation code
  - Added AJAX submission to the form, allowing for more dynamic database setup
  - Code cleanup in the `admin/install.php` file (all validation has been moved to `admin/includes/run-install.php`)
- Tweaked the Light admin theme
- Tweaked the Beta Snapshots changelog formatting
- Code cleanup in several admin files
- New functions:
  - Admin `run-install.php` (`runInstall`)

**Modified files:**
- admin/header.php (M)
- admin/includes/css/install.css
- admin/includes/css/install.min.css
- admin/includes/js/install.js (N)
- admin/includes/js/install.min.js (N)
- admin/includes/run-install.php (N)
- admin/install.php
- admin/posts.php (M)
- admin/setup.php (M)
- admin/terms.php (M)
- content/admin-themes/light.css (M)

## Version 1.2.5[b] (2021-10-13)

- Tweaked the Beta changelog formatting
- Updated the copyright year in the README file
- Updated Font Awesome to v5.15.4
- Updated jQuery to v3.6.0
- Created named constants for the jQuery and Font Awesome versions
- Renamed the `PHP` named constant to `PHP_MINIMUM` and created a new constant called `PHP_RECOMMENDED`, which will be the version recommended for administrators to run their servers on
- Set the recommended PHP version to `7.4`
- Tweaked styling for admin header notices
- Added an admin header notice for PHP versions below the recommended version
- Tweaked styling of the admin themes page
- Added a message that displays if a theme does not have a preview image

**Modified files:**
- README.md (M)
- admin/header.php
- admin/includes/class-theme.php (M)
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php (M)
- content/admin-themes/forest.css (M)
- content/admin-themes/harvest.css (M)
- content/admin-themes/light.css (M)
- content/admin-themes/ocean.css (M)
- content/admin-themes/sunset.css (M)
- includes/constants.php
- includes/css/font-awesome.min.css
- includes/fonts/fa-brands.ttf
- includes/fonts/fa-regular.ttf
- includes/fonts/fa-solid.ttf
- includes/functions.php (M)
- includes/js/jquery.min.js
- init.php (M)

## Version 1.2.4.1[b] (2021-10-12)

- Incremented the version from 1.2.3 to 1.2.4
- Updated the minified stylesheet for the admin dashboard

**Modified files:**
- admin/includes/css/style.min.css
- admin/includes/functions.php (M)

## Version 1.2.4[b] (2021-04-26)

- Improved the way the `init.php` file checks the current PHP version
- Updated Font Awesome to v5.15.3
- Added custom properties to the admin `style.css` file
- Tweaked styles in the `install.css` file
- Added custom properties to the admin themes CSS files and significantly reduced their file sizes
- Added a new light admin theme (some colors may not be finalized)

**Modified files:**
- admin/includes/css/install.css (M)
- admin/includes/css/style.css
- admin/includes/functions.php (M)
- content/admin-themes/forest.css
- content/admin-themes/harvest.css
- content/admin-themes/light.css (N)
- content/admin-themes/ocean.css
- content/admin-themes/sunset.css
- includes/class-login.php (M)
- includes/css/font-awesome.min.css
- includes/fonts/fa-brands.ttf
- includes/fonts/fa-regular.ttf
- includes/fonts/fa-solid.ttf
- includes/functions.php (M)
- init.php

## Version 1.2.3[b] (2021-02-01)

- Added a "Replace Media" button to the "Edit Media" form
- Cleaned up code in the `Media` class
- Media can now be replaced
- Added a default error message that will display for media that can't be deleted (this should only occur in catastrophic circumstances)
- Media entries in the database can now be deleted even if the associated file can't be found in the `uploads` directory
- The newest posts and terms are now ordered first in the menu items sidebar
- Optimized code in the `User` and `Profile` classes for the `pass_saved` checkboxes
- New functions:
  - Admin `Media` class (`replaceMedia`)
- Renamed functions:
  - Admin `Menu` class (`getMenuItemsLists` -> `getMenuItemsSidebar`)

**Bug fixes:**
- Two hyphens can be placed side by side in media filenames in some instances

**Modified files:**
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-profile.php (M)
- admin/includes/class-user.php
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/media.php

## Version 1.2.2[b] (2021-01-31)

- Added a box shadow to the admin widgets
- Comment feeds now only load the ten most recent comments by default
- Added a button to load more comments (loads ten at a time)
- Comment feeds now remember how many comments are loaded when they refresh
- Tweaked documentation in the front end `script.js` file
- Cleaned up code in the `ajax.php` file
- Tweaked front end styling
- New functions:
  - `Comment` class (`loadComments`)

**Modified files:**
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- content/themes/carbon/style.css
- includes/ajax.php
- includes/class-comment.php
- includes/css/style.css (M)
- includes/css/style.min.css (M)
- includes/functions.php (M)
- includes/js/script.js

## Version 1.2.1[b] (2021-01-20)

- The active theme is now listed first on the "List Themes" page
- Created widgets for the admin dashboard
  - Added three dashboard widgets: "Comments", "Users", and "Logins", which display information about each
- Tweaked styles for all admin themes
- The `SHOW INDEXES` command can now be executed using the `Query` class
- The `update.php` file now checks whether the `comments` table is missing one or more of its indexes before trying to reinstall it
- The `in_array` function now uses strict mode in all occurrences
- Removed an unnecessary comment from the admin `functions.php` file
- Added the `themes` directory to the `.gitignore` file, excluding the Carbon theme
- Cleaned up the `.gitignore` file
- New functions:
  - Admin `functions.php` (`dashboardWidget`)
  - `Query` class (`showIndexes`)

**Bug fixes:**
- The comment feed update script runs on every page (it now only runs if the page contains a comment feed)

**Modified files:**
- .gitignore
- admin/includes/class-post.php (M)
- admin/includes/class-theme.php
- admin/includes/class-user-role.php (M)
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/index.php
- content/admin-themes/forest.css
- content/admin-themes/harvest.css
- content/admin-themes/ocean.css
- content/admin-themes/sunset.css
- includes/class-query.php
- includes/js/script.js
- includes/update.php

## Version 1.2.0[b] (2021-01-16)
*Feature Update: Login Tracking*

### Dedicated to my grandmother, "Nam" (1940 - 2021)

- For a full list of changes, see: `changelog-beta-snapshots.md`
- Added the `actionLink` function to numerous admin classes
- Added the `ADMIN_URI` constant to numerous admin classes
- Tweaked documentation in numerous admin classes
- All primary admin pages now have an information icon that displays information about the page describing its purpose when clicked
- Added indexes to the `comments` table's schema (backwards compatible)
- Added a "select all" checkbox to the "Create User Roles" and "Edit User Roles" forms
- Database tables can now be dropped using the `Query` class
  - Replaced all instances of the `DROP TABLE` statement with the new functions
- New functions:
  - Admin `functions.php` (`adminInfo`)
  - `Query` class (`dropTable`, `dropTables`)

**Bug fixes:**
- The themes admin menu link doesn't work properly

**Modified files:**
- admin/includes/class-category.php (M)
- admin/includes/class-comment.php
- admin/includes/class-login.php (M)
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php (M)
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/includes/js/script.js
- includes/class-query.php
- includes/globals.php
- includes/schema.php (M)
- includes/update.php

## Version 1.1.7[b] (2020-12-03)

- Canonical links for the home page no longer include the page's slug and point to the actual home URL
- Added a default error message for the `Query::errorMsg` function
- Renamed two settings in the database to better clarify their usage (existing databases will have these settings updated automatically):
  - `comment_status` -> `enable_comments`
  - `comment_approval` -> `auto_approve_comments`
- Added a new setting named `allow_anon_comments`, which allows anonymous users (users without accounts) to comment on posts
- Updated all instances of the `comment_status` and `comment_approval` settings being used throughout the CMS to their new names
- The `getCommentReplyBox` and `getCommentFeed` functions now check whether anonymous users can comment
- Added a missing CSS class to the "Enable comments" checkbox's label on the admin post forms
- The front end `Comment` class' `createComment` and `deleteComment` functions now update the comment count for the post their comment is attached to
- The notice received when posting a new comment on the front end now says "Your comment was submitted for review!" if the `auto_approve_comments` setting is turned off
- Added the `post` variable back to the admin `Comment` class
- The admin `Comment` class' `approveComment`, `unapproveComment`, and `deleteComment` functions now update the comment count for the post their comment is attached to
- On the "List Comments" page, unnapproved comments now have a note saying "pending approval" after the comment's text
- Changed the "List \<post_type>" note separator appearing after unpublished posts from an en dash to an em dash
- Tweaked documentation in the `Post` class
- Renamed the admin `post-status-nav` CSS class to `status-nav`
- Comment counts are now fetched dynamically in the `Comment` class
- Tweaked code in the `Post::listPosts` function
- Added a status nav for the "List Comments" page to allow filtering comments by status (e.g., `approved` or `unapproved`)
- The status nav links for the "List \<post_type>" and "List Comments" pages now use the full admin URL
- Reduced the comment feed update timer from 60 seconds to 15 (note: this is how often the feed checks for updates, not how often it actually refreshes)
- The `Settings::getPageList` function now checks whether the home page exists and displays a blank option in the dropdown if it doesn't
- Tweaked various previous entries in the changelogs
- New functions:
  - Admin `Comment` class (`getCommentCount`)

**Modified files:**
- admin/includes/class-comment.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- includes/class-comment.php
- includes/class-query.php (M)
- includes/functions.php
- includes/globals.php
- includes/js/script.js (M)
- includes/update.php

## Version 1.1.6[b] (2020-11-15)

- The `sitemap-index.php` file is now loaded after the theme (this allows for custom post type and taxonomy sitemaps to be generated)
- The `sitemap-posts.php` and `sitemap-terms.php` files are now loaded at the top of the `sitemap-index.php` file
- Sitemaps are now deleted if the post type or taxonomy they are associated with is unregistered
- Tweaked mobile styling on the admin dashboard
  - The "Create \<item>" button is no longer placed below the page title
  - Removed the dashed borders around the copyright and version in the footer
  - Fixed a visual issue with the post data form's content area

**Modified files:**
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- includes/sitemap-index.php
- init.php (M)

## Version 1.1.5[b] (2020-11-14)

- Removed an unnecessary `if` statement from the `ajax.php` file
- Menu items can no longer be linked to unpublished posts
- Menu items that link to external sites now open the link in a new tab
- Sitemaps are now fully styled
- Tweaked documentation in the `sitemap-index.php` file
- The `sitemap-terms.php` file now generates sitemaps in the `root` directory for every public taxonomy
- The `sitemap-index.php` file is now loaded after the default post types and taxonomies are registered
- Added all sitemaps to the `.gitignore` file
- The `sitemap-posts.php` file now generates sitemaps in the `root` directory for every public post type
  - The `media` post type is public, but for the time being, it will be skipped (this may be changed at a later date)
- The `sitemap-index.php` file now regenerates the `sitemap.xml` file if an existing sitemap is deleted or a new one is created

**Bug fixes:**
- Class variables are not updated when an admin "Edit \<item>" form is submitted in various classes

**Modified files:**
- .gitignore (M)
- admin/includes/class-media.php (M)
- admin/includes/class-menu.php (M)
- admin/includes/class-profile.php (M)
- admin/includes/class-term.php (M)
- admin/includes/class-user-role.php (M)
- admin/includes/class-user.php (M)
- admin/includes/class-widget.php (M)
- includes/ajax.php (M)
- includes/class-menu.php
- includes/sitemap-index.php
- includes/sitemap-posts.php
- includes/sitemap-terms.php
- includes/sitemap.xsl (N)
- init.php (M)

## Version 1.1.4[b] (2020-11-10)

- Improved security for the `session` and `pw-reset` cookies
- Created a variable for the `Login` class that stores whether HTTPS is enabled
- Created a constructor for the `Login` class
- Moved the `PW_LENGTH` constant to the top of the `Login` class
- Added a new constant, `DEBUG_MODE`, which informs the CMS whether it should display or hide PHP errors (default is false)
  - This constant can be defined in the `config.php` file to override the default value
- Cleaned up some code in the `Query` class
- Moved the `VERSION` constant from the `globals.php` file to the `constants.php` file
- Cleaned up code in the `constants.php` file
- Tweaked documentation in the `globals.php` file
- Moved the `RSCopyright` and `RSVersion` functions from the `globals.php` file to the admin `functions.php` file
- Cleaned up code in the `RSCopyright` and `RSVersion` functions and removed their `echo` parameter
- Added an error message that displays if one of the required database constants is not defined in the `config.php` file
- Tweaked documentation and cleaned up code in the `init.php` file
- The XML headers in the `sitemap-posts.php` and `sitemap-terms.php` files are now displayed via PHP to prevent errors when the `short_open_tag` ini directive is turned on
- Completed the Alpha changelog cleanup

**Bug fixes:**
- The sitemap is appended multiple times to the `robots.txt` file if the `sitemap.xml` file is deleted and recreated

**Modified files:**
- admin/includes/functions.php
- includes/class-login.php
- includes/class-query.php (M)
- includes/constants.php
- includes/globals.php
- includes/sitemap-index.php
- includes/sitemap-posts.php (M)
- includes/sitemap-terms.php (M)
- init.php

## Version 1.1.3[b] (2020-11-07)

- Meta tags can now be dynamically added to the `head` section in themes
- Added canonical tags to the list of meta tags included in the `head` section
- Deleted the Carbon theme's `header-cat.php` and `header-tax.php` files
- The page title can now be dynamically added to the `head` section in themes (applies to both posts and terms)
- Added checks in the Carbon theme's `index.php` file to prevent errors from occurring if the current page is a term (this is only relevant if a taxonomy template doesn't exist)
- Moved a check for the theme `index.php` file from the `load-theme.php` file to the `load-template.php` file
- Category pages fallback to the generic taxonomy template if a category template does not exist
- Cleaned up some entries in the Alpha changelog
- New functions:
  - `functions.php` (`pageTitle`, `metaTags`)

**Bug fixes:**
- Sitemap links in the `sitemap-index.php` file are missing the `includes` directory before the filename

**Modified files:**
- content/themes/carbon/category.php (M)
- content/themes/carbon/header-cat.php (X)
- content/themes/carbon/header-tax.php (X)
- content/themes/carbon/header.php
- content/themes/carbon/index.php
- content/themes/carbon/taxonomy.php (M)
- includes/functions.php
- includes/load-template.php
- includes/load-theme.php
- includes/sitemap-index.php (M)

## Version 1.1.2[b] (2020-11-04)

- Added validation in the `init.php` file that checks whether the `BASE_INIT` constant has been defined (if so, it only loads the basic initialization files, otherwise it loads everything)
- Cleaned up code in the `ajax.php` file
- Cleaned up some entries in the Alpha changelog
- Created sitemaps for posts and terms
- Created a file that generates a sitemap index
- Tweaked validation in the `init.php` file
- Added `sitemap.xml` to the `.gitignore` file
- Tweaked documentation in the `install.php` and `setup.php` files
- Added additional validation for the `robots.txt` file to the `Settings::validateSettingsData` function
- Tweaked the coloring of comment upvote and downvote buttons

**Bug fixes:**
- The `robots.txt` file isn't updated when the `do_robots` setting is updated

**Modified files:**
- .gitignore (M)
- admin/includes/class-settings.php
- admin/install.php (M)
- admin/setup.php (M)
- content/themes/carbon/style.css (M)
- includes/ajax.php
- includes/class-comment.php (M)
- includes/sitemap-index.php (N)
- includes/sitemap-posts.php (N)
- includes/sitemap-terms.php (N)
- init.php

## Version 1.1.1[b] (2020-10-24)

- Fixed the mobile sizing of media thumbnails on the "Edit Media" page
- Updated Font Awesome to v5.15.1
- Cleaned up some entries in the Alpha changelog
- Created constructors for the `Media`, `Menu`, `User`, and `Widget` classes
- Changed the access for the `Post` class variables from `private` to `protected`
- The `Widget` class now makes use of class variables (inherited from `Post`)
- Created class variables for the `User` class
- The `Profile` class now makes use of class variables (inherited from `User`)
- Code cleanup in the `Media`, `Menu`, `Profile`, and `User` classes
- The `Comment`, `Menu`, `Post`, `Term`, `User`, and `Widget` class constructors now only fetch specific columns from the database
- The `Menu` class now makes use of class variables (inherited from `Post`)
- Removed the `Term::count` variable, as it was unused
- Removed the `Post::modified` variable, as it was unused
- Removed the `Comment::post`, `Comment::author`, `Comment::date`, and `Comment::parent` variables, as they were unused
- Moved all user role functions from the `Settings` class to a new `UserRole` class and created class variables for it
- Comment upvotes and downvotes are now grey when they are inactive and colored when they are active

**Bug fixes:**
- Any database field containing the string `count` returns an error in the `Query` class
- Classes with multi-worded names cause an error and don't autoload

**Modified files:**
- admin/includes/class-comment.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-term.php
- admin/includes/class-user-role.php (N)
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- admin/media.php
- admin/menus.php
- admin/profile.php
- admin/settings.php
- admin/users.php
- admin/widgets.php
- content/themes/carbon/style.css
- includes/class-comment.php (M)
- includes/class-query.php (M)
- includes/css/font-awesome.min.css
- includes/fonts/fa-brands.ttf
- includes/fonts/fa-regular.ttf
- includes/fonts/fa-solid.ttf
- includes/functions.php
- includes/js/script.js

## Version 1.1.0[b] (2020-10-22)
*Feature Update: Comments*

- For a full list of changes, see: `changelog-beta-snapshots.md`
- Optimized and improved the action links functionality for all of the "List \<item>" pages
- Users who don't have the `can_edit_comments` or `can_delete_comments` privileges can no longer see the "Approve/Unapprove", "Edit", or "Delete" action links
- Users who don't have the `can_edit_media` or `can_delete_media` privileges can no longer see the "Edit" or "Delete" action links
- Users who don't have the `can_edit_menus` or `can_delete_menus` privileges can no longer see the "Edit" or "Delete" action links
- Tweaked a previous entry in the Beta snapshots changelog
- Users who don't have the `can_edit_<post_type>` or `can_delete_<post_type>` privileges can no longer see the "Edit" or "Delete" action links
- Users who don't have the `can_edit_user_roles` or `can_delete_user_roles` privileges can no longer see the "Edit" or "Delete" action links
- Users who don't have the `can_edit_<taxonomy>` or `can_delete_<taxonomy>` privileges can no longer see the "Edit" or "Delete" action links
- Users who don't have the `can_edit_themes` or `can_delete_themes` privileges can no longer see the "Activate" or "Delete" action links
- Users who don't have the `can_edit_widgets` or `can_delete_widgets` privileges can no longer see the "Edit" or "Delete" action links
- Removed the `post_type` argument from the `registerTaxonomy` function as it was never used
- Cleaned up some entries in the Alpha changelog
- Comments are no longer covered by the Carbon theme's header when a link pointing to them is clicked
- Whitelisted the `class` property for `img` tags created with the `formTag` function
- Media thumbnails on the "Edit Media" page now have a max width of 250 pixels
- Tweaked previous entries in the changelog

**Modified files:**
- admin/includes/class-comment.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-widget.php
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php (M)
- content/themes/carbon/script.js
- includes/globals.php (M)

## Version 1.0.9[b] (2020-09-10)

- The current post's `type` is now added to the `body` tag as a CSS class
- Replaced `section` tags with `div` tags in several Carbon theme files
- The `id` parameter in the `Post::slugExists` function is now optional (default value is `0`)
- Unique slugs can now be created dynamically
- Improved the logic in the `getUniqueFilename` function
- The media upload system now checks whether the media's slug is unique before uploading it
- Deprecated the `filenameExists` function (merged its functionality with the `getUniqueFilename` function)
- Users will no longer see an error message if the chosen slug is not unique (instead, the CMS will append a number at the end of the slug to make it unique)
- Created `getUniquePostSlug` and `getUniqueTermSlug` alias functions
- The menu item link dropdowns now only include posts and terms of the same post type or taxonomy as their menu item
- Added multiple CSS classes to the `body` tag on term pages (e.g., `class="<slug> <taxonomy> <taxonomy>-id-<id>"`)
- Cleaned up some entries in the Alpha changelog
- New functions:
  - Admin `functions.php` (`getUniqueSlug`, `getUniquePostSlug`, `getUniqueTermSlug`)
- Deprecated functions:
  - Admin `Media` class (`filenameExists`)

**Bug fixes:**
- The blank user avatar on the admin bar displays incorrectly
- The "Insert Media" button populates both the content and meta description fields
- The author's id is sometimes passed as a string by the `Post::getAuthorList` function
- Post objects initialized with a slug redirect to the 404 not found page if the post is not published (this resolves issues with redirection when using the `getPost` function to pull data on posts that are drafts)

**Modified files:**
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-term.php (M)
- admin/includes/class-widget.php (M)
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- admin/includes/js/modal.js
- content/themes/carbon/category.php (M)
- content/themes/carbon/index.php (M)
- content/themes/carbon/post.php (M)
- content/themes/carbon/taxonomy.php (M)
- includes/class-post.php (M)
- includes/css/style.css (M)
- includes/css/style.min.css (M)
- includes/deprecated.php
- includes/functions.php

## Version 1.0.8[b] (2020-08-11)

- The `Query::showTables` function now has an optional `table` parameter
- The existence of a specific database table can now be checked using the `Query` class
- Essential database tables are now recreated individually if they are accidentally deleted instead of prompting the user to reinstall the entire database
- If one or more tables are missing from the database and `admin/install.php` is accessed, the whole database is not reinstalled (only the missing tables are reinstalled)
- The `user_roles`, `user_privileges`, and `user_relationships` tables are now dynamically populated during installation
- Privileges are now created for comments
- Undeprecated various database population functions
- Moved the `getUserRoleId` and `getUserPrivilegeId` functions to the `globals.php` file
- Missing core database tables are now dynamically recreated
- Cleaned up some entries in the Alpha changelog
- New functions:
  - `Query` class (`tableExists`)
  - `globals.php` (`populateTable`, `populateUserRoles`, `populateUserPrivileges`)
- Undeprecated functions:
  - `globals.php` (`populatePosts`, `populateUsers`, `populateSettings`, `populateTaxonomies`, `populateTerms`)

**Modified files:**
- admin/includes/functions.php
- admin/install.php
- includes/class-query.php
- includes/deprecated.php
- includes/functions.php
- includes/globals.php
- init.php

## Version 1.0.7[b] (2020-07-30)

- Tweaked a previous entry in the changelog
- The `parent` parameter of the `getPermalink` function is now optional (default value is 0)
- The permalink base is now added to permalinks on the "Create \<post_type>" forms (this only affects custom post types)
- Cleaned up some entries in the Alpha changelog
- Minor text change in the Carbon theme's `term.php` file
- Post types and taxonomies can no longer be registered multiple times (this allowed for default post types and taxonomies to be overrided in themes)
- Post types can no longer be registered with the same name as an existing taxonomy and vice versa
- Removed a block of code in the `registerPostType` function that caused the `category` taxonomy to function as a fallback if the post type had an invalid taxonomy registered
- Renamed the Carbon theme's `term.php` file to `taxonomy.php` and `header-term.php` file to `header-tax.php`
  - The new format for custom taxonomy theme template files is `taxonomy-<taxonomy>.php`
- Added support for custom post type theme template files (format: `posttype-<type>.php`)

**Bug fixes:**
- Errors are generated by the `adminNavMenu` and `adminBar` functions if a post type has a nonexistent taxonomy registered to it
- Errors occur on the "List \<post_type>s", "Create \<post_type>", and "Edit \<post_type>" forms if the post type is non-hierarchical and has an invalid taxonomy

**Modified files:**
- admin/includes/class-post.php
- admin/includes/functions.php
- content/themes/carbon/header-tax.php (R)
- content/themes/carbon/taxonomy.php (R,M)
- includes/functions.php
- includes/globals.php
- includes/load-template.php

## Version 1.0.6[b] (2020-07-25)

- Improved how permalinks are structured for custom post types and taxonomies (this fixed an issue with all taxonomies having `term` as their base url)
- Tweaked a previous entry in the changelog
- Created a new class variable in the `Post` class to hold taxonomy data
- Custom taxonomies are now properly linked with their respective post types
- The 'List Posts' page now properly shows the taxonomy related to the post type (and omits it if the post type doesn't have a taxonomy)
- Removed taxonomy labels from the `getPostTypeLabels` function
- Moved most of the root `index.php` file's contents to the `init.php` file
- Improved the way the CMS determines whether the current page is a post or a term
- Added functions that check what part of the CMS the user is currently viewing
- A `Term` object can now be dynamically created by supplying a slug
- Changed the way the `load-template.php` file tries to load taxonomy templates
- Added support for custom taxonomy templates
- Added a message to the `getRecentPosts` function if there are no posts that can be displayed
- The `getRecentPosts` function can now be used to load posts associated with any taxonomy and of any post type
- Taxonomies are now displayed on the admin statistics bar graph if they have `show_in_stats_graph` set to true
- Custom taxonomies will now display in nav menus if `show_in_nav_menus` is set to true
- New functions:
  - `functions.php` (`getTerm`)
  - `globals.php` (`isAdmin`, `isLogin`, `is404`)
- Renamed functions:
  - Admin `Post` class (`getCategories` -> `getTerms`, `getCategoriesList` -> `getTermsList`)
  - `Post` class (`getPostCategories` -> `getPostTerms`)
  - `functions.php` (`getPostsInCategory` -> `getPostsWithTerm`)
- Deprecated functions:
  - `functions.php` (`isCategory`)

**Bug fixes:**
- Blank post entries are added to the posts array by the `getPostsInCategory` function if the posts are not published
- Using custom taxonomies as menu items causes numerous issues
- Menu items that point to nonexistent posts or terms cause an error

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-term.php (M)
- admin/includes/functions.php
- content/themes/carbon/functions.php
- content/themes/carbon/header-term.php (N)
- content/themes/carbon/post.php
- content/themes/carbon/term.php (N)
- includes/class-menu.php
- includes/class-post.php
- includes/deprecated.php
- includes/functions.php
- includes/globals.php
- includes/load-template.php
- index.php
- init.php

## Version 1.0.5[b] (2020-07-23)

- Added the `can_upload_media` permission to the admin nav menu
- Post types can now be unregistered (this only applies to custom post types)
- User role and privilege ids can now be fetched
- Admin menu item labels are now properly filtered to remove underscores
- Tweaked how default post type and taxonomy labels are displayed in various locations
- Underscores are now replaced with hyphens in post type and taxonomy base urls
- Cleaned up the `getTaxonomyId` function
- A post can now be checked for existence in the database
- Post types can now be checked for existence in the database
- Taxonomies can now be unregistered (this only applies to custom taxonomies)
- Created class variables for the `Term` class
- Terms can now be viewed, created, edited, and deleted
- Moved all functions from the admin `Category` class to the `Term` class (only the `listCategories`, `createCategory`, `editCategory`, and `deleteCategory` functions remain as alias functions)
  - The `Category` class now inherits from the `Term` class
  - A term's taxonomy can now be fetched from the database
- Code cleanup in the `Post` class
- Code cleanup in the `globals.php` file
- The admin nav menu now scrolls if its content overflows the window
- Added an inner content wrapper to all admin pages to fix a floating issue with page content presented by the overflow fix
- Current page functionality now works properly for custom post types and taxonomies
- Tweaked the admin themes
- New functions:
  - Admin `Term` class (`listTerms`, `createTerm`, `editTerm`, `deleteTerm`, `getTaxonomy`)
  - Admin `functions.php` (`postExists`)
  - `functions.php` (`postTypeExists`, `taxonomyExists`)
  - `globals.php` (`getUserRoleId`, `getUserPrivilegeId`, `unregisterPostType`, `unregisterTaxonomy`)

**Bug fixes:**
- When previewing a post or page, the content is not loaded due to an issue with permalink redirection
- Non-hierarchical post types (aside from type `post`) can't be submitted to the database
- The widths of newly uploaded images are not calculated properly (image dimensions are now fetched via PHP and not JS)

**Modified files:**
- admin/categories.php
- admin/footer.php (M)
- admin/header.php (M)
- admin/includes/class-category.php
- admin/includes/class-post.php
- admin/includes/class-term.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/index.php (M)
- admin/media.php (M)
- admin/menus.php (M)
- admin/posts.php
- admin/profile.php (M)
- admin/settings.php (M)
- admin/terms.php (M)
- admin/themes.php (M)
- admin/users.php (M)
- admin/widgets.php (M)
- content/admin-themes/forest.css (M)
- content/admin-themes/harvest.css (M)
- content/admin-themes/ocean.css (M)
- content/admin-themes/sunset.css (M)
- includes/class-post.php (M)
- includes/functions.php
- includes/globals.php

## Version 1.0.4[b] (2020-07-12)

- Tweaked the max width of select inputs in data form sidebars
- Permalinks no longer redirect to the 404 not found page if they contain query parameters
- Cleaned up some entries in the Alpha changelog
- Cleaned up code in the admin `posts.php` file
- Created a global array to hold the registered taxonomies
- Moved the `registerTaxonomy` function to the `globals.php` file
- Default taxonomies (`category`, `nav_menu`) are now registered in the `globals.php` file
- Added a new argument to the `registerPostType` function: `create_privileges` (will create new privileges in the database for the post type if true)
- The `registerTaxonomy` function can now accept arguments
- Custom taxonomies now have proper links on the admin nav menu
- Cleaned up code in the `adminNavMenu` function
- Cleaned up code in the `adminBar` function
- Custom taxonomies now have proper links on the admin bar
- Added a link to the "Create Theme" page to the admin bar
- Improved privilege checking for items on the admin nav menu
- Added privilege checking for items on the admin bar
- Improved privilege checking for the admin "List \<item>" pages
- The `getPrivileges` function now orders privileges by their ids
- New functions:
  - `globals.php` (`getTaxonomyLabels`, `registerDefaultTaxonomies`)

**Bug fixes:**
- An empty submenu item breaks out of the loop in the `adminNavMenuItem` function, causing subsequent submenu items not to display

**Modified files:**
- admin/includes/class-category.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-term.php (N)
- admin/includes/class-theme.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- admin/posts.php
- admin/terms.php (N)
- includes/class-post.php
- includes/class-term.php
- includes/functions.php
- includes/globals.php
- init.php (M)

## Version 1.0.3[b] (2020-07-04)

- Tweaked previous entries in the changelog
- Whitelisted the `style` attribute for divs and spans in the `formTag` function
- The remove icon now moves based on the avatar's width on the "Create User", "Edit User", and "Edit Profile" pages
- The remove icon now moves based on the site logo and site icon's width on the "Design Settings" page
- The remove icon now moves based on the featured image's width on the "Create Post" and "Edit Post" pages
- Cleaned up some entries in the Alpha changelog
- Added the `public` argument to the `registerPostType` function (if set to true, post type will display in menus, the admin bar, etc. and if set to false it will not)
- Custom posts will now display in the "Create Menu" and "Edit Menu" pages if `show_in_nav_menus` is set to true
- Menu item permalinks are now properly constructed for custom post types on the front end
- Menu items are no longer displayed on the front end if their post type has `show_in_nav_menus` set to false

**Bug fixes:**
- Media thumbnails smaller than 150 pixels on the upload modal's media tab display incorrectly
- Media thumbnails smaller than 150 pixels on the "Design Settings" page display incorrectly
- Media thumbnails smaller than 100% of the container width on the "Create Post" and "Edit Post" pages display incorrectly
- Non-hierarchical post types (other than type `post`) are submitted with no value for `parent` (the value is supposed to be `0`)
- Long menu item labels on the admin nav menu display incorrectly

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- includes/class-menu.php
- includes/globals.php

## Version 1.0.2[b] (2020-07-02)

- Code cleanup in the `Post` class
- The `Post` class variables are now updated by the `Post::validateData` function
- Custom posts will now display on the admin bar if `show_in_admin_bar` is set to true
- Media entries now display in the admin stats bar graph
- Restructured the `statsBarGraph` function to display posts based on whether their `show_in_stats_graph` property is true
- Cleaned up the admin `index.php` file
- Tweaked previous entries in the changelog
- Post previews now redirect to the proper permalink when the post is published

**Bug fixes:**
- Media thumbnails smaller than 100 pixels on the "List Media" page display incorrectly
- Media thumbnails smaller than 150 pixels on the "Edit Media", "Create User", "Edit User", and "Edit Profile" pages display incorrectly
- The `parent` parameter is not type cast to an integer in the global `getPermalink` function

**Modified files:**
- admin/includes/class-media.php (M)
- admin/includes/class-post.php
- admin/includes/class-profile.php (M)
- admin/includes/class-user.php (M)
- admin/includes/css/style.css (M)
- admin/includes/css/style.min.css (M)
- admin/includes/functions.php
- admin/index.php
- includes/class-post.php
- includes/functions.php
- includes/globals.php

## Version 1.0.1[b] (2020-06-25)

- Tweaked the readme
- Tweaked a previous entry in the changelog
- Images of `x-icon` MIME type can now be accessed through the upload modal
- When a widget is created, it is no longer assigned an author
- When a menu item is created, it is no longer assigned an author
- All stylesheets are now served minified
- Added a missing semicolon in the `modal.js` file
- Taxonomies can now be dynamically registered (allows for custom taxonomies)
- Tweaked documentation in the Carbon theme's `functions.php` file
- The `registerPostType` function now sets the label to the post type's name if no label is provided
- Tweaked the `adminNavMenuItem` function to allow empty arrays to be passed without creating an empty submenu item
- Created a global function that sets all post type labels
- The admin `Post` class now sets the queried post data in the constructor
- Custom post type data is now passed to the `Post` class constructor
- Default post types (`page`, `post`, `media`, `nav_menu_item`, `widget`) are now registered in the `globals.php` file
- Added multiple new arguments to the `registerPostType` function:
  - `hierarchical` (whether the post type should be treated like a post or a page)
  - `show_in_stats_graph` (whether to show the post type in the admin stats bar graph)
  - `show_in_admin_menu` (whether to show the post type in the admin nav menu)
  - `show_in_admin_bar` (whether to show the post type in the admin bar)
  - `show_in_nav_menus` (whether to show the post type in front end nav menus)
  - `menu_link` (base link for the post type's admin menu item)
  - `taxonomy` (allows for connecting a custom taxonomy to the post type)
- Default and custom post types are now dynamically added to the admin nav menu
- New functions:
  - `globals.php` (`getPostTypeLabels`, `registerDefaultPostTypes`, `registerTaxonomy`)

**Bug fixes:**
- Redirects don't work properly for the following post types: `media`, `nav_menu_item`, `widget`

**Modified files:**
- README.md (M)
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-widget.php (M)
- admin/includes/css/style.min.css (N)
- admin/includes/functions.php
- admin/includes/js/modal.js (M)
- admin/posts.php
- content/themes/carbon/functions.php (M)
- includes/css/style.min.css (N)
- includes/functions.php
- includes/globals.php
- init.php (M)

## Version 1.0.0[b] (2020-06-21)
*Feature Update: Custom Post Types*

- Created content for the readme
- Renamed `changelog.md` to `changelog-alpha.md`
- Created a new changelog for Beta
- Improved mobile styling for the setup and installation pages
- Tweaked some of the text in the `setup.php` file
- Improved mobile styling for the log in and forgot password pages
- Menus and widgets can now be dynamically registered by themes
  - The Carbon theme now registers three widgets by default
  - The Carbon theme now registers two menus by default
- Arbitrary text strings can now be easily sanitized
- Post types can now be dynamically registered (allows for custom post types)
- Moved the admin nav menu items to a new function that simply displays them
- The `includes/functions.php` and `themes/<theme>/functions.php` files are now included on the back end
- The admin nav menu now supports custom post types
- User privileges are now created when a custom post type is registered
- Added a `type` parameter to the front end `Post::getPostPermalink` function
- Modified the way post permalinks are constructed so that custom post types have a base permalink before the slug
- Changed the inclusion order of the `load-theme.php`, `class-post.php`, `class-category.php`, and `load-template.php` files in the root `index.php` file
- The `load-template.php` file is no longer included in the `load-theme.php` file
- Tweaked how slugs are sanitized in several back end classes
- If the site's home page is accessed from its full permalink, it now redirects to the home URL (e.g., `www.mydomain.com`)
- Admin menu items are now hidden if a logged in user does not have sufficient privileges to view them
- Deprecated functions:
  - Admin `Post` class (`getPermalink`)
  - Admin `functions.php` (`adminNavMenu`)
  - `functions.php` (`registerMenu`, `registerWidget`)
  - `globals.php` (`registerPostType`, `sanitize`)

**Bug fixes:**
- A `DROP TABLE` query is run on empty database installations
- An error occurs when attempting to move a menu item up or down if it's the only item on a given menu

**Modified files:**
- README.md
- admin/header.php
- admin/includes/class-category.php (M)
- admin/includes/class-menu.php (M)
- admin/includes/class-post.php
- admin/includes/class-theme.php (M)
- admin/includes/class-widget.php (M)
- admin/includes/css/install.css
- admin/includes/css/install.min.css
- admin/includes/functions.php
- admin/install.php
- admin/setup.php
- content/themes/carbon/functions.php
- includes/class-post.php
- includes/css/style.css (M)
- includes/deprecated.php
- includes/functions.php
- includes/globals.php
- includes/load-theme.php (M)
- includes/logs/changelog-alpha.md (R)
- includes/logs/changelog-beta.md (N)
- index.php