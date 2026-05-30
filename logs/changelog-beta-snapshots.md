# ReallySimpleCMS Changelog (Beta Snapshots)

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
- \*-alpha (Alpha version)
- \*-beta (Beta version)

## Version 1.4.0-beta_snap-04 (2026-03-30)

**General changes:**
- Tidied up the `.gitignore` file
- Bumped minimum PHP version to `8.1` and recommended to `8.2`
- Created a global stylesheet file to hold system-wide styles
- Renamed the `theme` setting to `active_theme` and added a new setting: `active_modules`
- Replaced all occurrences of hardcoded database table names with the new `getTable` function
- All admin classes are now prefixed with `ad_` when instantiated (e.g., `$rs_post` becomes `$rs_ad_post`)
- Moved the `Admin\Theme::isActiveTheme`, `::isBrokenTheme`, and `::themeExists` methods to `register/themes.php`
- Moved the `registerAdminMenuItem` and `registerAdminMenu` functions to `register/admin-pages.php`
- Updated the broken theme message on the List Themes page
- When a new user is created, the admin theme is now set to `bedrock` by default
- Added a user stats page
- Renamed front end resource files to `front.css` and `front.js`, respectively
- Created a `Register` class for admin registries
  - The admin menu is now registered dynamically based on registered admin pages
    - Each menu item has an index to determine placement
    - Relabeled "Your Profile" to "My Profile" on menu for consistency
    - Moved the "Customization" menu item up below the dashboard and renamed it "Customize"
	- Tweaked other sub-labels
- Various labels throughout the admin dashboard are now dynamically populated based on labels from registered admin pages, post types, and taxonomies
  - Menus and logins have not been made dynamic yet
- Moved some core files to a new `utilities` subdirectory
  - This includes the captcha generator, backward compatibility functions, debugging functions, polyfills, database schema, and updater files
  - `polyfill-functions.php` renamed to `polyfills.php`
- Admin themes can now be updated and custom themes can be registered
- Renamed a param in the `postTypeExists` function for consistency
- Modules can now be updated on the admin update page
- Modules can now be activated and deactivated
- Tweaked styling of admin nav menu items
- Improved logic for determining whether a post or term is being viewed (previously, a term with the same slug as a post would redirect to the post)
- Favicon now displays on the following utility screens:
  - Error screen
  - Fallback theme screen
  - Maintenance screen
- All sitemap generator code has been moved to the `/utilities` subdirectory
- Overhauled the way menus are constructed in the `Engine\Menu` class
- The `Engine\Query::select` method now returns unprefixed versions of database columns; this is to simplify their use in the code
- Theme and admin theme registries can now include a description
- Tweaked labels for custom links on the menu admin page
  - "Custom" is now "Custom Link"
  - "Title" field is now "Caption"
- The themes page now displays author and version info
- Updated admin copyright link
- Standardized core functions filenames

**Programmatic changes:**
- New constants/global vars:
  - `RS_THEME_FUNC`, `UTILS`
  - `$rs_admin_pages`
- Renamed constants/global vars:
  - `GLOBAL_FUNC` -> `RS_GLOBAL_FUNC`, `RS_FUNC` -> `RS_FRONT_FUNC`
  - `$notices` -> `$rs_notices`
- New classes:
  - `Admin\Register`
- New functions/methods:
  - `Admin\Comment` class (`getActionLinks`)
  - `Admin\Media` class (`getActionLinks`)
  - `Admin\Module` class (`activateModule`, `deactivateModule`, `getActionLinks`, `installModule`, `validateSubmission`)
  - `Admin\Post` class (`getActionLinks`)
  - `Admin\Register` class (`registerAdminPage`, `setAdminPageActions`, `setAdminPageLabels`)
  - `Admin\Term` class (`getActionLinks`)
  - `Admin\Theme` class (`getActionLinks`)
  - `Admin\User` class (`getActionLinks`)
  - `Admin\UserRole` class (`getActionLinks`)
  - `Admin\Widget` class (`getActionLinks`)
  - `Engine\Menu` class (`__construct`, `getMenuId`, `getSubmenu`)
  - `Engine\Register` class (`setPostTypeActions`, `setTaxonomyActions`)
  - `register/admin-pages.php` (`adminPageExists`, `registerAdminPage`, `runAdminPagesRegister`)
  - `register/admin-themes.php` (`loadAdminThemeReg`)
  - `register/modules.php` (`loadModuleReg`)
  - `utilities/sitemaps.php` (`genPostSitemaps`, `genSitemaps`, `genTermSitemaps`)
- Renamed functions/methods:
  - `Engine\Menu` class (`getMenuItemDescendants` -> `getMenuItemTree`)
  - `Engine\Register` class (`getPostTypeLabels` -> `setPostTypeLabels`, `getTaxonomyLabels` -> `setTaxonomyLabels`)
  - `register/admin-themes.php` (`registerDefaultAdminThemes` -> `runAdminThemesRegister`)
  - `register/modules.php` (`registerRequiredModules` -> `runModulesRegister`)
  - `register/post-types.php` (`registerDefaultPostTypes` -> `runPostTypesRegister`)
  - `register/taxonomies.php` (`registerDefaultTaxonomies` -> `runTaxonomiesRegister`)
  - `register/themes.php` (`registerThemes` -> `runThemesRegister`)
