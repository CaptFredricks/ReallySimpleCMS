# ReallySimpleCMS Changelog (Beta)

----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - renamed file, X - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
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
- Created a function that fetches the comment count from the database
- Tweaked code in the `Post::listPosts` function
- Added a status nav for the "List Comments" page to allow filtering comments by status (e.g., `approved` or `unapproved`)
- The status nav links for the "List \<post_type>" and "List Comments" pages now use the full admin URL
- Reduced the comment feed update timer from 60 seconds to 15 (note: this is how often the feed checks for updates, not how often it actually refreshes)
- The `Settings::getPageList` function now checks whether the home page exists and displays a blank option in the dropdown if it doesn't
- Tweaked various previous entries in the changelogs

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

----------------------------------------------------------------------------------------------------
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

----------------------------------------------------------------------------------------------------
## Version 1.1.5[b] (2020-11-14)

- Removed an unnecessary `if` statement from the `ajax.php` file
- Fixed a bug in numerous classes where class variables were not updated when an admin "Edit \<item>" form was submitted
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

----------------------------------------------------------------------------------------------------
## Version 1.1.4[b] (2020-11-10)

- Improved security for the `session` and `pw-reset` cookies
- Created a variable for the `Login` class that stores whether HTTPS is enabled
- Created a constructor for the `Login` class
- Moved the `PW_LENGTH` constant to the top of the `Login` class
- Fixed a bug where the sitemap would be appended multiple times to the `robots.txt` file if the `sitemap.xml` file was deleted and recreated
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

----------------------------------------------------------------------------------------------------
## Version 1.1.3[b] (2020-11-07)

- Fixed sitemap links in the `sitemap-index.php` file (they previously were missing the `includes` directory before the filename)
- Created a function that compiles and includes all of the necessary meta tags for the `head` section
- Added canonical tags to the list of meta tags included in the `head` section
- Deleted the Carbon theme's `header-cat.php` and `header-tax.php` files
- Created a function that constructs and displays the page title (applies to posts and terms)
- Added checks in the Carbon theme's `index.php` file to prevent errors from occurring if the current page is a term (this is only relevant if a taxonomy template doesn't exist)
- Moved a check for the theme `index.php` file from the `load-theme.php` file to the `load-template.php` file
- Category pages fallback to the generic taxonomy template if a category template does not exist
- Cleaned up some entries in the Alpha changelog

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

----------------------------------------------------------------------------------------------------
## Version 1.1.2[b] (2020-11-04)

- Added validation in the `init.php` file that checks whether the `BASE_INIT` constant has been defined (if so, it only loads the basic initialization files, otherwise it loads everything)
- Cleaned up code in the `ajax.php` file
- Cleaned up some entries in the Alpha changelog
- Created sitemaps for posts and terms
- Created a file that generates a sitemap index
- Tweaked validation in the `init.php` file
- Added `sitemap.xml` to the `.gitignore` file
- Tweaked documentation in the `install.php` and `setup.php` files
- Fixed a bug where the `robots.txt` file was not being updated when the `do_robots` setting was updated
- Added additional validation for the `robots.txt` file to the `Settings::validateSettingsData` function
- Tweaked the coloring of comment upvote and downvote buttons

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

----------------------------------------------------------------------------------------------------
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
- Fixed a bug in the `Query` class that caused any database field containing the string `count` to return an error
- The `Comment`, `Menu`, `Post`, `Term`, `User`, and `Widget` class constructors now only fetch specific columns from the database
- The `Menu` class now makes use of class variables (inherited from `Post`)
- Removed the `Term::count` variable, as it was unused
- Removed the `Post::modified` variable, as it was unused
- Removed the `Comment::post`, `Comment::author`, `Comment::date`, and `Comment::parent` variables, as they were unused
- Fixed a bug where classes with multi-worded names would cause an error and not autoload
- Moved all user role functions from the `Settings` class to a new `UserRole` class and created class variables for it
- Comment upvotes and downvotes are now grey when they are inactive and colored when they are active

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

----------------------------------------------------------------------------------------------------
## Version 1.1.0[b] (2020-10-22)

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

----------------------------------------------------------------------------------------------------
## Version 1.0.9[b] (2020-09-10)

