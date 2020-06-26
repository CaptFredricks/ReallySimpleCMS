# ReallySimpleCMS Changelog (Beta)

----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - renamed file, X - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.0.1[b] (2020-06-25)

- Tweaked the README
- Tweaked a previous entry in the changelog
- Images of x-icon MIME type can now be accessed through the upload modal
- When a widget is created, it is no longer assigned an author
- When a menu item is created, it is no longer assigned an author
- All stylesheets are now served minified
- Added a missing semicolon in the modal.js file
- Created a function that registers custom taxonomies
- Tweaked documentation in the Carbon theme's functions.php file
- The registerPostType function now sets the label to the post type's name if no label is provided
- Tweaked the adminNavMenuItem function to allow empty arrays to be passed without creating an empty submenu item
- Created a global function that sets all post type labels
- The admin Post class now sets the queried post data in the constructor
- Custom post type data is now passed to the Post class constructor
- Fixed a minor issue with redirection for certain post types (media, nav_menu_items, widgets)
- Created a global function that registers the default post types (page, post, media, nav_menu_item, widget)
- Added multiple new arguments to the registerPostType function:
  - hierarchical (whether the post type should be treated like a post or a page)
  - show_in_stats_graph (whether to show the post type in the admin stats bar graph)
  - show_in_admin_menu (whether to show the post type in the admin nav menu)
  - show_in_admin_bar (whether to show the post type in the admin bar)
  - show_in_nav_menus (whether to show the post type in front end nav menus)
  - menu_link (base link for the post type's admin menu item)
  - taxonomy (allows for connecting a custom taxonomy to the post type)
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

- Created content for the README file
- Renamed changelog.md to changelog-alpha.md
- Created a new changelog for Beta
- Improved mobile styling for the setup and installation pages
- Tweaked some of the text in the setup.php file
- Fixed a bug where a 'DROP TABLE' query was run on empty database installs
- Improved mobile styling for the log in and forgot password pages
- Created a function that registers widgets for a theme
- The Carbon theme now registers three widgets by default
- Created a function that registers menus for a theme
- The Carbon theme now registers two menus by default
- Created a global function that sanitizes text strings
- Created a global function that registers custom post types
- Moved the admin nav menu items to a new function that simply displays them
- The includes/functions.php and themes/<theme>/functions.php files are now included on the back end
- The admin nav menu now supports custom post types
- User privileges are now created when a custom post type is registered
- The Post::getPermalink function is now deprecated
- Added a 'type' parameter to the front end Post::getPostPermalink function
- Modified the way post permalinks are constructed so that custom post types have a base permalink before the slug
- Changed the inclusion order of the load-theme.php, class-post.php, class-category.php, and load-template.php files in the root index.php file
- The load-template.php file is no longer included in the load-theme.php file
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