- Removed functions/methods:
  - `register/themes.php` (`registerDefaultThemes`)

**Bug fixes:**
- The `isDismissedNotice` function contains an outdated instatiation of the `Admin\Notice` class
- In PHP 8.2 or higher, a line of code in the `Engine\ErrorHandler` class triggers a deprecation notice
- Some global vars aren't properly loaded
- The error fallback page doesn\'t exist
- A faulty logic check during system initialization prevents maintenance mode and system updates from running
- The old `style.css` files attempt to be loaded on utility screens, such as the maintenance screen
- Errors can occur during sitemap generation due to improperly scoped variables
- The admin about page displays the wrong number of installed modules

**Modified files:**
- .gitignore
- admin/about.php (M)
- admin/categories.php
- admin/comments.php
- admin/header.php
- admin/index.php
- admin/logins.php
- admin/media.php
- admin/menus.php
- admin/modules.php (M)
- admin/posts.php
- admin/profile.php
- admin/settings.php
- admin/stats.php (N)
- admin/terms.php
- admin/themes.php
- admin/update.php
- admin/users.php
- admin/widgets.php
- content/themes/carbon/carbon.php (N)
- content/themes/carbon/category.php (M)
- content/themes/carbon/footer.php (M)
- content/themes/carbon/functions.php
- content/themes/carbon/header.php (M)
- content/themes/carbon/index.php (M)
- content/themes/carbon/post.php (M)
- content/themes/carbon/script.js (M)
- content/themes/carbon/style.css (M)
- content/themes/carbon/taxonomy.php (M)
- includes/admin/class-comment.php
- includes/admin/class-login.php
- includes/admin/class-media.php
- includes/admin/class-menu.php
- includes/admin/class-module.php
- includes/admin/class-notice.php (M)
- includes/admin/class-post.php
- includes/admin/class-profile.php
- includes/admin/class-register.php (N)
- includes/admin/class-settings.php
- includes/admin/class-term.php
- includes/admin/class-theme.php
- includes/admin/class-update.php (N)
- includes/admin/class-user-role.php
- includes/admin/class-user.php
- includes/admin/class-widget.php
- includes/admin/interface-admin.php (M)
- includes/ajax/bulk-actions.php (M)
- includes/constants.php
- includes/engine/class-api-fetch.php
- includes/engine/class-comment.php
- includes/engine/class-curl-fetch.php (M)
- includes/engine/class-error-handler.php (M)
- includes/engine/class-login.php
- includes/engine/class-menu.php
- includes/engine/class-post.php
- includes/engine/class-query.php
- includes/engine/class-register.php
- includes/engine/class-term.php
- includes/engine/class-update.php (M)
- includes/enums/enum-table.php (N)
- includes/functions-admin.php (R)
- includes/functions-critical.php (R)
- includes/functions-front.php (R)
- includes/functions-global.php (R)
- includes/functions-theme.php (R)
- includes/load-template.php (M)
- includes/load-theme.php
- includes/register/admin-pages.php (N)
- includes/register/admin-themes.php
- includes/register/modules.php
- includes/register/post-types.php
- includes/register/taxonomies.php
- includes/register/themes.php
- includes/sitemap-index.php (X)
- includes/sitemap-posts.php (X)
- includes/sitemap-terms.php (X)
- includes/utilities/backward-compat.php
- includes/utilities/captcha.php (M)
- includes/utilities/debug.php (M)
- includes/utilities/error.php (N)
- includes/utilities/fallback-theme.php
- includes/utilities/maintenance.php
- includes/utilities/polyfills.php (R)
- includes/utilities/sitemaps.php (N)
- includes/utilities/update-db.php
- includes/utilities/update.php (M)
- index.php (M)
- init.php (M)
- login.php (M)
- resources/css/admin.css
- resources/css/admin.min.css
- resources/css/button.css (M)
- resources/css/font-awesome-rules.min.css (M)
- resources/css/front.css (R)
- resources/css/front.min.css (R)
- resources/css/global.css (N)
- resources/css/global.min.css (N)
- resources/js/front.js (R)
- resources/js/front.min.js (R)
- resources/js/jquery.min.js (M)

## Version 1.4.0-beta_snap-03 (2025-03-13)

**General changes:**
- Henceforth, all mentions of core classes in the changelog will include their namespaces
- Removed redundant checks for whether a user is online in several files
- Cleaned up previous Beta snapshots changelog entries
- Added an additional error check to the `rsdb-config.php` file
- The `MODULES` constant now points to `/content/modules`
- Tweaked README copyright text
- Moved the admin `functions.php` file to the `includes` directory and renamed it `admin-functions.php`
- The `Engine\Query::select`, `::selectRow`, `::selectField`, `::insert`, `::update`, and `::delete` methods now accept an optional parameter (bundled with `$table`) to prefix the columns/fields
- Replaced numerous instances of hardcoded HTML with DOMtags
- Modals and AJAX loaders have been moved to their own subdirectories of the `includes` directory
- All admin classes have been namespaced and moved to their own subdirectory of the `includes` directory
  - Cleaned up code in all classes