- The current post's `type` is now added to the `body` tag as a CSS class
- Replaced `section` tags with `div` tags in several Carbon theme files
- Fixed a minor visual issue with the blank user avatar on the admin bar
- The `id` parameter in the `Post::slugExists` function is now optional (default value is `0`)
- Created a function that constructs a unique slug
- Improved the logic in the `getUniqueFilename` function
- The media upload system now checks whether the media's slug is unique before uploading it
- Deprecated the `filenameExists` function (merged its functionality with the `getUniqueFilename` function)
- Users will no longer see an error message if the chosen slug is not unique (instead, the CMS will append a number at the end of the slug to make it unique)
- Created `getUniquePostSlug` and `getUniqueTermSlug` alias functions
- The menu item link dropdowns now only include posts and terms of the same post type or taxonomy as their menu item
- Added multiple CSS classes to the `body` tag on term pages (e.g., `class="<slug> <taxonomy> <taxonomy>-id-<id>"`)
- Fixed a bug that caused the "Insert Media" button to populate both the content and meta description fields
- Fixed a minor bug with the `Post::getAuthorList` function where the author's id would sometimes be passed as a string
- Post objects initialized with a slug no longer redirect to the 404 not found page if the post is not published (this resolves issues with redirection when using the `getPost` function to pull data on posts that are drafts)
- Cleaned up some entries in the Alpha changelog

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

----------------------------------------------------------------------------------------------------
## Version 1.0.8[b] (2020-08-11)

- The `Query::showTables` function now has an optional `table` parameter
- Created a function that checks whether a database table exists
- Essential database tables are now recreated individually if they are accidentally deleted instead of prompting the user to reinstall the entire database
- If one or more tables are missing from the database and `admin/install.php` is accessed, the whole database is not reinstalled (only the missing tables are reinstalled)
- Created a function that populates the `user_roles` database table
- Created a function that populates the `user_privileges` and `user_relationships` database tables
- Privileges are now created for comments
- Undeprecated the `populateUsers`, `populatePosts`, `populateSettings`, `populateTaxonomies`, and `populateTerms` functions
- Moved the `getUserRoleId` and `getUserPrivilegeId` functions to the `globals.php` file
- Created a function that populates any missing essential database tables
- Cleaned up some entries in the Alpha changelog

**Modified files:**
- admin/includes/functions.php
- admin/install.php
- includes/class-query.php
- includes/deprecated.php
- includes/functions.php
- includes/globals.php
- init.php

----------------------------------------------------------------------------------------------------
## Version 1.0.7[b] (2020-07-30)

- Tweaked a previous entry in the changelog
- The `parent` parameter of the `getPermalink` function is now optional (default value is 0)
- The permalink base is now added to permalinks on the "Create \<post_type>" forms (this only affects custom post types)
- Cleaned up some entries in the Alpha changelog
- Minor text change in the Carbon theme's `term.php` file
- Post types and taxonomies can no longer be registered multiple times (this allowed for default post types and taxonomies to be overrided in themes)
- Post types can no longer be registered with the same name as an existing taxonomy and vice versa
- Errors are no longer generated by the `adminNavMenu` and `adminBar` functions if a post type has a nonexistent taxonomy registered to it
- Removed a block of code in the `registerPostType` function that caused the `category` taxonomy to function as a fallback if the post type had an invalid taxonomy registered
- Fixed some errors on the "List \<post_type>s", "Create \<post_type>", and "Edit \<post_type>" forms if the post type was non-hierarchical and had an invalid taxonomy
- Renamed the Carbon theme's `term.php` file to `taxonomy.php` and `header-term.php` file to `header-tax.php`
  - The new format for custom taxonomy theme template files is `taxonomy-<taxonomy>.php`
- Added support for custom post type theme template files (format: `posttype-<type>.php`)

**Modified files:**
- admin/includes/class-post.php
- admin/includes/functions.php
- content/themes/carbon/header-tax.php (R)
- content/themes/carbon/taxonomy.php (R/M)
- includes/functions.php
- includes/globals.php
- includes/load-template.php

----------------------------------------------------------------------------------------------------
## Version 1.0.6[b] (2020-07-25)