- Added a new setting: `active_modules`, which will hold a serialized array of all active modules
- Added an admin page for modules
- Modules can be updated using the ReallySimpleSystems API
- Changed the "Full Name" column on the List Users page to "Display Name"
- Split the admin about page into the following tabs:
  - Stats: displays various stats about the site
  - Software: displays software information
  - Credits: displays development information
- The admin about page now displays much more information
- Removed the `ADMIN_THEMES` constant from `admin-functions.php` as it's now defined in `constants.php`
- Overhauled registry logic and created a new class to handle all component registries
  - Modules, themes, and admin themes can now be registered
  - Post types and taxonomies can be registered as before
  - Register and unregister functions for modules, themes, and admin themes should be called from the component's base file (e.g., `/<module_name>/<module_name>.php`, `/<theme_name>/functions.php`)
- Cleaned up admin CSS and moved default styles to the new Bedrock admin theme
- Added missing minified JS resources
- Tweaked the `.gitignore` file

**Programmatic changes:**
- New constants/global vars:
  - `AJAX`, `MODALS`
  - `$rs_modules`, `$rs_theme_path`
- Renamed constants/global vars:
  - `ADMIN_FUNC` -> `RS_ADMIN_FUNC`
- New classes:
  - `Admin\Module`
  - `Engine\Register`
- New functions/methods:
  - `Admin\Comment` class (`getResults`)
  - `Admin\Login` class (`getResults`)
  - `Admin\Menu` class (`getResults`)
  - `Admin\Module` class (`__construct`, `bulkActions`, `exitNotice`, `getEntryCount`, `getResults`, `isActive`, `listRecords`, `pageHeading`)
  - `Admin\Post` class (`getResults`)
  - `Admin\User` class (`getResults`)
  - `Engine\Register` class ()
  - `global-functions.php` (`getQueryString`, `moduleExists`, `registerModule`, `registerRequiredModules`, `removeDir`, `unregisterModule`)
- Renamed functions/methods:
  - `Admin\Comment` class (`getCommentCount` -> `getEntryCount`)
  - `Admin\Login` class (`getLoginCount` -> `getEntryCount`)
  - `Admin\Menu` class (`getMenuCount` -> `getEntryCount`)
  - `Admin\Post` class (`getPostCount` -> `getEntryCount`)
  - `Admin\Profile` class (`getThemesList` -> `getAdminThemesList`)
  - `Admin\User` class (`getUserCount` -> `getEntryCount`)
  - `admin-functions.php` (`adminNavMenu` -> `registerAdminMenu`, `adminNavMenuItem` -> `registerAdminMenuItem`)
  - `global-functions.php` (`adminThemeStylesheet` -> `loadAdminTheme`)
- Deprecated functions/methods:
  - `admin-functions.php` (`adminScript`, `adminStylesheet`)
- Removed functions/methods:
  - `Engine\Query` class (`errorMsg`)

**Bug fixes:**
- Queries of prefixed columns using the `COUNT()` function fail
- The default homepage is not properly set during installation
- The `unregisterTaxonomy` function is missing its second parameter
- Admin notices can't be properly dismissed due to the inclusion of a removed constant

**Modified files:**
- .gitignore (M)
- 404.php
- README.md (M)
- admin/about.php (M)
- admin/categories.php (M)
- admin/comments.php (M)
- admin/header.php
- admin/index.php
- admin/logins.php (M)
- admin/media.php (M)
- admin/menus.php (M)
- admin/modules.php (N)
- admin/posts.php
- admin/profile.php (M)
- admin/settings.php (M)
- admin/terms.php
- admin/themes.php (M)
- admin/update.php
- admin/users.php (M)
- admin/widgets.php (M)
- content/admin-themes/bedrock/bedrock.css (N)
- content/admin-themes/bedrock/bedrock.php (N)
- content/admin-themes/forest/forest.css
- content/admin-themes/forest/forest.php (N)
- content/admin-themes/harvest/harvest.css
- content/admin-themes/harvest/harvest.php (N)
- content/admin-themes/ocean/ocean.css
- content/admin-themes/ocean/ocean.php (N)
- content/admin-themes/sky/sky.css (R)
- content/admin-themes/sky/sky.php (N)
- content/admin-themes/sunset/sunset.css
- content/admin-themes/sunset/sunset.php (N)
- content/themes/carbon/footer.php (M)
- content/themes/carbon/functions.php (M)
- content/themes/carbon/index.php (M)
- includes/admin-functions.php (R)
- includes/admin/class-comment.php
- includes/admin/class-login.php
- includes/admin/class-media.php
- includes/admin/class-menu.php
- includes/admin/class-module.php (N)
- includes/admin/class-notice.php (M)
- includes/admin/class-post.php
- includes/admin/class-profile.php
- includes/admin/class-settings.php
- includes/admin/class-term.php
- includes/admin/class-theme.php
- includes/admin/class-user-role.php
- includes/admin/class-user.php
- includes/admin/class-widget.php
- includes/admin/interface-admin.php (M)
- includes/ajax/admin-ajax.php (R)
- includes/ajax/ajax.php (R)
- includes/ajax/bulk-actions.php
- includes/ajax/file-upload.php (R)
- includes/ajax/load-media.php
- includes/constants.php
- includes/critical-functions.php
- includes/debug.php
- includes/engine/class-api-fetch.php (M)
- includes/engine/class-error-handler.php (N)
- includes/engine/class-post.php
- includes/engine/class-query.php
- includes/engine/class-register.php (N)
- includes/engine/class-update.php (N)
- includes/functions.php
- includes/global-functions.php
- includes/load-template.php
- includes/load-theme.php
- includes/modals/modal-delete.php (M)
- includes/modals/modal-upload.php (M)
- includes/register/admin-themes.php (N)
- includes/register/modules.php (N)
- includes/register/post-types.php (N)
- includes/register/taxonomies.php (N)
- includes/register/themes.php (N)
- includes/theme-functions.php (M)
- includes/update-db.php
- includes/update.php
- init.php
- resources/css/admin.css (R)
- resources/css/admin.min.css (R)
- resources/js/admin.js (R,M)
- resources/js/admin.min.js (N)
- resources/js/modal.min.js (N)
- resources/js/script.js (M)
- resources/js/script.min.js (M)
- setup/rsdb-config.php
- setup/rsdb-install.php
- setup/run-install.php (M)

## Version 1.4.0-beta_snap-02 (2025-02-05)

**General changes:**
- **WARNING!** This update contains many potentially breaking changes. Back up your database before updating.
- General cleanup:
  - Optimized code in all core files
  - Reorganized the database schema
    - All database tables now have prefixed columns
	- Existing databases will be updated automatically
  - Added more documentation throughout and cleaned up unnecessary documentation
    - Added documentation of class variables and methods to the top of all class and function files
  - Renamed various system constants to align with other ReallySimpleSystems projects
  - Moved all setup-related files to a new `setup` directory and overhauled the code
  - Moved all CSS and JS files related to system setup out of the `admin` subdirectory
  - Organized and deprecated several items in the `Query` class (this is a major breaking change)
  - Added more exit statuses to various admin pages and simplified how they are created
  - Removed the `DOMTAGS_VERSION` constant from core code; it is now defined within the DOMtags library
  - The `DEBUG_MODE` and `MAINT_MODE` constants are now defined in the default config file
  - Added documentation of class variables and methods to all classes and functions files
  - Replaced numerous instances of hardcoded HTML with DOMtags
  - Optimized code in the database updater
- Text changes:
  - Added "Developed by" credit to the admin about page
  - Updated admin footer copyright text
  - Added sister projects to README and updated the copyright
- Added support for a `homepage.php` template file in themes
- Added an `is_default` column to the `user_privileges` table
  - Default and custom privileges are now listed under different columns on the dashboard
- Added two new possible labels for custom post types and taxonomies:
  - `no_items` (displays on the list page when no records can be found in the database)
  - `title_placeholder` (displays in the title field; post types only)
- The `unregisterPostType` and `unregisterTaxonomy` functions no longer erase post and taxonomy data by default
  - Post metadata is now cleared if the associated post is deleted in this way
- The `populateTables` function has been moved to `global-functions.php`
- Added `upvotes` and `downvotes` columns to the List Comments table
- Added a `slug` column to the List Menus table
- Media can now be replaced from the List Media page
- The `Comment::getAuthor` function now returns 'Anonymous' instead of a dash when the author is blank
- The admin List Themes page now has pagination (this is considered experimental for now)
- Updated DOMtags to v1.1.4.2
- Added SQL debugging to more methods in the `Query` class
- The Forgot Password form now tries to use the admin email to send email notifications to the user
- Added `IF EXISTS` check to the `Query::dropTable` and `::dropTables` methods
- Added missing field ids to various forms

**Programmatic changes:**
- New constants/global vars:
  - `MODULES`, `RS_DEVELOPER`, `RS_LEAD_DEV`, `RS_PROJ_START`, `SETUP`
- Renamed constants/global vars:
  - `CMS_ENGINE` -> `RS_ENGINE`, `CMS_VERSION` -> `RS_VERSION`, `CRIT_FUNC` -> `RS_CRIT_FUNC`, `DB_CONFIG` -> `RS_CONFIG`, `DB_SCHEMA` -> `RS_SCHEMA`, `DEBUG_FUNC` -> `RS_DEBUG_FUNC`, `FUNC` -> `RS_FUNC`
- Removed constants/global vars:
  - `DOMTAGS_VERSION`, `QUERY_CLASS`
- New functions/methods:
  - Admin `Comment` class (`exitNotice`, `pageHeading`)
  - Admin `Login` class (`exitNotice`, `pageHeading`)
  - Admin `Media` class (`exitNotice`, `pageHeading`)
  - Admin `Menu` class (`exitNotice`, `pageHeading`)
  - Admin `Post` class (`exitNotice`)
  - Admin `Profile` class (`pageHeading`)
  - Admin `Settings` class (`exitNotice`, `pageHeading`)
  - Admin `Term` class (`exitNotice`, `pageHeading`)
  - Admin `Theme` class (`exitNotice`, `pageHeading`)
  - Admin `User` class (`pageHeading`)
  - Admin `UserRole` class (`exitNotice`, `pageHeading`)
  - Admin `Widget` class (`exitNotice`, `pageHeading`)
  - `ApiFetch` class (`getDownload`)
  - `Query` class (`createTable`)
  - `critical-functions.php` (`baseSetup`, `checkDBConfig`, `repopulateTable`)