- Improved how permalinks are structured for custom post types and taxonomies (this fixed an issue with all taxonomies having `term` as their base url)
- Tweaked a previous entry in the changelog
- Created a new class variable in the `Post` class to hold taxonomy data
- Renamed the `Post::getCategoriesList` function to `Post::getTermsList`
- Custom taxonomies are now properly linked with their respective post types
- Renamed the `Post::getCategories` function to `Post::getTerms`
- The 'List Posts' page now properly shows the taxonomy related to the post type (and omits it if the post type doesn't have a taxonomy)
- Removed taxonomy labels from the `getPostTypeLabels` function
- Moved most of the root `index.php` file's contents to the `init.php` file
- Improved the way the CMS determines whether the current page is a post or a term
- Moved the `isCategory` function to the `deprecated.php` file, as it is no longer used
- Renamed the `Post::getPostCategories` function to `Post::getPostTerms`
- Created a function that checks whether the user is currently viewing an admin page
- Created a function that checks whether the user is currently viewing the login page
- Created a function that checks whether the user is currently viewing the 404 not found page
- Created a function that creates a `Term` object based on a slug
- Changed the way the `load-template.php` file tries to load taxonomy templates
- Added support for custom taxonomy templates
- Renamed the `getPostsInCategory` function to `getPostsWithTerm`
- Fixed a bug in the `getPostsWithTerm` function that allowed blank post entries to be added to the posts array if the posts are not published
- Added a message to the `getRecentPosts` function if there are no posts that can be displayed
- The `getRecentPosts` function can now be used to load posts associated with any taxonomy and of any post type
- Taxonomies are now displayed on the admin statistics bar graph if they have `show_in_stats_graph` set to true
- Custom taxonomies will now display in nav menus if `show_in_nav_menus` is set to true
- Fixed several issues with menu items using custom taxonomies
- Fixed an error with menu items that point to nonexistent posts or terms

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

----------------------------------------------------------------------------------------------------
## Version 1.0.5[b] (2020-07-23)

- Added the `can_upload_media` permission to the admin nav menu
- Fixed a permalink redirection bug that caused post previews not to load their contents
- Created a function that unregisters a post type (default post types cannot be unregistered)
- Created a function that fetches a user privilege's id
- Admin menu item labels are now properly filtered to remove underscores
- Tweaked how default post type and taxonomy labels are displayed in various locations
- Fixed a bug that prevented non-hierarchical post types (aside from type `post`) from being submitted to the database
- Underscores are now replaced with hyphens in post type and taxonomy base urls
- Created a function that fetches a user role's id
- Cleaned up the `getTaxonomyId` function
- Created a function that checks whether a post exists in the database
- Created a function that checks whether a post type exists in the database
- Created a function that unregisters a taxonomy (default taxonomies cannot be unregistered)
- Fixed an issue where the widths of newly uploaded images would not be calculated properly (image dimensions are now fetched via PHP and not JS)
- Created class variables for the `Term` class
- Created the "List Terms", "Create Term", and "Edit Term" pages
- Moved all functions from the admin `Category` class to the `Term` class (only the `listCategories`, `createCategory`, `editCategory`, and `deleteCategory` functions remain as alias functions)
- Created a function that fetches a taxonomy's name based on its id
- Code cleanup in the `Post` class
- Code cleanup in the `globals.php` file
- The admin nav menu now scrolls if its content overflows the window
- Added an inner content wrapper to all admin pages to fix a floating issue with page content presented by the overflow fix
- Current page functionality now works properly for custom post types and taxonomies
- Tweaked the admin themes

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

----------------------------------------------------------------------------------------------------
## Version 1.0.4[b] (2020-07-12)

- Tweaked the max width of select inputs in data form sidebars
- Permalinks no longer redirect to the 404 not found page if they contain query parameters
- Cleaned up some entries in the Alpha changelog
- Cleaned up code in the admin `posts.php` file
- Created a global array to hold the registered taxonomies
- Moved the `registerTaxonomy` function to the `globals.php` file
- Created a function that sets the default labels for registered taxonomies
- Created a function that registers the default taxonomies
- Added a new argument to the `registerPostType` function: `create_privileges` (will create new privileges in the database for the post type if true)
- The `registerTaxonomy` function can now accept arguments
- Custom taxonomies now have proper links on the admin nav menu
- Cleaned up code in the `adminNavMenu` function
- Cleaned up code in the `adminBar` function
- Custom taxonomies now have proper links on the admin bar
- Fixed an issue with the `adminNavMenuItem` function that caused an empty submenu item to break out of the loop (causing subsequent submenu items not to display)
- Added a link to the "Create Theme" page to the admin bar
- Improved privilege checking for items on the admin nav menu
- Added privilege checking for items on the admin bar
- Improved privilege checking for the admin "List \<item>" pages
- The `getPrivileges` function now orders privileges by their ids

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

----------------------------------------------------------------------------------------------------
## Version 1.0.3[b] (2020-07-04)

- Tweaked previous entries in the changelog
- Fixed a visual issue with media thumbnails smaller than 150px on the upload modal's media tab
- Whitelisted the `style` attribute for divs and spans in the `formTag` function
- The remove icon now moves based on the avatar's width on the "Create User", "Edit User", and "Edit Profile" pages
- Fixed a visual issue with media thumbnails smaller than 150 pixels on the "Design Settings" page
- The remove icon now moves based on the site logo and site icon's width on the "Design Settings" page
- Fixed a visual issue with media thumbnails smaller than 100% of the container width on the "Create Post" and "Edit Post" pages
- The remove icon now moves based on the featured image's width on the "Create Post" and "Edit Post" pages
- Cleaned up some entries in the Alpha changelog
- Added the `public` argument to the `registerPostType` function (if set to true, post type will display in menus, the admin bar, etc. and if set to false it will not)
- Custom posts will now display in the "Create Menu" and "Edit Menu" pages if `show_in_nav_menus` is set to true
- Fixed a bug where non-hierarchical post types (other than type `post`) would be submitted with no value for `parent` (the value is supposed to be set to `0`)
- Menu item permalinks are now properly constructed for custom post types on the front end
- Menu items are no longer displayed on the front end if their post type has `show_in_nav_menus` set to false
- Fixed a visual issue with long menu item labels on the admin nav menu

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

----------------------------------------------------------------------------------------------------
## Version 1.0.2[b] (2020-07-02)

- Code cleanup in the `Post` class
- The `Post` class variables are now updated by the `Post::validateData` function
- Custom posts will now display on the admin bar if `show_in_admin_bar` is set to true
- Media entries now display in the admin stats bar graph
- Restructured the `statsBarGraph` function to display posts based on whether their `show_in_stats_graph` property is true
- Cleaned up the admin `index.php` file
- Fixed a visual issue with media thumbnails smaller than 100 pixels on the "List Media" page
- Fixed a visual issue with media thumbnails smaller than 150 pixels on the "Edit Media", "Create User", "Edit User", and "Edit Profile" pages
- Tweaked previous entries in the changelog
- Fixed a bug in the global `getPermalink` function where the `parent` parameter was not type cast to an integer
- Post previews now redirect to the proper permalink when the post is published

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

----------------------------------------------------------------------------------------------------
## Version 1.0.1[b] (2020-06-25)

- Tweaked the readme
- Tweaked a previous entry in the changelog
- Images of `x-icon` MIME type can now be accessed through the upload modal
- When a widget is created, it is no longer assigned an author
- When a menu item is created, it is no longer assigned an author
- All stylesheets are now served minified
- Added a missing semicolon in the `modal.js` file
- Created a function that registers custom taxonomies
- Tweaked documentation in the Carbon theme's `functions.php` file
- The `registerPostType` function now sets the label to the post type's name if no label is provided
- Tweaked the `adminNavMenuItem` function to allow empty arrays to be passed without creating an empty submenu item
- Created a global function that sets all post type labels
- The admin `Post` class now sets the queried post data in the constructor
- Custom post type data is now passed to the `Post` class constructor
- Fixed a minor issue with redirection for certain post types (media, nav_menu_items, widgets)
- Created a global function that registers the default post types (page, post, media, nav_menu_item, widget)
- Added multiple new arguments to the `registerPostType` function:
  - `hierarchical` (whether the post type should be treated like a post or a page)
  - `show_in_stats_graph` (whether to show the post type in the admin stats bar graph)
  - `show_in_admin_menu` (whether to show the post type in the admin nav menu)
  - `show_in_admin_bar` (whether to show the post type in the admin bar)
  - `show_in_nav_menus` (whether to show the post type in front end nav menus)
  - `menu_link` (base link for the post type's admin menu item)
  - `taxonomy` (allows for connecting a custom taxonomy to the post type)
- Default and custom post types are now dynamically added to the admin nav menu

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

----------------------------------------------------------------------------------------------------
## Version 1.0.0[b] (2020-06-21)

- Created content for the readme
- Renamed `changelog.md` to `changelog-alpha.md`
- Created a new changelog for Beta
- Improved mobile styling for the setup and installation pages
- Tweaked some of the text in the `setup.php` file
- Fixed a bug where a `DROP TABLE` query was run on empty database installs
- Improved mobile styling for the log in and forgot password pages
- Created a function that registers widgets for a theme
- The Carbon theme now registers three widgets by default
- Created a function that registers menus for a theme
- The Carbon theme now registers two menus by default
- Created a global function that sanitizes text strings
- Created a global function that registers custom post types
- Moved the admin nav menu items to a new function that simply displays them
- The `includes/functions.php` and `themes/<theme>/functions.php` files are now included on the back end
- The admin nav menu now supports custom post types
- User privileges are now created when a custom post type is registered
- The `Post::getPermalink` function is now deprecated
- Added a `type` parameter to the front end `Post::getPostPermalink` function
- Modified the way post permalinks are constructed so that custom post types have a base permalink before the slug
- Changed the inclusion order of the `load-theme.php`, `class-post.php`, `class-category.php`, and `load-template.php` files in the root `index.php` file
- The `load-template.php` file is no longer included in the `load-theme.php` file
- Fixed an error that occurred when attempting to move a menu item up or down when it is the only item on a given menu
- Tweaked how slugs are sanitized in several back end classes
- If the site's home page is accessed from its full permalink, it now redirects to the home URL (e.g., `www.mydomain.com`)
- Admin menu items are now hidden if a logged in user does not have sufficient privileges to view them

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