- Renamed functions/methods:
  - Admin `Comment` class (`validateData` -> `validateSubmission`)
  - Admin `Login` class (`blacklistExits` -> `blacklistExists`, `validateBlacklistData` -> `validateBlacklistSubmission`, `validateRuleData` -> `validateRuleSubmission`)
  - Admin `Media` class (`deleteMedia` -> `deleteRecordMedia`, `editMedia` -> `editRecordMedia`, `listMedia` -> `listRecordsMedia`, `replaceMedia` -> `replaceRecordMedia`, `uploadMedia` -> `uploadRecordMedia`, `validateData` -> `validateSubmission`)
  - Admin `Menu` class (`validateMenuData` -> `validateMenuSubmission`, `validateMenuItemData` -> `validateMenuItemSubmission`)
  - Admin `Profile` class (`validateData` -> `validateSubmission`)
  - Admin `Settings` class (`validateSettingsData` -> `validateSubmission`)
  - Admin `Term` class (`validateData` -> `validateSubmission`)
  - Admin `Theme` class (`validateData` -> `validateSubmission`)
  - Admin `User` class (`validateData` -> `validateSubmission`)
  - Admin `UserRole` class (`validateUserRoleData` -> `validateSubmission`)
  - Admin `Widget` class (`validateData` -> `validateSubmission`)
  - `Login` class (`validateForgotPasswordData` -> `validateForgotPasswordSubmission`, `validateLoginData` -> `validateLoginSubmission`, `validateResetPasswordData` -> `validateResetPasswordSubmission`)
- Deprecated functions/methods:
  - `Query` class (`errorMsg`)
- Removed functions/methods:
  - Admin `Profile` class (`validatePasswordData`)
  - Admin `User` class (`validatePasswordData`, `validateReassignContentData`)
  - Admin `functions.php` (`formTag`, `tag`)
  - `global-functions.php` (`trailingSlash`)

**Bug fixes:**
- Styles on the database setup screen are not properly loaded (they point to the old location)
- The `Query` class is not properly namespaced in `setup.php` or `install.php`
- The database setup fails because the `isAdmin` function isn't loaded in `critical-functions.php`
- Custom user roles can neither be edited nor deleted
- Post metadata doesn't update properly when a post is saved
- Some references to `PDOException` in the `Query` class are not properly namespaced
- Dashes are not being added to filenames of uploaded files
- The `index_post` metadata is being added to non-content post types
- Indexing for posts isn't set during installation
- The update privileges script doesn't properly transfer privilege data
- An error can occur in the `getOnlineUser` function if database tables are missing or have old column names (this can occur during database updates)
- Bulk actions can fail if the passed id is not an integer
- Form row labels target the `name` prop instead of the `id` prop
- The admin `Comment::deleteSpamComments` method doesn't specify a return type

**Modified files:**
- 404.php (M)
- README.md (M)
- admin/about.php
- admin/categories.php (M)
- admin/comments.php
- admin/header.php
- admin/includes/ajax-upload.php (M)
- admin/includes/ajax.php (M)
- admin/includes/bulk-actions.php
- admin/includes/class-comment.php
- admin/includes/class-login.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-notice.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-term.php
- admin/includes/class-theme.php
- admin/includes/class-user-role.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/functions.php
- admin/includes/interface-admin.php
- admin/includes/load-media.php (M)
- admin/includes/modal-delete.php (M)
- admin/includes/modal-upload.php (M)
- admin/logins.php
- admin/media.php
- admin/menus.php (M)
- admin/posts.php
- admin/profile.php (M)
- admin/settings.php
- admin/terms.php
- admin/themes.php
- admin/update.php (M)
- admin/users.php
- admin/widgets.php
- content/admin-themes/forest.css (M)
- content/admin-themes/harvest.css (M)
- content/admin-themes/light.css (M)
- content/admin-themes/ocean.css (M)
- content/admin-themes/sunset.css (M)
- content/themes/carbon/category.php (M)
- content/themes/carbon/footer.php (M)
- content/themes/carbon/functions.php (M)
- content/themes/carbon/header.php (M)
- content/themes/carbon/index.php (M)
- content/themes/carbon/post.php (M)
- content/themes/carbon/script.js (M)
- content/themes/carbon/style.css (M)
- content/themes/carbon/taxonomy.php (M)
- includes/ajax.php (M)
- includes/backward-compat.php (M)
- includes/class-dom-tags.php
- includes/constants.php
- includes/critical-functions.php
- includes/debug.php (M)
- includes/dom-tags/\*
- includes/domtags.php
- includes/engine/class-api-fetch.php
- includes/engine/class-comment.php
- includes/engine/class-curl-fetch.php
- includes/engine/class-login.php
- includes/engine/class-menu.php
- includes/engine/class-post.php
- includes/engine/class-query.php
- includes/engine/class-term.php
- includes/fallback-theme.php (M)
- includes/functions.php
- includes/global-functions.php
- includes/load-template.php (M)
- includes/load-theme.php (M)
- includes/maintenance.php
- includes/polyfill-functions.php
- includes/schema.php
- includes/sitemap-index.php (M)
- includes/sitemap-posts.php
- includes/sitemap-terms.php
- includes/theme-functions.php
- includes/update-db.php
- includes/update.php (M)
- init.php
- login.php
- resources/css/admin/style.css
- resources/css/admin/style.min.css
- resources/css/button.css (M)
- resources/css/button.min.css (M)
- resources/css/setup.css (R)
- resources/css/setup.min.css (R)
- resources/css/style.css (M)
- resources/css/style.min.css (M)
- resources/js/admin/modal.js (M)
- resources/js/admin/script.js
- resources/js/script.js
- resources/js/setup.js (R)
- resources/js/setup.min.js (R)
- setup/default-config.php (R)
- setup/rsdb-config.php (R)
- setup/rsdb-install.php (R)
- setup/run-install.php

## Version 1.4.0-beta_snap-01 (2023-12-26)
*Feature Update: Auto Update*

**General changes:**
- Changed the format of snapshot versions from `x.x.x[x]_snap-xx` to `x.x.x-xxxx_snap-xx`
- Changed the format of regular versions from `x.x.x[a|b]` to `x.x.x-alpha|beta`
- Updated documentation in various files
- Added new API fetch files
- The `polyfill-functions.php` file is now loaded in the `critical-functions.php` file
- Created a new file to hold backward compatibility functions
- Removed the `deprecated.php` file
- Moved core classes to a new `engine` directory and namespaced them
- Removed a deprecated constant

**Bug fixes:**
- An external link on the admin footer doesn't open in a new tab

**Modified files:**
- 404.php (M)
- admin/about.php (M)
- admin/footer.php (M)
- admin/header.php (M)
- admin/includes/functions.php
- admin/index.php (M)
- admin/update.php (N)
- includes/ajax.php
- includes/backward-compat.php (N)
- includes/captcha.php (M)
- includes/config-setup.php (M)
- includes/constants.php (M)
- includes/critical-functions.php (M)
- includes/debug.php (M)
- includes/deprecated.php (X)
- includes/engine/class-api-fetch.php (N)
- includes/engine/class-comment.php (M)
- includes/engine/class-curl-fetch.php (N)
- includes/engine/class-login.php (M)
- includes/engine/class-menu.php (M)
- includes/engine/class-post.php (M)
- includes/engine/class-query.php
- includes/engine/class-term.php (M)
- includes/fallback-theme.php (M)
- includes/functions.php
- includes/global-functions.php
- includes/load-template.php (M)
- includes/load-theme.php (M)
- includes/maintenance.php (M)
- includes/polyfill-functions.php (M)
- includes/schema.php (M)
- includes/sitemap-index.php (M)
- includes/sitemap-posts.php (M)
- includes/sitemap-terms.php (M)
- includes/theme-functions.php (M)
- includes/update-db.php
- includes/update.php
- index.php (M)
- init.php (M)
- login.php (M)

## Version 1.2.0-beta_snap-05 (2020-12-28)

**General changes:**
- Tweaked the schema for the `login_rules` database table
- Login rules can now be viewed, created, edited, and deleted
- Login blacklist duration is now dynamically converted from seconds to a more readable format
- Added class variables for the `login_rules` table and its functions
- Tweaked documentation in the `Login` class
- The `actionLink` function can now accept `data_item` as a valid argument (allows for the action link to include a `data-item` parameter)
- The "Log In" form now checks whether a user/IP should be blacklisted based on predefined rules
- Added two new columns to the `login_attempts` table, `last_blacklisted_login` and `last_blacklisted_ip`, which will track the most recent time the login (username or email) or IP address of a login attempt was blacklisted (if ever)
- Cleaned up code in the `Query` class and added support for various comparison operators
- Cleaned up code in the admin `Login` class

**Programmatic changes:**
- New functions/methods:
  - Admin `Login` class (`loginRules`, `createRule`, `editRule`, `deleteRule`, `validateRuleData`, `formatDuration`)
  - `Login` class (`shouldBlacklist`)

**Modified files:**
- admin/includes/class-login.php
- admin/includes/functions.php
- admin/logins.php
- includes/class-login.php
- includes/class-query.php
- includes/schema.php

## Version 1.2.0-beta_snap-04 (2020-12-20)

**General changes:**
- Added two new settings:
  - `track_login_attempts` (whether login attempts should be logged in the database or not)
  - `delete_old_login_attempts` (whether to delete login attempts more than 30 days old)
- The new settings are added to the database automatically for sites updating from `1.1.7-beta`
- Added support for conditionally hidden fields in admin forms
  - The "Comments" and "Logins" settings groups are now conditionally hidden if the "Enable comments" or "Keep track of login attempts" settings are unchecked, respectively
- Cleaned up code in the `Settings::validateSettingsData` function
- The `Login` class now checks whether the `track_login_attempts` setting is turned on and only tracks new attempts if it is
- The admin `Login` class now checks whether the `delete_old_login_attempts` setting is turned on and deletes old login attempts if it is

**Modified files:**
- admin/includes/class-login.php
- admin/includes/class-settings.php
- admin/includes/js/script.js
- includes/class-login.php
- includes/globals.php (M)
- includes/update.php

## Version 1.2.0-beta_snap-03 (2020-12-10)

**General changes:**
- Tweaked a previous entry in the changelog
- All `select`, `update`, and `delete` queries can now use `OR` logic in their `where` clauses by supplying `'logic'=>'OR'` as an element of the `where` clause array
- If a logged in user is added to the logins blacklist, they are now logged out
- The `DISTINCT` keyword can now be added to `select` queries (it must be added to the `data` parameter's array)
- The `actionLink` function can now accept `classes` as a valid argument (allows for the action link to receive CSS classes)
- Tweaked documentation in the admin `Login` class
- Custom login blacklist entries can now be created (this is distinct from the "Login Attempts" blacklist options)

**Programmatic changes:**
- New functions/methods:
  - Admin `Login` class (`createBlacklist`)

**Modified files:**
- admin/includes/class-login.php
- admin/includes/functions.php
- admin/logins.php
- includes/class-query.php

## Version 1.2.0-beta_snap-02 (2020-12-08)

**General changes:**
- Blacklisted logins can now be edited and whitelisted
- Expired blacklisted logins are now deleted when a user views the "Login Blacklist" page
- Added an icon to the "Login" admin menu item
- Added login-related nav items to the admin bar menu
- Added privileges for the `login_attempts`, `login_blacklist`, and `login_rules` tables to the `populateUserPrivileges` function
- The new privileges are automatically installed upon updating to this version or higher
- Tweaked a previous entry in the changelog
- The `populateUserPrivileges` function now only selects the default roles
- Users can now be checked for multiple privileges as opposed to just one (can be configured to use `AND` or `OR` logic)
- Added privilege checks for all logins admin pages and the admin bar
- Cleaned up some logic in the `adminNavMenu` and `adminBar` functions
- Made minor formatting tweaks to the `init.php` file

**Programmatic changes:**
- New functions/methods:
  - Admin `Login` class (`editBlacklist`, `whitelistLoginIP`, `blacklistExits`)
  - `globals.php` (`userHasPrivileges`)

**Bug fixes:**
- Users can view actions for post types that they don't have privileges for on the admin bar
- Users can view actions for taxonomies that they don't have privileges for on the admin bar
- Users can view actions for post types that they don't have privileges for on the admin nav menu

**Modified files:**
- admin/includes/class-login.php
- admin/includes/functions.php
- admin/logins.php
- includes/functions.php
- includes/globals.php
- includes/update.php
- init.php (M)

## Version 1.2.0-beta_snap-01 (2020-12-05)
*Feature Update: Login Tracking*

**General changes:**
- Tweaked a previous entry in the changelog
- Added three new tables to the database schema:
  - `login_attempts` - tracks login attempts
  - `login_blacklist` - holds all blacklisted logins and ip addresses
  - `login_rules` - stores rules for what happens if a user attempts to log in too many times unsuccessfully
  - These tables are automatically installed upon updating to this version or higher
- Created a new admin class to handle logins
  - Logins (usernames/emails) and IP addresses can be blacklisted
- The front end `Login::validateLoginData` function now records all login attempts
- Created an admin nav item for the "Login Attempts", "Login Blacklist", and "Login Rules" pages
- Action links can now be created dynamically
- Added user/IP blacklist checks to the login form
- A default timezone is now set in the `config-setup.php` file (and likewise the `config.php` file)

**Programmatic changes:**
- New functions/methods:
  - Admin `Login` class (`loginAttempts`, `blacklistLogin`, `blacklistIPAddress`, `loginBlacklist`, `validateBlacklistData`)
  - Admin `functions.php` (`actionLink`)
  - `Login` class (`isBlacklisted`, `getBlacklistDuration`)

**Modified files:**
- admin/includes/class-login.php (N)
- admin/includes/functions.php
- admin/logins.php (N)
- includes/class-login.php
- includes/config-setup.php (M)
- includes/schema.php
- includes/update.php

## Version 1.1.0-beta_snap-05 (2020-10-21)

**General changes:**
- Tweaked documentation in the Carbon theme's `script.js` file
- Tweaked documentation in the front end `script.js` file
- When using the Carbon theme, the sticky header no longer covers the reply box when a reply link is clicked
- Users will now be redirected back to the admin page they were viewing upon logging back in if they are logged out unexpectedly
- Renamed some selectors for the comment section and tweaked some styling
- Users can now edit and update their comments
- Optimized and improved the action links functionality for the "List Users" page
  - Users who don't have the `can_edit_users` or `can_delete_users` privileges can no longer see the "Edit" or "Delete" action links

**Programmatic changes:**
- New functions/methods:
  - `Comment` class (`updateComment`)

**Bug fixes:**
- Links to the home page (typically `/`) that also have query strings attached to them redirect to the 404 "Not Found" page
- Users without sufficient privileges can't edit their profile via the "List Users" page

**Modified files:**
- admin/header.php
- admin/includes/class-user.php
- content/themes/carbon/script.js
- content/themes/carbon/style.css
- includes/ajax.php
- includes/class-comment.php
- includes/js/script.js
- init.php (M)

## Version 1.1.0-beta_snap-04 (2020-09-23)

**General changes:**
- Added comments to the "Admin" admin bar dropdown
- Reply links are now hidden on existing comments if comments are disabled on the post, post type, or global level (existing comments are not hidden, however)
- Styled and added a reply form to the comment feed
- Comments can now be created and deleted
- If a comment is a reply to another comment, the child comment now has a link to its parent
- Tweaked previous entries in the changelog
- Added a container element to the comment feed
- The comment feed will now refresh whenever a new reply is posted or a comment is deleted
- Code cleanup in the front end `script.js` file

**Programmatic changes:**
- New functions/methods:
  - `Comment` class (`getCommentAuthorId`, `getCommentParent`, `getCommentCount`, `getCommentReplyBox`, `createComment`, `deleteComment`)
- Renamed functions/methods:
  - `Comment` class (`getCommentThread` -> `getCommentFeed`)

**Bug fixes:**
- The `media` post type doesn't display on the "New" admin bar dropdown

**Modified files:**
- content/themes/carbon/style.css
- includes/ajax.php
- includes/class-comment.php
- includes/class-post.php
- includes/functions.php
- includes/js/script.js

## Version 1.1.0-beta_snap-03 (2020-09-22)

**General changes:**
- Added two new settings:
  - `comment_status` (whether comments are enabled)
  - `comment_approval` (whether comments are automatically approved)
- The new settings are added to the database automatically for sites updating from `1.0.9-beta`
- Comments are now hidden if the global `comment_status` setting is turned off, including on post types that have them enabled
- Created a front end class that handles comments
  - Added comment feeds
  - Added upvote and downvote functionality
  - Posts will now fetch all comments associated with them and display them below the post content
- Tweaked the styling of the Carbon theme
- Added styling for comment feeds
- Created a file to handle Ajax requests
- An em dash now displays if a comment has no author (anonymous) on the dashboard
- Added `includes/error_log` to the `.gitignore` file
- The content for the default page and post created on installation are now wrapped in paragraph tags

**Programmatic changes:**
- New functions/methods:
  - `Comment` class (`getCommentAuthor`, `getCommentDate`, `getCommentContent`, `getCommentUpvotes`, `getCommentDownvotes`, `getCommentStatus`, `getCommentPermalink`, `getCommentThread`, `incrementVotes`, `decrementVotes`)
  - `Post` class (`getPostComments`)

**Modified files:**
- .gitignore (M)
- admin/includes/class-comment.php (M)
- admin/includes/class-post.php
- admin/includes/class-settings.php
- content/themes/carbon/post.php
- content/themes/carbon/style.css
- includes/ajax.php (N)
- includes/class-comment.php (N)
- includes/class-post.php
- includes/globals.php
- includes/js/script.js
- includes/update.php

## Version 1.1.0-beta_snap-02 (2020-09-21)

**General changes:**
- Tweaked a previous entry in the changelog
- Tweaked documentation in the `update.php` file
- Added most of the core functions of the admin `Comment` class
  - Comments can be viewed, edited, deleted, approved, and unapproved
- Tweaked the styling of data tables on the admin dashboard
- Tweaked documentation in the `Post` and `Term` classes
- Added an additional CSS class to data table columns to ensure that they don't conflict with other page elements
- Tweaked the schema for the `comments` database table
- Cleaned up code in the `Post` class
- Changed the format of snapshot versions from `x.x.x[x][ss-xx]` to `x.x.x[x]{ss-xx}`

**Programmatic changes:**
- New functions/methods:
  - Admin `Comment` class (`listComments`, `editComment`, `approveComment`, `unapproveComment`, `deleteComment`, `validateData`, `getPost`, `getPostPermalink`, `getAuthor`)

**Modified files:**
- admin/comments.php
- admin/includes/class-comment.php
- admin/includes/class-post.php
- admin/includes/class-term.php (M)
- admin/includes/css/style.css
- admin/includes/css/style.min.css
- admin/includes/functions.php (M)
- includes/schema.php (M)
- includes/update.php (M)

## Version 1.1.0-beta_snap-01 (2020-09-20)
*Feature Update: Comments*

**General changes:**
- Created a database schema for the `comments` table
- Created a file that will handle safely updating things such as the database schema
- The `update.php` file is included in the `init.php` file
- The `comments` database table is now created when the version is higher than `1.0.9-beta`
- Added a new `comments` argument to the `registerPostType` function (if set to true, comments will be allowed for that post type; default is false)
- Set comments to display for the `post` post type
- Two new metadata entries are now created for posts of any type that has comments enabled (`comment_status` and `comment_count`)
- The comment count is now listed on the "List \<post_type>" page as its own column
- Added a comments block to the "Create \<post_type>" and "Edit \<post_type>" pages
- Created a `Comment` admin class and admin `comments.php` file
- Added comments to the admin nav menu (below the post types and above 'Customization')
- Tweaked documentation in the admin `functions.php` file
- Tweaked a previous entry in the Beta changelog
- Created a new changelog for Beta Snapshots
- Adjusted privileges for the default user roles (the CMS will automatically reinstall the `user_privileges` and `user_relationships` tables)
- Individual posts with comments disabled will now display an emdash on the "Comments" column of the "List \<post_type>" page
- When a post with comments is deleted, its comments are now deleted along with it

**Bug fixes:**
- Comment privileges aren't properly being assigned to default user roles

**Modified files:**
- admin/comments.php (N)
- admin/includes/class-comment.php (N)
- admin/includes/class-post.php
- admin/includes/functions.php
- includes/globals.php
- includes/logs/changelog-beta-snapshots.md (N)
- includes/schema.php
- includes/update.php (N)
- init.php