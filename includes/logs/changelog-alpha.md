# ReallySimpleCMS Changelog (Alpha)

----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - renamed file, X - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 2.4.5[a] (2020-06-16)

* Added a blank avatar for users with no avatar
* Added styling for the blank avatar on the front end and back end
* The getMedia function now creates a blank <img> tag instead of an <a> tag if the 'src' attribute is '//:0'
* Removed an unnecessary if statement in the User::listUsers function
* Improved styling of the admin themes
* Tweaked previous entries in the changelog
* Updated jQuery to v3.5.1
* Updated Font Awesome to v5.13.0
* Moved Font Awesome's font-face rules to a separate css file (for easier updating moving forward)
* Improved mobile responsive design of the admin dashboard (should be fully responsive)
* Tweaked how the post's permalink displays on mobile
* Tweaked how the custom menu item fieldset displays on mobile
* Posts can now be previewed
* Added the "noreferrer" and "noopener" directives to all links using the target attribute

**Modified files:**
* admin/header.php (M)
* admin/includes/class-media.php (M)
* admin/includes/class-menu.php (M)
* admin/includes/class-post.php (M)
* admin/includes/class-user.php (M)
* admin/includes/css/style.css
* admin/includes/functions.php
* content/admin-themes/forest.css
* content/admin-themes/harvest.css
* content/admin-themes/ocean.css
* content/admin-themes/sunset.css
* includes/class-post.php
* includes/css/font-awesome-rules.min.css (N)
* includes/css/font-awesome.min.css
* includes/css/style.css
* includes/fonts/fa-brands.ttf
* includes/fonts/fa-regular.ttf
* includes/fonts/fa-solid.ttf
* includes/functions.php
* includes/globals.php (M)
* includes/img/blank.png (N)
* includes/js/jquery.min.js

----------------------------------------------------------------------------------------------------
## Version 2.4.4[a] (2020-04-30)

* Added more details to the previous log in the changelog
* Tweaked the title in the Carbon theme's category archive
* Tweaked styling of the Carbon theme's category archive
* Added the author and date to single posts in the Carbon theme
* Tweaked styling of the Carbon theme's single posts
* Adjusted date formats for the Carbon theme

**Modified files:**
* content/themes/carbon/category.php (M)
* content/themes/carbon/functions.php (M)
* content/themes/carbon/post.php
* content/themes/carbon/style.css
* includes/class-post.php (M)

----------------------------------------------------------------------------------------------------
## Version 2.4.3[a] (2020-04-27)

* The constants.php file is now included in the setup.php and install.php files (this fixes an error with an undefined constant)
* Added a form for reassigning a user's content to another user when the former is deleted
* Created a function that fetches a username based on a user id (User class)
* Created a function that constructs a list of users (User class)
* Created a function that checks whether a user has content assigned to them
* If a user has no assigned content, deletion of their account will work as normal; if they do, however, the admin performing the deletion will be redirected to the new form
* Improved mobile responsive design of the admin dashboard

**Modified files:**
* admin/includes/class-user.php
* admin/includes/css/style.css
* admin/install.php
* admin/setup.php
* admin/users.php

----------------------------------------------------------------------------------------------------
## Version 2.4.2[a] (2020-03-11)

* Updated Font Awesome to v5.12.1
* Created a function that loads header scripts and stylesheets
* Created a function that loads footer scripts and stylesheets
* Added internal version numbers for the admin themes
* Improved styling of the admin themes

**Modified files:**
* 404.php
* admin/includes/functions.php (M)
* content/admin-themes/forest.css
* content/admin-themes/harvest.css
* content/admin-themes/ocean.css
* content/admin-themes/sunset.css
* content/themes/carbon/footer.php
* content/themes/carbon/header.php
* content/themes/carbon/header-cat.php
* includes/css/font-awesome.min.css
* includes/functions.php
* login.php

----------------------------------------------------------------------------------------------------
## Version 2.4.1[a] (2020-02-19)

* Reordered the postmeta entries in the populateTables function
* The Post::validateData function will no longer try to submit the 'template' postmeta entry if it is not included in the submitted data
* Created a template file for blog posts in the Carbon theme
* Moved the getTaxonomyId function to the globals.php file
* Created a function that fetches a post's categories
* Created a function that creates a Category object based on a provided slug
* Modified the Carbon theme's getRecentPosts function to accept an optional 'categories' parameter (if populated, it will display posts from specified categories)
* Created a function that returns the posts in a specific category

**Modified files:**
* admin/includes/class-post.php
* admin/includes/functions.php
* content/themes/carbon/category.php (M)
* content/themes/carbon/footer.php (M)
* content/themes/carbon/functions.php
* content/themes/carbon/post.php (N)
* includes/class-post.php
* includes/functions.php
* includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 2.4.0[a] (2020-01-30)

* Tweaked styling of certain title/name fields on the admin forms
* The recent post titles now link to the proper blog posts in the Carbon theme's getRecentPosts function
* Added redirect rules to the .htaccess file
* Fixed a pass by reference error in the Post class constructor
* Invalid post slugs and unpublished posts are now redirected to the 404 (Not Found) page
* Invalid permalinks now redirect to the proper permalink
* Tweaked a logic statement in the load-template.php file
* The getPermalink function now adds /category/ as the base slug for category pages
* Created a function that checks whether the current page is actually a category archive
* Created a front end class to handle entries in the 'terms' table
* Created a front end class to handle categories
* Changed the CSS classes for the Carbon theme's index.php article content wrapper
* Created a category template for the Carbon theme
* Added a global call to the Category object in the getHeader and getFooter functions
* Tweaked styling of the Carbon theme
* Optimized code in the getRecentPosts function
* Began styling the category template page
* Added a 'view' link to the 'List Categories' admin table
* Improved mobile responsive design of the admin modals and post forms

**Modified files:**
* .htaccess
* admin/includes/class-category.php
* admin/includes/css/style.css
* content/themes/carbon/category.php (N)
* content/themes/carbon/footer.php (M)
* content/themes/carbon/functions.php
* content/themes/carbon/header-cat.php (N)
* content/themes/carbon/index.php
* content/themes/carbon/style.css
* includes/class-category.php (N)
* includes/class-post.php
* includes/class-term.php (N)
* includes/functions.php
* includes/globals.php
* includes/load-template.php (M)
* index.php

----------------------------------------------------------------------------------------------------
## Version 2.3.3[a] (2020-01-22)

* Tweaked a previous entry in the changelog
* Fixed a bug with the Menu::isFirstSibling, Menu::getPreviousSibling, and Menu::getNextSibling functions that allowed menu items to be confused with items on other menus (the functions now receive the menu id as a second parameter)
* Fixed a bug with the Menu::isPreviousSibling and Menu::isNextSibling functions that allowed menu items to be confused with items on other menus (the functions now receive the menu id as a third parameter)
* Tweaked styling of the header menu for the Carbon theme
* Added a page template field to the 'Create Page' and 'Edit Page' forms
* A page template metadata entry is now created for the sample page when the CMS is installed
* Created a directory in the Carbon theme for page templates
* Created a function that constructs a list of page templates
* Created a function that fetches all siblings of a specified menu item
* Cleaned up code in numerous functions in the Menu class
* Fixed a bug in the Menu::isNextSibling function that prevented a menu item with children from being moved down
* Page/post/category titles are trimmed down to a maximum of 5 words on the menu sidebar
* Created a file that loads page templates
* Created a function that checks whether a page template exists
* Tweaked documentation and renamed a variable in the load-theme.php file
* Added a regular expression to sanitize the category slug in the Category::validateData function
* Added a regular expression to sanitize the post slug in the Post::validateData function
* Added a regular expression to sanitize the widget slug in the Widget::validateData function
* Improved mobile responsive design for admin forms

**Modified files:**
* admin/includes/class-category.php
* admin/includes/class-menu.php
* admin/includes/class-post.php
* admin/includes/class-widget.php
* admin/includes/css/style.css
* admin/includes/functions.php (M)
* content/themes/carbon/style.css
* includes/functions.php
* includes/load-template.php (N)
* includes/load-theme.php

----------------------------------------------------------------------------------------------------
## Version 2.3.2[a] (2020-01-20)

* Corrected an erroneous entry in the changelog
* Tweaked styling of the front end admin bar
* Improved the internal logic of the bodyClasses function
* Tweaked styling of the 404 (Not Found) page
* The admin bar now displays on the 404 page if the user is logged in
* Tweaked styling of the admin data lists
* Cleaned up code in the Theme::listThemes function
* Corrected a text error on the 'Create Theme' submit button
* Added an extra logic check to the Theme::activateTheme function
* Corrected a regular expression in the Theme::validateData function
* Tweaked documentation in the Theme class
* The select media button now remains disabled if the media upload form does not return a success
* Tweaked a regular expression in the uploadMediaFile function
* Added a regular expression to sanitize the menu slug in the Menu::validateMenuData function
* Created a function that inserts a nav menu item into the database
* Created a function that fetches all relationships for a menu
* Fixed a bug with the Menu::hasSiblings and Menu::isLastSibling functions that allowed menu items to be confused with items on other menus (the functions now receive the menu id as a second parameter)
* Tweaked code in the getPermalink function

**Modified files:**
* 404.php
* admin/includes/class-menu.php
* admin/includes/class-theme.php
* admin/includes/css/style.css (M)
* admin/includes/functions.php (M)
* admin/includes/js/modal.js
* content/themes/carbon/style.css (M)
* includes/css/style.css (M)
* includes/functions.php
* includes/globals.php (M)

----------------------------------------------------------------------------------------------------
## Version 2.3.1[a] (2020-01-18)

* Removed an unnecessary int cast in the getOnlineUser function
* Optimized code in the getHeader and getFooter functions
* Moved the getThemeScript and getThemeStylesheet functions to the front end functions.php file and optimized them
* Fixed a minor issue with the HTML in the adminBar function
* Optimized the isEmptyDir function
* Tweaked styling of the front end admin bar
* Created a function that fetches stylesheets for the admin themes
* Added a preview image for the Carbon theme
* Styled the admin 'List Themes' page
* Created a function that checks whether a theme is the active theme
* Created a function that activates an inactive theme
* Created a function that checks whether a theme exists
* Created a function that recursively deletes files and directories
* Created a function that deletes a selected theme
* Created a function that constructs the 'Create Theme' form
* Created a function that validates data submitted on the 'Create Theme' form

**Modified files:**
* admin/includes/class-theme.php
* admin/includes/css/style.css
* admin/includes/functions.php
* admin/themes.php
* content/themes/carbon/preview.png (N)
* includes/css/style.css
* includes/functions.php
* includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 2.3.0[a] (2020-01-15)

* Created a file to hold named constants used throughout the CMS
* Moved all constant declarations to the new constants.php file
* Created a new named constant for the themes directory
* Updated the file path for the config.php file in the setup.php file
* Created a global function that checks whether a directory is empty
* Created a fallback theme file in case the themes directory is empty
* Moved the front end theme files to the new Carbon theme folder
* Added an optional parameter for the getHeader and getFooter functions to allow specifying alternate template files from header.php and footer.php
* Added error checking to the getHeader and getFooter functions
* Modified the getThemeScript and getThemeStylesheet functions to search in the proper file path
* Created an admin page and a class for front end themes
* Added the 'List Themes' page to the admin nav menu
* Created user privileges for themes
* Updated the file path for the config.php file in the install.php file
* Created a function that displays a list of installed themes

**Modified files:**
* admin/header.php
* admin/includes/class-theme.php (N)
* admin/includes/functions.php
* admin/install.php
* admin/setup.php (M)
* admin/themes.php (N)
* content/footer.php (X)
* content/functions.php (X)
* content/header.php (X)
* content/index.php (X)
* content/script.js (X)
* content/style.css (X)
* content/themes/carbon/footer.php (N)
* content/themes/carbon/functions.php (N)
* content/themes/carbon/header.php (N)
* content/themes/carbon/index.php (N)
* content/themes/carbon/script.js (N)
* content/themes/carbon/style.css (N)
* includes/constants.php (N)
* includes/fallback.php (N)
* includes/functions.php
* includes/globals.php
* includes/load-theme.php (N)
* index.php
* init.php

----------------------------------------------------------------------------------------------------
## Version 2.2.7[a] (2020-01-14)

- Added the "theme color" meta tag to the `login.php` file
- Tweaked documentation in the front end `script.js` file
- Replaced some standard anonymous functions with arrow functions in the admin `modal.js` and `script.js` files
- Tweaked a styling rule in the front end `style.css` file
- The `config.php` file is now created in the root directory when the CMS is installed (it was previously created in the `includes` directory)
- Added a settings database entry for storing the current front end theme
- When the CMS is being initialized, it now looks for the `config.php` in the root directory
- Constructed and styled the front end admin bar
- The `getOnlineUser` function now only fetches the user's avatar id and not the whole file path
- Replaced some hard coded img tags with the `getMedia` function in the admin `header.php` file
- Made all remaining elements in the Carbon theme mobile responsive

**Modified files:**
- .gitignore (M)
- admin/header.php
- admin/includes/functions.php (M)
- admin/includes/js/modal.js (M)
- admin/includes/js/script.js (M)
- admin/setup.php (M)
- content/footer.php
- content/style.css
- includes/css/style.css
- includes/functions.php
- includes/globals.php
- includes/js/script.js (M)
- init.php
- login.php (M)

----------------------------------------------------------------------------------------------------
## Version 2.2.6[a] (2020-01-08)

- Tweaked styling of the setup and installation forms
- Tweaked styling of the log in and forgot/reset password forms
- Added a missing CSS class to the setup form
- Tweaked documentation in numerous back end files
- Added error checking to the `Media::listMedia` function that checks whether the media actually exists in the uploads directory
- Created a footer for the front end theme
- Created a `functions.php` file for the front end theme and included it in the root `index.php` file
- Created a function that fetches the most recent blog posts from the database
- Constructed and styled the front end footer
- Fixed an issue with the sticky header functionality
- Cleaned up code in the front end theme's `script.js` file
- Merged the `classes` and `link_text` parameters on the `getMedia` function into a single parameter, `props` that accepts an array of key/value pairs
- Updated all instances where the above parameters were being used
- Tweaked the `.gitignore` file

**Modified files:**
- .gitignore
- admin/includes/class-category.php (M)
- admin/includes/class-media.php
- admin/includes/class-menu.php (M)
- admin/includes/class-post.php (M)
- admin/includes/class-profile.php (M)
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php (M)
- admin/includes/class-widget.php (M)
- admin/includes/css/install.css (M)
- admin/includes/css/install.min.css (M)
- admin/includes/functions.php (M)
- admin/install.php (M)
- admin/setup.php
- content/footer.php
- content/functions.php (N)
- content/script.js
- content/style.css
- includes/class-post.php
- includes/css/style.css
- includes/globals.php
- index.php

----------------------------------------------------------------------------------------------------
## Version 2.2.5[a] (2020-01-04)

- Removed an unnecessary console log from the front end theme's `script.js` file
- Deleted the `page.php` file (deprecated in a previous version)
- Tweaked documentation in numerous front end files
- Reorganized the changelog
- Tweaked styling of the "scroll to top" button
- The current page's slug is now determined within the `Post` class' constructor
- The `Post::getPostParent` function now correctly returns an integer
- The `getHeader` and `getFooter` functions now include both the `Post` object and the user's session data as global variables
- Deprecated the `getPageSlug` function (its functionality is now used directly by the `Post` class' constructor)
- The `getPost` function's `slug` parameter is now required, and it no longer makes use of the deprecated `getPageSlug` function
- Cleaned up code in the `bodyClasses` function
- A class is now added to the body if the post/page is a child of another post/page
- Custom classes can now be added to the body tag by using the `bodyClass` function's optional `addtl_classes` parameter
- The global `session` variable is now set in the root `index.php` file
- Created a function that fetches a post's permalink (`Post` class)
- Added a CSS class to the widget content wrapper div
- The Font Awesome stylesheet is now included on the "Log In" page
- The jQuery library and the front end `script.js` file are now included on the "Log In" page
- Tweaked some styling of the log in form
- The password field can now be set to plain text on the log in form
- Set maximum a width and height for the delete and upload modals

**Modified files:**
- admin/includes/css/style.css (M)
- content/script.js (M)
- includes/class-login.php
- includes/class-menu.php (M)
- includes/class-post.php
- includes/css/style.css
- includes/deprecated.php
- includes/functions.php
- includes/globals.php (M)
- includes/js/script.js
- index.php
- login.php (M)
- page.php (X)

----------------------------------------------------------------------------------------------------
## Version 2.2.4[a] (2019-12-30)

- Tweaked styling of the front end theme
- List pages for all post types other than `page` now sort in descending order by date (pages sort in ascending order by title)
- If a post's date has not been set, it will no longer display the current date on the "List Post" page
- Tweaked styling for certain inputs on admin forms
- Added a publish date field to the "Create Post" and "Edit Post" forms
- Menu items are now given an `invalid` status if the post they are linked to is deleted
- Invalid menu items are now denoted in light red on the "Edit Menu" form
- Invalid menu items no longer are displayed on the front end
- If a new post is published without a date being set, its publish date will be set using the special `NOW()` function
- If an existing post is updated without a date being set, its publish date will be set to `null`
- The front end `Post` object is now created in the root `index.php` file
- Created a function that checks whether a post has a featured image
- Updated Font Awesome to v5.12.0
- Added a scroll to top button to the front end

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/css/style.css
- admin/includes/functions.php (M)
- content/footer.php
- content/header.php
- content/index.php
- content/style.css (M)
- includes/class-menu.php
- includes/class-post.php
- includes/css/font-awesome.min.css
- includes/css/style.css
- includes/functions.php
- includes/js/script.js
- index.php

----------------------------------------------------------------------------------------------------
## Version 2.2.3[a] (2019-12-24)

- Tweaked documentation in the `globals.php` file
- Tweaked code in the `getMedia` function
- Created a front end `Menu` class and moved all menu-related functions to it
- Tweaked documentation in the `includes/functions.php` file
- Repurposed the `getMenu` function
- Created a function that checks whether a menu item matches the current page
- Improved conditional CSS class handling for menu items in the `Menu::getMenu` and `Menu::getMenuItemDescendants` functions
- Restructured the content area of the front end
- Created a function that fetches a page's slug
- Created a function that constructs a list of body classes
- Deprecated the `getPost` function and replaced it with a new function (named the same) that instantiates the `Post` object
- A post's data can now be retrieved by calling the `getPost` function and optionally supplying a slug
- Fixed an issue in the `Menu::getMenuItemDescendants` function where sub menu items were not properly ordered
- Tweaked styling of the content area
- Created a function that fetches a post's metadata
- Added meta tags to the front end `header.php` file
- Created a function that constructs a post's full URL
- Cleaned up code in the front end theme's `script.js` file

**Modified files:**
- content/footer.php (M)
- content/header.php
- content/index.php
- content/script.js
- content/style.css
- includes/class-menu.php (N)
- includes/class-post.php
- includes/deprecated.php
- includes/functions.php
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 2.2.2[a] (2019-12-22)

- The front end header now remains sticky when the page is scrolled
- Fixed some visual issues with the header and tweaked its styling
- Created a function that constructs a nav menu
- Created a function that fetches a menu item's metadata
- Created a function that checks whether a menu item has a parent
- Created a function that checks whether a menu item has children
- Created a function that fetches a menu item's descendants
- Created a function that fetches a menu item's parent
- Created a global function that constructs a permalink
- Moved the `Post::isHomePage` function to the `globals.php` file and tweaked its code
- A house icon now denotes the home page on the "List Pages" admin page
- Menus are now fully dynamic on the front end
- Moved all theme-related styles to the theme stylesheet (`content/style.css`)
- Moved all theme-related scripts to the theme script file (`content/script.js`)
- Fixed some minor issues with the mobile header scripts
- The `Post::getPermalink` function now makes use of the global `getPermalink` function
- Tweaked documentation in multiple files

**Modified files:**
- admin/includes/class-post.php
- content/footer.php (M)
- content/header.php
- content/script.js
- content/style.css
- includes/css/style.css
- includes/functions.php
- includes/globals.php
- includes/js/script.js

----------------------------------------------------------------------------------------------------
## Version 2.2.1[a] (2019-12-17)

- Tweaked a previous entry in the changelog
- Corrected a typo on the 404 (Not Found) error page
- Styled the 404 (Not Found) error page
- Added optional `classes` and `link_text` parameters to the `getMedia` function
- The `getMedia` function now properly outputs an image's alt text
- Added a CSS class to the post's featured image
- Constructed and styled the header for the front end
- Created a function that fetches a widget from the database
- Created a front end `script.js` file
- Included jQuery in the theme header and the front end scripts in the theme footer
- Tweaked documentation in the front end `style.css` file
- The front end header and menu are functional and mobile responsive

**Modified files:**
- 404.php (M)
- content/footer.php (M)
- content/header.php
- includes/class-post.php (M)
- includes/css/style.css
- includes/functions.php
- includes/globals.php
- includes/js/script.js (N)

----------------------------------------------------------------------------------------------------
## Version 2.2.0[a] (2019-12-13)

- Tweaked documentation in the `Profile` class
- Reduced the refresh delay on the `Profile::resetPassword` form from 3 seconds to 2 seconds
- The site logo and site icon image ids are now cast to integers in the `Settings::designSettings` function
- The `page` value is now removed from the submitted settings data after it is used in a conditional statement (it is not needed after this point)
- Added the delete modal to the "List User Roles" page
- A home icon is now displayed next to the site title on the admin dashboard instead of the site logo
- The global `getOnlineUser` function now makes use of the `getMedia` function to fetch the user's avatar
- Tweaked the styling of the admin dashboard header
- Removed an unused styling rule from the setup/installation stylesheet
- Tweaked documentation in the admin `script.js` and `modal.js` files
- Replaced a standard anonymous function with an arrow function in the `modal.js` file
- Cleaned up some unnecessary code in the `modal.js` file
- Created a front-end function to fetch a post's id
- Added a `lang` attribute to the front end `header.php` file
- The favicon now displays on the front end
- Created a function that fetches post data via the `Post` class
- Moved the `formatDate` function to the `globals.php` file
- Created front-end functions to fetch data from each column of the posts table
- Added more content to the front end `header.php`, `index.php`, and `footer.php` files
- Renamed the `getMedia` function to `getMediaSrc` and created a new `getMedia` function that constructs an HTML tag for the media based on its type
- Tweaked styling of the thumbnail column of the "List Media" table
- Replaced all old occurences of `getMedia` with `getMediaSrc`
- Created the 404 (Not Found) error page
- A settings entry for `theme_color` is now created during installation
- Added a theme color setting to the "Design Settings" page
- Styled form `color` inputs

**Modified files:**
- 404.php
- admin/header.php
- admin/includes/class-post.php (M)
- admin/includes/class-profile.php (M)
- admin/includes/class-settings.php
- admin/includes/class-user.php (M)
- admin/includes/css/install.css (M)
- admin/includes/css/install.min.css (M)
- admin/includes/css/style.css
- admin/includes/functions.php (M)
- admin/includes/js/modal.js
- admin/includes/js/script.js (M)
- content/footer.php
- content/header.php
- content/index.php
- includes/class-post.php
- includes/functions.php
- includes/globals.php
- login.php (M)

----------------------------------------------------------------------------------------------------
## Version 2.1.11[a] (2019-12-07)

- Improved styling of the setup and installation forms
- Created minified versions of several CSS files
- Renamed `buttons.css` to `button.css`
- The setup and installation pages now load minified resources
- Renamed `fa-icons.css` to `font-awesome.min.css`
- All admin pages now load some minified resources (the rest will be changed later)
- The log in and reset password pages now load some minified resources (the rest will be changed later)
- The log in and reset password page titles now display the page name before the site name
- Created a function that fetches the title of an admin page
- Tweaked documentation in the `getCurrentPage` function
- Added a "Design Settings" page link to the admin menu
- Created the "Design Settings" page
- Renamed the `feat-image-wrap` CSS class to `image-wrap`
- Cleaned up some code in the `Profile::editProfile` function
- Settings entries for `site_logo` and `site_icon` are now created during installation
- Cleaned up some code in the `User::createUser` and `User::editUser` functions
- A notice is now displayed on the media library tab of the upload modal if the media library is empty
- Files uploaded through the upload modal can now be inserted into a post's content
- Updated the upload functionality to accomodate multiple image fields on the same page
- The site logo is now displayed on the admin header bar
- The site icon is now displayed on the page tab
- Moved the `getMedia` function from the admin `functions.php` file to the `globals.php` file
- The site icon and site logo are now both displayed on the log in and reset password pages
- When a user's profile form is submitted, it now refreshes after only 2 seconds (reduced from 3)

**Modified files:**
- admin/header.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-user.php
- admin/includes/css/install.css
- admin/includes/css/install.min.css (N)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/js/script.js
- admin/install.php (M)
- admin/settings.php
- admin/setup.php (M)
- includes/css/button.css (R)
- includes/css/button.min.css (N)
- includes/css/font-awesome.min.css (R)
- includes/css/style.css
- includes/globals.php
- login.php

----------------------------------------------------------------------------------------------------
## Version 2.1.10[a] (2019-12-06)

- The `globals.php` file is now included in the `install.php` file
- Reordered properties on the header CSS file imports in the `setup.php` and `install.php` files (this is a cosmetic change only)
- Tweaked documentation in the `Query` class
- Tweaked styling of form elements
- Rearranged some elements on the `Profile::resetPassword` form and added a new spacer class to the line break tag
- Rearranged some elements on the `User::createUser`, `User::editUser`, and `User::resetPassword` forms and added a new spacer class to the line break tag
- Whitelisted more properties in the `formTag` function
- Data atrributes can now be used in the `formTag` function on input tags
- Tweaked and rearranged some elements on the `Post::createPost` and `Post::editPost` forms
- The content fields of the `Post::createPost` and `Post::editPost` forms are now slightly taller
- Added a hidden "media type" field to the media tab of the upload modal
- Tweaked the way the upload modal handles set up (launch) and clean up (closing)
- Added "mime type" and "alt text" data fields to the media items' info
- Tweaked code in the `uploadMediaFile` function
- When a media item is uploaded via the upload modal, its title will now be derived from the slug
- Media in the media library can now be inserted into a post's content
- Tweaked styling of the admin nav menu
- Improved styling of the "Ocean" and "Sunset" admin themes

**Modified files:**
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/modal-upload.php (M)
- admin/install.php
- admin/setup.php (M)
- content/admin-themes/ocean.css
- content/admin-themes/sunset.css
- includes/class-query.php (M)

----------------------------------------------------------------------------------------------------
## Version 2.1.9[a] (2019-12-04)

- Wrapped some null coalescing operations in parentheses in the `Post` class
- Created a new admin theme named "Harvest" (it uses the old color scheme of the "Sunset" theme)
- Updated color scheme for the "Sunset" theme
- Updated some CSS classes and ids in all of the admin classes
- Reordered a function in the `Post` class
- Added JavaScript form validation
- Tweaked the styling of checkboxes on forms
- Changed the access for the `Post::getAuthor` function from `private` to `protected`
- Replaced the "Alt Text" column on the "List Media" table with an "Author" column and reordered the "Upload Date" column
- Added more documentation to the `Query` class
- Tweaked a previous entry in the changelog
- Created a global constant that holds the minimum required PHP version
- Added a check to make sure the minimum PHP version or higher is being run on the web server RSCMS is installed on

**Modified files:**
- admin/includes/class-category.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/js/script.js
- content/admin-themes/harvest.css (N)
- content/admin-themes/sunset.css
- includes/class-query.php
- init.php

----------------------------------------------------------------------------------------------------
## Version 2.1.8[a] (2019-11-27)

- Tweaked documentation in the `modal.js` file
- Changed the `modal-launch` id to a class to work with all modal windows
- Constructed and styled the delete modal
- Added the delete modal to the "List Users" page
- The upload modal's tabs are now only cleared if the non-active tab is clicked
- Added extra checks in the `modal.js` file to determine which modal is being used (delete or upload)
- Added the delete modal to the "List Widgets" page
- Tweaked documentation in the `Profile` class
- Added the delete modal to the "List Posts" page
- Tweaked the styling of the upload modal's upload tab
- Added the delete modal to the "List Categories" page
- Tweaked documentation in the `Media` class
- Tweaked the status messages for the "List Media" page
- Added the delete modal to the "List Media" page
- Added the delete modal to the "List Menus" page
- Menu item types are now displayed next to their titles
- A new menu item's author is now set to the currently logged in user when it is created
- An empty string will now be returned by the `formTag` function if the tag name is not whitelisted
- Added the `a` tag to the list of whitelisted tags in the `formTag` function
- Tweaked styling of the "Edit Menu Item" form
- Moved the menu item delete button to within the edit form
- Removed a status message that displayed when a menu item was successfully edited (the page refreshes immediately so the message is not needed)
- The menu item parents dropdown is now sorted by item index

**Modified files:**
- admin/includes/class-category.php
- admin/includes/class-media.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/modal-delete.php (N)

----------------------------------------------------------------------------------------------------
## Version 2.1.7[a] (2019-11-25)

- Rearranged code and tweaked documentation in the admin `formTag` function
- Added styling for the upload modal to the admin "Ocean" theme
- Tweaked styling of the upload modal in the default theme
- Added styling for disabled buttons
- The "Select Media" button on the upload modal is now disabled by default (it is reenabled if a file is uploaded or if a file is selected from the media library
- Active data is now cleared when switching between the modal tabs
- Added failure conditions for deleting media: if the media is currently a user's avatar or a post's featured image

**Modified files:**
- admin/includes/class-media.php
- admin/includes/css/style.css (M)
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/modal-upload.php (M)
- content/admin-themes/ocean.css
- includes/css/buttons.css

----------------------------------------------------------------------------------------------------
## Version 2.1.6[a] (2019-11-24)

- Created a function to delete media from the database and the uploads folder
- Tweaked an entry in the changelog
- Tweaked styling of the upload tab of the upload modal
- Moved the `Media::filenameExists` and `Media::getUniqueFilename` functions to the admin `functions.php` file
- Tweaked the layout of the upload modal's upload form
- Media can now be uploaded via the upload modal
- Uploaded images can now be inserted as featured images and as avatars

**Modified files:**
- admin/includes/class-media.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/modal-upload.php
- admin/media.php
- admin/upload.php (N)

----------------------------------------------------------------------------------------------------
## Version 2.1.5[a] (2019-11-23)

- Added a metadata entry for the sample page and post's featured image in the `populateTables` function
- The "tag" argument is now removed from the args array after being assigned to its own variable in the `formRow` function
- Completely rebuilt the `formTag` function to make the code cleaner and more efficient
- Added the "remove image" button to the user profile page
- Tweaked styling for featured images and avatars
- Tweaked mobile responsive styling of media item details on the modal
- Avatars and featured images can now be removed
- Added a missing space in the `buttons.css` file
- If a new image is selected after the "remove image" button has been clicked, the greyed out effect is removed from the thumbnail
- Added the "remove image" button to the "Create User" and "Edit User" pages
- Moved the `User::getAvatar` function to the admin `functions.php` file and renamed it to `getMedia`
- Tweaked documentation in the `Post` class
- The "remove image" button in the `Post::createPost` function is now constructed with the `formTag` function
- Featured images can now be selected on the "Edit Post" form
- Tweaked documentation in the `Category` and `Settings` classes
- Tweaked documentation in the admin `functions.php` file
- Tweaked some entries in the changelog

**Modified files:**
- admin/includes/class-category.php (M)
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/script.js
- includes/css/buttons.css (M)

----------------------------------------------------------------------------------------------------
## Version 2.1.4[a] (2019-11-21)

- Added a redirect for the `media` post type in the `posts.php` file (`posts.php?type=media` -> `media.php`)
- Tweaked documentation in the `load-media.php` file
- Removed an unnecessary set of parentheses in the `Media::uploadMedia` function
- Tweaked some text on the upload modal
- Tweaked documentation and changed an id name in the `modal.js` file
- Tweaked documentation in the admin `functions.php` file
- Media items are now loaded by date in descending order in the `loadMedia` function
- Tweaked styling and improved mobile responsiveness of the upload modal's elements
- Changed an id name in the `Profile::editProfile` function
- A thumbnail for a selected image is now displayed in the details pane of the upload modal
- Button text can no longer be selected
- The `getOnlineUser` function now fetches the user's avatar
- The user's avatar now displays on the admin menu bar and on the user dropdown menu
- Users' avatars are now displayed on the "List Users" page
- Added the upload modal to the `User::createUser` and `User::editUser` functions
- Tweaked documentation in the `User` class
- The media form fields are now cleared if a user clicks the "select" button without selecting an image on the modal
- Added the upload modal to the `Post::createPost` function
- Created and styled a "remove image" button (not yet functional)

**Modified files:**
- admin/header.php
- admin/includes/class-media.php (M)
- admin/includes/class-post.php
- admin/includes/class-profile.php (M)
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/js/script.js
- admin/includes/modal-upload.php (M)
- admin/load-media.php (M)
- admin/posts.php
- includes/css/buttons.css (M)
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 2.1.3[a] (2019-11-20)

- A media item's details are now displayed when it is selected
- Tweaked styling on the modal
- Selected media items are now cleared when the modal is closed
- Moved the `Media::getFileSize` function to the admin `functions.php` file
- Created a function that converts a string or file's size to bytes
- Media items can now be selected and inserted on the user profile form
- Changed the access for the `User::getAvatar` function from `private` to `protected`
- Cleaned up code in the `User::getAvatar` function
- A user can now set their avatar from their own profile (through the media library only)

**Modified files:**
- admin/includes/class-media.php
- admin/includes/class-profile.php
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/modal-upload.php

----------------------------------------------------------------------------------------------------
## Version 2.1.2[a] (2019-11-18)

- Renamed some element classes on the upload modal
- Added a partially opaque backdrop to the modal when it's open
- Increased the top margin above the modal
- Improved transitioning effects for the modal
- Created a function that loads the media library
- Styled the media library tab of the upload modal

**Modified files:**
- admin/includes/class-profile.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js
- admin/includes/modal-upload.php
- admin/load-media.php (N)

----------------------------------------------------------------------------------------------------
## Version 2.1.1[a] (2019-11-13)

- An empty array is now created in the `Profile::getThemesList` if the `admin-themes` directory doesn't exist
- Tweaked documentation in the `Post` class
- Replaced a ternary operator with a null coalescing operator in the `Post::createPost` function
- Removed an unnecessary set of parentheses in the `Widget::createWidget` function
- Added the `id` parameter to the `img` tag in the `formTag` function
- Created a file to hold the upload modal's content
- The upload modal is now included on the user profile page
- Constructed and styled the upload modal
- Tweaked styling for buttons
- Added a "strict mode" declaration in the admin `script.js` file
- Created a file to hold scripts for the admin modal windows
- The jQuery library is now loaded in the admin head section instead of in the footer (prevents errors in script files included before the footer)

**Modified files:**
- admin/includes/class-post.php
- admin/includes/class-profile.php
- admin/includes/class-widget.php (M)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/modal.js (N)
- admin/includes/js/script.js (M)
- admin/includes/modal-upload.php (N)
- includes/css/buttons.css (M)

----------------------------------------------------------------------------------------------------
## Version 2.1.0[a] (2019-11-10)

- Added a link to the "List Media" page to the admin menu
- Created the "List Media" page and `Media` class
- Created a function that constructs a list of all media
- Created a function that constructs the "Upload Media" form
- Tweaked styling for file inputs
- Increased the width of text inputs
- Created a function that validates the media form data
- Created a function that checks whether a filename exists in the database
- Added `LIKE` to the list of accepted operators for where clauses
- Converted the operator `if/elseif` statment in the `Query::select` function to a `switch` statement
- Created a function that makes a filename unique if a matching filename is found in the database
- Replaced a ternary operator with a null coalescing operator in the `Widget::createWidget` function
- Shortened a regular expression in the `Login::validateLoginData` function by using the `\w` metacharacter
- Renamed a variable in the `Profile::getThemesList` function
- Changed the access for the `Post::getPostMeta` function from private to protected
- Created a function that converts a file size from bytes to a more manageable size (e.g., KB, MB, GB)
- Created a function that constructs the "Edit Media" form
- Tweaked documentation in the `Widget` class
- When a widget is created, its author is now set as the logged in user who created it
- When a menu item is created, its author is now set as the logged in user who created it
- Tweaked documentation in the `Menu` class

**Modified files:**
- admin/header.php (M)
- admin/includes/class-media.php (N)
- admin/includes/class-menu.php
- admin/includes/class-post.php (M)
- admin/includes/class-profile.php (M)
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/media.php (N)
- includes/class-login.php (M)
- includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 2.0.8[a] (2019-11-08)

- Tweaked documentation in the `globals.php` file
- Tweaked documentation in the admin `Profile` class
- Tweaked documentation in the admin `User` class
- During installation, a usermeta entry is now created for the user's admin theme
- When a new user is created, a usermeta entry is now created for the user's admin theme
- The `Profile::getThemesList` function now checks whether a file has the `css` extension and only includes it in the list if so
- The profile page now refreshes after 3 seconds instead of 4
- Tweaked the color of the user dropdown menu text
- Improved the design of the "Ocean" admin theme
- Changed the "From" header field in the "Forgot Password" email from the site's name to "ReallySimpleCMS"
- If the user does not check the "Keep me logged in" checkbox when they log in, the session cookie is now set to expire at the end of the browsing session (previously was 30 minutes)

**Modified files:**
- admin/includes/class-profile.php
- admin/includes/class-user.php
- admin/includes/css/style.css (M)
- admin/includes/functions.php (M)
- content/admin-themes/ocean.css
- includes/class-login.php
- includes/globals.php (M)

----------------------------------------------------------------------------------------------------
## Version 2.0.7[a] (2019-11-03)

- Shortened the version query string on static resources from "version" to "v" (this applies to both stylesheets and scripts)
- Tweaked documentation in the `includes/functions.php` file
- Tweaked documentation in the `Profile` class
- Created a function that constructs the "Reset Password" form (`Profile` class)
- Created a function that validates the "Reset Password" form data (`Profile` class)
- Cleaned up code in the `User::validatePasswordData` function
- Changed the access for the `User::verifyPassword` function from `private` to `protected`
- Replaced the `session_data` parameter with `id` in the `User::verifyPassword` function
- Cleaned up code in the `User::verifyPassword` function
- The `User::PW_LENGTH` constant's access is now `protected` (it was inadvertently set to `private`)
- The `User::validatePasswordData` function's second parameter is no longer optional
- If a redirect URL is provided on the "Log In" page, the user will be redirected upon logging in
- Created a function that loads all admin header scripts
- Created a function that constructs a list of all admin themes
- Added a field to the user profile form to allow users to select their own admin theme
- Created a function that fetches a theme-specific stylesheet (located in the `content` directory)
- Created a function that fetches a theme-specific script file (located in the `content` directory)
- Users can now load custom admin themes by placing stylesheets in the `content/admin-themes` directory
- Created a function that loads all admin footer scripts
- Created three alternate admin themes, named "Ocean", "Forest", and "Sunset"
- Tweaked styling of the user dropdown menu
- Created a constant to hold the minimum password lenth (`Login` class)

**Modified files:**
- admin/footer.php
- admin/header.php
- admin/includes/class-profile.php
- admin/includes/class-user.php
- admin/includes/css/style.css (M)
- admin/includes/functions.php
- content/admin-themes/forest.css (N)
- content/admin-themes/ocean.css (N)
- content/admin-themes/sunset.css (N)
- includes/class-login.php
- includes/functions.php (M)
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 2.0.6[a] (2019-10-30)

- Removed the "fetching cookie" code from the `login.php` file
- Tweaked several previous entries in the changelog
- Removed the `cookie_data` parameter from the `Login::resetPasswordForm` function
- Added a `security_key` column to the `users` table in the database schema
- Added a new constant that stores a cookie hash based on the site's URL
- Tweaked the styling for the formatted emails
- Cleaned up code in the `Login::validateLoginData` function
- The session value hash is now generated with the `generateHash` function
- Added an error to the `Login::forgotPasswordForm` function that displays if a password reset security key is invalid
- Cleaned up code in the `Login::validateForgotPasswordData` function
- The reset password email's "From" address is now `rscms@hostname` to prevent the possibility of an email being sent both from and to the same address
- The reset password cookie is no longer created in the `Login::validateForgotPasswordData` function
- Cleaned up code in the `Login::resetPasswordForm` function
- The reset password cookie is now created in the `Login::resetPasswordForm` function
- Cleaned up code in the `Login::validateResetPasswordData` function
- Created a function that checks whether a reset password cookie is valid
- Added an error to the `Login::forgotPasswordForm` function that displays if a password reset security key has expired
- Tweaked documentation in the front end `style.css` file
- Changed the access for the `User::UN_LENGTH` and `User::PW_LENGTH` constants from `public` to `protected`
- Created a function that checks whether an email exists in the database (`User` class)
- Cleaned up code in the `User::usernameExists` function
- The delete action no longer displays on the "List Users" page for the current user
- Added output buffering to the `admin/header.php` and `admin/footer.php` files (this prevents errors with certain redirects)
- Users can no longer delete themselves
- The user's profile will now refresh after it's updated
- Changed the access for the `User::usernameExists` function from `private` to `protected`
- Created a function that validates the user profile form

**Modified files:**
- admin/footer.php (M)
- admin/header.php (M)
- admin/includes/class-profile.php
- admin/includes/class-user.php
- includes/class-login.php
- includes/css/style.css (M)
- includes/functions.php
- includes/schema.php (M)
- login.php

----------------------------------------------------------------------------------------------------
## Version 2.0.5[a] (2019-10-28)

- Created a function that validates the "Forgot Password" form data and sends a reset password email
- Created a function that formats an email with HTML and CSS
- Created a function that generates a random hash
- The user will now be redirected back to the "Log In" form after they successfully submit the "Forgot Password" form
- A confirmation is now displayed over the "Log In" form after the "Forgot Password" form is submitted
- Tweaked documentation in the `login.php` file
- Renamed the `Login::loginForm` function to `Login::logInForm`
- Moved the `generatePassword` function from the `admin/functions.php` file to the `globals.php` file
- Tweaked the `generatePassword` function's internal code
- Created a function that constructs the "Reset Password" form
- Created a function that validates the "Reset Password" form data
- A confirmation is now displayed over the "Log In" form after the "Reset Password" form is submitted

**Modified files:**
- admin/includes/functions.php
- includes/class-login.php
- includes/functions.php
- includes/globals.php
- login.php

----------------------------------------------------------------------------------------------------
## Version 2.0.4[a] (2019-10-27)

- Tweaked documentation in the `globals.php` file
- Added an optional parameter to the global redirect function to specify the HTTP status for a redirect
- Renamed the `Login::errorMessage` function to `Login::statusMessage` and added an optional parameter to specify whether the message should show success or failure
- Added styling for success status messages
- Tweaked documentation in the front end stylesheet
- Tweaked some styling on the "Log In" form

**Modified files:**
- includes/class-login.php
- includes/css/style.css
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 2.0.3[a] (2019-10-24)

- Added a `switch` statement to the `login.php` file for actions
- The page title will now change based on the current action
- Created a function that constructs the "Log In" form
- Renamed the `Login::userLogin` function to `Login::validateLoginData`
- Rearranged some code in the `Login::validateLoginData` function
- The `Login::validateLoginData` and `Login::isValidPassword` functions now only check for the `@` character to determine if the login is an email
- The `Login::sanitizeData` function now strips off HTML and PHP tags from strings and it accepts integer values for `filter_var`
- The email is now sanitized with the `FILTER_SANITIZE_EMAIL` filter
- Added a "Forgot your password?" link below the "Log In" form
- Created a function that constructs the "Forgot Password" form

**Modified files:**
- includes/class-login.php
- includes/css/style.css
- login.php

----------------------------------------------------------------------------------------------------
## Version 2.0.2[a] (2019-10-18)

- Added output buffering to the login page (prevents errors that may occur with cookie creation)
- Tweaked documentation in the `login.php` file
- Turned off autocompletion on the captcha field of the login form
- Tweaked documentation in the `globals.php` file
- Created a function that checks whether a specific session already exists in the database
- Added an optional parameter to the `Login::sanitizeData` function that accepts regex patterns
- Renamed the `username_email` input on the login form to `login`
- Created a function that checks whether a specific email address already exists in the database
- Renamed the `username` parameter in the `Login::isValidPassword` function to `login`
- A user can now log in with their email in addition to their username
- The "keep me logged in" checkbox now works (cookie is saved for 30 days)
- Tweaked documentation in the following files:
  - `admin/header.php`
  - `admin/menus.php`
  - `admin/users.php`
  - `admin/widgets.php`

**Modified files:**
- admin/header.php (M)
- admin/menus.php (M)
- admin/users.php (M)
- admin/widgets.php (M)
- includes/class-login.php
- includes/globals.php (M)
- login.php

----------------------------------------------------------------------------------------------------
## Version 2.0.1[a] (2019-10-17)

- Tweaked documentation in the `captcha.php` file
- Created a function that sanitizes user input data from the login form
- A session cookie is now created when the user logs in instead of a session array
- Created a function that checks whether a user is properly logged in
- Created a function that fetches an online user's data
- The session data is now set based on the session cookie
- Replaced all occurences of the `$_SESSION` superglobal with the session array
- Created a function that logs the user out
- The user is now redirected to the login page from the admin dashboard if they're logged out

**Modified files:**
- admin/categories.php
- admin/header.php
- admin/menus.php
- admin/posts.php
- admin/profile.php
- admin/settings.php
- admin/users.php
- admin/widgets.php
- includes/captcha.php (M)
- includes/class-login.php
- includes/globals.php
- login.php

----------------------------------------------------------------------------------------------------
## Version 2.0.0[a] (2019-10-16)

- When menu items are created, their slugs are now initially set to an empty string instead of null
- Pages and posts that are in the trash can no longer be used as menu item links
- Tweaked documentation in the `Post` class
- Updated the Font Awesome CSS to version 5.11.2
- Built and styled the user dropdown menu
- Created the user profile page and the `Profile` class
- Tweaked documentation in the `Widget` class
- Created a function that constructs the "Edit Profile" form
- Changed the access for the `User::getUserMeta` function from `private` to `protected`
- Tweaked documentation in the root `index.php` file
- Tweaked the styling of the login form
- Created the `Login` class
- Created a function that constructs error messages for the login form
- Increased the default value for the `generatePassword` function's `lenth` parameter from 15 to 16
- Removed the space character from the list of special characters in the `generatePassword` function
- Created a function that checks whether a captcha value is valid
- Created a function that checks whether a password is valid
- Tweaked documentation in the `User` class
- Added the `start_session` function to the `captcha.php` file and the `admin/header.php` file
- Created a function that validates the login data and logs the user in

**Modified files:**
- admin/header.php
- admin/includes/class-menu.php
- admin/includes/class-post.php (M)
- admin/includes/class-profile.php (N)
- admin/includes/class-user.php (M)
- admin/includes/class-widget.php (M)
- admin/includes/css/style.css
- admin/includes/functions.php (M)
- admin/profile.php (N)
- includes/captcha.php (M)
- includes/class-login.php (N)
- includes/css/fa-icons.css
- includes/css/style.css
- index.php (M)
- login.php

----------------------------------------------------------------------------------------------------
## Version 1.8.12[a] (2019-10-13)

- Changed the default values of some columns in the database schema to avoid issues when using phpMyAdmin's strict mode
- Created a function that fetches a menu item's parent
- Created a function that checks whether a menu item has siblings
- Created a function that checks whether a menu item is the first of its siblings
- Created a function that checks whether a menu item is the last of its siblings
- Created a function that checks whether a menu item is the previous sibling of another menu item
- Created a function that checks whether a menu item is the next sibling of another menu item
- Created a function that fetches the previous sibling of a menu item
- Menu items are properly reordered when a menu item is moved up
- The `Menu::isSibling` function is now deprecated
- Created a function that fetches the next sibling of a menu item
- Menu items are properly reordered when a menu item is moved down
- Tweaked styling for form `textarea` fields

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/css/style.css
- includes/deprecated.php
- includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.8.11[a] (2019-10-10)

- Updated various functions in the `Post` and `User` classes to make use of the new `Query::selectField` function
- Changed the access for the `Post::getAuthor` and `Post::getAuthorList` functions from `protected` to `private`
- Updated a function in the `admin/functions.php` file to make use of the new `Query::selectField` function
- Added more documentation to the `admin/functions.php` file
- Added a full filepath for autoloaded classes in the `includes/functions.php` file
- Updated some functions in the `globals.php` file to make use of the new `Query::selectField` function
- A menu item's children will now have their parent updated when that menu item is deleted

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-user.php
- admin/includes/functions.php
- includes/functions.php (M)
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.8.10[a] (2019-10-08)

- Created a function that selects only a single field from the database and returns it
- Added more documentation to the `Query` class
- Changed the default value for the `where` parameter from `''` to `array()` in all `Query` functions that use it
- Updated various functions in the `Category`, `Menu`, and `Settings` classes to make use of the new `Query::selectField` function
- Cleaned up some code in the `Menu` class

**Modified files:**
- admin/includes/class-category.php
- admin/includes/class-menu.php
- admin/includes/class-settings.php
- includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.8.9[a] (2019-10-07)

- Fixed a styling issue with the custom menu link inputs
- Menu items are now properly reordered when a menu item's parent is set (now works in all cases)
- Fixed the menu item parents dropdown to only display menu items from the current menu
- Created a function that checks whether a menu item is a sibling of another menu item
- Menu items now cannot be reordered beyond the range of their siblings (i.e., a child cannot be given a lower index than its parent)

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.8.8[a] (2019-10-02)

- Added the "menu-item" class to the list item that displays if the menu is empty
- Menu items are now properly reordered when a menu item's parent is set or unset (in most cases)
- Added more documentation to the `Query` class
- Widened text inputs
- Changed padding for buttons from pixels to ems

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/css/style.css (M)
- includes/class-query.php
- includes/css/buttons.css (M)

----------------------------------------------------------------------------------------------------
## Version 1.8.7[a] (2019-09-29)

- Child menu items are now indented on the "Edit Menu" page (up to 3 levels deep)
- Created a function that fetches the whole "family tree" of a menu item and returns the number of members
- Added a global variable to the `Menu` class to hold the member count of a menu item's "family tree"
- Created a function that fetches all descendants of a menu item
- Replaced an occurence of `intval()` with a casted integer in the `install.php` file
- Added more documentation to the `Query` class

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/css/style.css
- admin/install.php (M)
- includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.8.6[a] (2019-09-28)

- Tweaked a line of documentation in the `adminNavMenuItem` function
- Created a function that constructs a list of parent menu items
- Created a function that checks whether the current menu item is a descendant of other menu items
- Menu items can now be nested
- Created a function that determines the nested depth of a menu item

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.8.5[a] (2019-09-20)

- Tweaked a previous entry in the changelog
- Added a line of documentation to the `schema.php` file
- Tweaked documentation and updated a constant in the `init.php` file
- A notice will now be displayed if the content directory's `index.php` file is accessed directly
- Added more documentation to the content `index.php` file
- Added Font Awesome icons
- Tweaked documentation in the `includes/css/style.css` stylesheet
- Included the Font Awesome stylesheet in the `admin/header.php` file
- Added a parameter to the `adminNavMenuItem` function to include an icon
- Added styling for the icons
- Adjusted the width, font size, and margins of the admin nav menu and menu items
- Made the admin nav menu mobile responsive
- Made several other elements mobile responsive

**Modified files:**
- admin/header.php
- admin/includes/css/style.css
- admin/includes/functions.php
- content/index.php
- includes/css/fa-icons.css (N)
- includes/css/style.css (M)
- includes/fonts/fa-brands.ttf (N)
- includes/fonts/fa-regular.ttf (N)
- includes/fonts/fa-solid.ttf (N)
- includes/schema.php (M)
- init.php

----------------------------------------------------------------------------------------------------
## Version 1.8.4[a] (2019-09-15)

- Added more documentation to the root `index.php` file and removed a closing PHP tag
- Minor tweak to the content `index.php` file
- Added an extra parameter to the `Menu::deleteMenuItem` function
- When a menu item is deleted, the indexes of any other menu items are now reordered properly
- Added some documentation to the `Query` class
- The CMS now checks whether all database tables are accounted for on initialization
- Existing tables are now dropped during installation if there are tables missing (they will not be deleted in a proper installation)
- Tweaked the text on the installation form
- Tweaked some previous entries in the changelog

**Modified files:**
- admin/includes/class-menu.php
- admin/install.php
- content/index.php (M)
- includes/class-query.php
- index.php
- init.php

----------------------------------------------------------------------------------------------------
## Version 1.8.3[a] (2019-09-14)

- Added a disabled input field that displays the menu item's type (post/page, category, or custom)
- Added styling for disabled input fields
- Renamed the `Menu::getPostsList` function to `Menu::getMenuItemsList`
- Category menu items can now be edited
- Custom links can now be added to menus
- Custom menu items can now be edited
- Menu items can now be sorted up (to a lower index) and down (to a higher index)
- Removed an old, unused test function from the `includes/functions.php` file
- Added an exit function after a redirect in the `init.php` file
- Updated some documentation in the `login.php` file
- Tweaked some previous entries in the changelog

**Modified files:**
- admin/includes/class-menu.php
- admin/includes/css/style.css
- includes/functions.php
- init.php (M)
- login.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.8.2[a] (2019-09-13)

- Fixed a typo and escaped some special characters in the changelog
- Tweaked the documentation in all the admin files
- Renamed all public functions with "entry" or "entries" in their name to the name of their class (e.g., `User::listEntries` -> `User::listUsers`)
- Added a redirect from the "List Posts" page to the "List Menus" page if the requested post's type is `nav_menu_item`
- Renamed the `Menu::getMenuItemsList` function to `Menu::getMenuItemsLists` and removed its optional parameter
- Cleaned up the `Menu::getMenuItemsLists` function and split pages and posts into separate fieldset lists
- Added styling to the menu items fieldsets
- Added a checkbox list for categories and fields for adding custom menu items
- Categories can now be added to menus

**Modified files:**
- admin/categories.php
- admin/includes/class-category.php
- admin/includes/class-menu.php (M)
- admin/includes/class-post.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/index.php (M)
- admin/menus.php (M)
- admin/posts.php
- admin/settings.php (M)
- admin/users.php
- admin/widgets.php

----------------------------------------------------------------------------------------------------
## Version 1.8.1[a] (2019-09-09)

- Tweaked some documentation in the `Post` class
- The count value now increments when a new menu is created with menu items
- Switched a margin from the data form content block to the metadata block
- Fixed an issue where the "Categories" block in the post editor still was captioned "Attributes" (type `post` only)
- Added styling for new a `item-list` class
- Updated the menu forms to more closely resemble the post forms (their functionality still remains different)
- Rebuilt the `Menu::getMenuItems` function for use on the menu forms pages
- Added extra validation to the `Category::deleteEntry` function
- Menus can now be edited and deleted
- Added a wrapper element for data forms (allows for floating blocks outside of the main form)
- Replaced occurrences of `count() === 0` with `empty()` when checking if no entries exist on the list entries pages
- Set all form elements to use the `Segoe UI` font (including the installation forms)
- Styled the reset password button on the 'Edit User' form
- Added the admin footer to the menus page
- Renamed all public menu functions (e.g., `Menu::createEntry` -> `Menu::createMenu`)
- Renamed the `Menu::validateData` function to `Menu::validateMenuData`
- Created a function that constructs the 'Edit Menu Item' form
- Created a function that constructs a list of posts (`Menu` class)
- Created a function to fetch a menu item's metadata
- Menu items can now be edited and deleted
- A cancel button will now appear when a menu item is being edited

**Modified files:**
- admin/includes/class-category.php
- admin/includes/class-menu.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/install.css (M)
- admin/includes/css/style.css
- admin/menus.php

----------------------------------------------------------------------------------------------------
## Version 1.8.0[a] (2019-09-08)

- Fixed a bug with the `pagerNav` function that added too many `paged` parameters
- Fixed the pagination on the 'List User Roles' page
- Created the `Menu` class
- Created an admin "List Menus" page and added a link to the nav menu
- Fixed an issue with the `getCurrentPage` function that prevented the "Create Menu" page from displaying as the current page
- The `nav_menu` taxonomy is now created during installation
- Created a function that constructs the 'Create Menu' form
- Created a function that constructs a list of nav menu items
- Renamed the `privileges-list` id to the `checkbox-list` class
- Created a function that checks whether the `nav_menu` slug already exists
- Created a function that constructs the "Edit Menu" form
- Created a function to fetch the nav menu items
- Menus can now be created

**Modified files:**
- admin/header.php (M)
- admin/includes/class-menu.php (N)
- admin/includes/class-settings.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/menus.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.7.5[a] (2019-09-07)

- Tweaked documentation in the `admin/users.php` file
- The categories pages now check whether a logged in user has sufficient privileges to view the pages
- The posts pages now check whether a logged in user has sufficient privileges to view the pages
- The `Category::getParent` function now returns an em dash if a category has no parent
- The default user roles now display in a separate list below user-created roles
- Added styling for subheadings
- Renamed the `current` parameter in the `pagerNav` function to `page`
- Improved the functionality of the `pagerNav` function

**Modified files:**
- admin/categories.php
- admin/includes/class-category.php
- admin/includes/class-settings.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/posts.php
- admin/users.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.7.4[a] (2019-09-03)

- Tweaked documentation in the `Post` class
- Adjusted the margins on list entries pages when a status message is displayed
- Added a default value for the `_default` column in the `user_roles` database table
- Added a full file path to the `autoload` class function
- Fixed an issue with the `getCurrentPage` function that prevented the "Create Widget" page from displaying as the current page
- Added more documentation to the admin functions
- Tweaked documentation in the `User` class
- When creating a new user, the role dropdown now displays the default user role
- Changed an HTML id to a class
- Added the delete link for users on the "List Users" page
- Tweaked the default site description in the `populateTables` function
- Tweaked the styling of form tables
- The widgets pages now check whether a logged in user has sufficient privileges to view the pages
- The users pages now check whether a logged in user has sufficient privileges to view the pages

**Modified files:**
- admin/includes/class-post.php (M)
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/users.php
- admin/widgets.php
- includes/schema.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.7.3[a] (2019-09-02)

- Tweaked the deleted entry status message for all classes
- Replaced all occurrences of the PHP `header` function with the new `redirect` function in the `User` and `Widget` classes
- Replaced a `null` comparison and `empty` with `is_null` in the `User::listEntries` function
- The "Full Name" column on the "List Users" page now displays an em dash if the user has no first name or last name
- The `Post::getParent` function now returns an em dash if a post has no parent
- Fixed some issues with the `getCurrentPage` function that caused some pages not to show as the current nav menu item
- Added a `_default` column to the `user_roles` database table (this will be used to protect default roles from tampering)
- Removed edit and delete actions from default user roles on the "List User Roles" page
- Attempting to edit a default user role will now redirect the user to the "List User Roles" page

**Modified files:**
- admin/includes/class-category.php (M)
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/functions.php
- includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.7.2[a] (2019-09-01)

- Changed the pagination *page* `GET` varible to *paged* to differentiate it from the settings *page* `GET` variable
- Added more documentation to the admin functions
- The `can_view_user_roles` privilege is now created on installation
- Created a global function that checks whether a user has a specified privilege (to protect certain pages from users with limited privileges)
- Created a global function for easy url redirection
- Added a temporary `$_SESSION` variable to the admin header (to test user roles/privileges)
- The settings pages now check whether a logged in user has sufficient privileges to view the pages
- Created a function that fetches user privileges
- Tweaked some documentation in the `Post` class
- The `Post::getCategories` function now returns an em dash if a post has no categories
- Renamed the `Settings::userRolesSettings` function to `Settings::listUserRoles`
- Renamed the `Settings::validateData` function to `Settings::validateSettingsData`
- Created a function that constructs the "Create User Role" form
- The `formRow` function now can accept string values in its `args` parameters
- Cleaned up the `formRow` function's code and added more documentation
- Added styling for the user privilege list
- Tweaked styling of the post category list
- Created a function that constructs a list of user privileges
- Created a function that constructs the "Edit User Role" form
- Created a function that validates the user role form data
- Replaced all occurrences of the PHP `header` function with the new `redirect` function in the `Post` and `Category` classes
- Fixed an issue where categories would get deleted from a post unnecessarily
- Created a function that deletes user roles

**Modified files:**
- admin/header.php (M)
- admin/includes/class-category.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-user.php (M)
- admin/includes/class-widget.php (M)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/settings.php
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.7.1[a] (2019-08-30)

- Tweaked some documentation in the `Post` class
- Tweaked how the category post count is calculated when a post is created
- The `user_privileges` database table is now populated during installation
- The `user_relationships` database table is now populated during installation
- Added the `clear` class to all admin page wrapper elements (prevents page content from overflowing into the footer)
- Removed a line of documentation from the `categories.php` file
- Renamed the `Settings::listSettings` function to `Settings::generalSettings`
- Created the "User Roles" settings page
- Added a nav menu item for the user roles settings page
- Added an extra check in the `getCurrentPage` function to look for the page `GET` parameter (for settings pages)

**Modified files:**
- admin/categories.php (M)
- admin/header.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/functions.php
- admin/index.php (M)
- admin/posts.php (M)
- admin/settings.php
- admin/users.php (M)
- admin/widgets.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.7.0[a] (2019-08-24)

- Changed possible statuses for widgets from `draft` and `published` to `active` and `inactive`
- Added a warning to all admin pages if the user has JavaScript disabled in their browser
- Added a redirect from the "List Posts" page to the "List Widgets" page if the requested post's type is `widget`
- Added a redirect from the "Edit Post" page to the "Edit Widget" page if the requested id corresponds to a widget
- Renamed the following database tables:
  - `roles` -> `user_roles`
  - `privileges` -> `user_privileges`
  - `rp_relationships` -> `user_relationships`
- The `user_roles` database table is now populated during installation
- Consolidated all database table populate functions into one and moved the old functions to the `deprecated.php` file
- The user created on installation is now given the administrator user role
- Replaced the `UPL_DIR` constant with `UPLOADS` in the `User::getAvatar` function (the former is no longer used)
- Created a function that constructs a list of user roles (`Settings` class)
- Tweaked the username column's styling on the "List Users" page
- Created a function that fetches a specified user's role (`User` class)
- Created a function that constructs a list of user roles (`User` class)
- The `Post::getAuthorList` function no longer calls the `Post::getAuthor` function
- The `Post::getParentList` function no longer calls the `Post::getParent` function
- User roles now appear on the "Create User" and "Edit User" forms
- Added a missing class to a button on the "Reset Password" form
- Fixed the alignment of the `pass_saved` labels on the "Create User" and "Reset Password" forms
- The `Category::getParentList` function no longer calls the `Category::getParent` function

**Modified files:**
- admin/header.php
- admin/includes/class-category.php
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/install.php
- admin/posts.php
- includes/deprecated.php
- includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.6.3[a] (2019-08-22)

- Tweaked documentation in the `Post` class
- Improved validation for the "Edit Post" form
- Widgets can no longer be edited via the "Edit Post" form
- Fixed some errors that would pop up if a post's id was invalid
- Tweaked the pagination code for the `User` class
- A notice is now displayed on the "List Users" page if no users exist in the database
- Tweaked documentation in the `User` class
- Improved some styling of elements on the installation form
- Added and renamed some CSS classes on the installation page
- Minor CSS cleanup in the admin `style.css` stylesheet
- The `page.php` file is now deprecated
- Updated documentation in the `config-setup.php` and `captcha.php` files
- Updated documentation in the `Query` class

**Modified files:**
- admin/includes/class-post.php
- admin/includes/class-user.php
- admin/includes/css/install.css
- admin/includes/css/style.css (M)
- admin/includes/functions.php
- admin/install.php
- includes/captcha.php (M)
- includes/class-query.php
- includes/config-setup.php (M)
- page.php (D)

----------------------------------------------------------------------------------------------------
## Version 1.6.2[a] (2019-08-20)

- Fixed an issue with the "Search Engine Visibility" checkbox label that prevented the checkbox from being checked
- Removed the jQuery library and script from the installation page (no longer needed since clicking the checkbox now works)
- Renamed a CSS class on the installation page
- Changed the default value for the `getStylesheet` and `getScript` functions' version parameter to the `VERSION` constant
- Added more documentation to the `globals.php` file
- Cleaned up the `trimWords` function
- Changed the default value for the `getAdminStylesheet` and `getAdminScript` functions' version parameter to the `VERSION` constant
- Added more documentation to the admin functions
- Tweaked the styling of data form tables
- An error will no longer display if no content is specified for a `select` or `textarea` field in the `formTag` function
- Added a status field to the `Widget` class forms
- Tweaked documentation in the `Post` class
- Added form validation for widgets
- Modified date is now set when a post is edited
- Removed a line of documentation from the `widgets.php` file
- Tweaked the pagination code for the `Post` class
- Removed a few lines of documentation from the `Category` class
- Tweaked the styling of some elements on the "General Settings" page

**Modified files:**
- admin/install.php
- admin/includes/class-category.php (M)
- admin/includes/class-post.php
- admin/includes/class-settings.php
- admin/includes/class-widget.php
- admin/includes/css/install.css (M)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/widgets.php (M)
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.6.1[a] (2019-08-18)

- Fixed an issue with form submissions where old data would be fetched before the new data was submitted
- The text for the "Search Engine Visibility" checkbox on the installation form can now be used to check the checkbox
- Included the jQuery library on the installation page and added a custom script
- Added more documentation to the `Post` class
- Created a function that constructs the "Edit Widget" form

**Modified files:**
- admin/install.php
- admin/includes/class-category.php
- admin/includes/class-post.php
- admin/includes/class-user.php
- admin/includes/class-widget.php
- admin/includes/css/install.css
- admin/widgets.php

----------------------------------------------------------------------------------------------------
## Version 1.6.0[a] (2019-08-16)

- The admin nav menu now properly displays the current page, even if it's in a submenu
- Reorganized the admin nav menu items
- Improved styling of the admin nav menu
- The `getCurrentPage` function now adds any action in the url to the end of the current page
- Added more documentation to the `User` class and fixed the styling on the reset password form
- Renamed the data form's `hr` class from `divider` to `separator` and added styling to it
- Created the `Widget` class
- Created admin "List Widgets" page and added a link to the nav menu
- Fixed some documentation in the `Category` class
- Created a function that constructs the "Create Widget" form
- Tweaked the documentation in the `install.css` stylesheet

**Modified files:**
- admin/header.php
- admin/includes/class-category.php
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/class-widget.php (N)
- admin/includes/css/install.css (M)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/widgets.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.5.7[a] (2019-08-15)

- Improved styling of the statistics graph
- Reordered the bars in the statistics graph
- Rebuilt the admin submenu item functionality
- Removed a closing PHP tag in the `admin/settings.php` file
- Tweaked some code in the `setup.php` file
- Tweaked some code and fixed some documentation in the `install.php` file
- The CMS version constant now only displays the version number (e.g., 1.5.7); the `RSVersion` function now includes the long form
- Added the CMS version to the admin stylesheets and scripts
- Fixed some documentation in the `globals.php` file
- Removed a closing PHP tag in the `admin/index.php` file
- Tweaked some code in the `Settings` and `User` classes

**Modified files:**
- admin/footer.php (M)
- admin/header.php
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php (M)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/index.php (M)
- admin/install.php
- admin/settings.php (M)
- admin/setup.php (M)
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.5.6[a] (2019-08-14)

- A notice will now be displayed on the "List Categories" page if there are no categories in the database
- Minor code tweak in the `Post` class
- Adjusted the margins for status messages
- Removed some deprecated code from the `admin/functions.php` file
- Changed some class and id names for the statistics graph
- Added styling to the statistics graph
- Created a file for admin scripts
- Created a file for the jquery library
- Added admin scripts and jquery to the `admin/footer.php` file
- Added an optional version parameter to stylesheet and script fetching functions
- Created a script that generates the bars for the statistics graph

**Modified files:**
- admin/footer.php
- admin/includes/class-category.php
- admin/includes/class-post.php (M)
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/includes/js/script.js (N)
- includes/globals.php
- includes/js/jquery.min.js (N)

----------------------------------------------------------------------------------------------------
## Version 1.5.5[a] (2019-08-05)

- Created an `index.php` file for the `content` directory
- Created functions that include theme header and footer files
- Moved the front end `header.php` and `footer.php` files to the `content` directory
- The root `index.php` file now includes the `content/index.php` file
- Created a new defined constant for the `content` directory
- Updated documentation in the `deprecated.php` file

**Modified files:**
- content/footer.php (N)
- content/header.php (N)
- content/index.php (N)
- footer.php (X)
- header.php (X)
- includes/deprecated.php
- includes/functions.php
- index.php
- init.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.5.4[a] (2019-08-04)

- When a post is deleted, its term relationships are also deleted and the category counts are updated
- Added the `button` class to the pager nav links
- Styled the pager nav buttons
- Tweaked the styling of the entry count
- Created a function that fetches the current admin page
- The admin `body` tag is now assigned a class based on the current page
- Changed some class names relating to the admin nav menu
- Renamed the `adminNavItem` function to `adminNavMenuItem`
- The admin nav menu now properly displays the current nav menu item
- Submenus are now visible if they are children of the current nav menu item

**Modified files:**
- admin/header.php (M)
- admin/includes/class-post.php
- admin/includes/css/style.css
- admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.5.3[a] (2019-08-02)

- Categories can now be removed from posts
- The "General Settings" form now has styling
- Tweaked the documentation of the `Settings` class
- Tweaked the styling of form tables

**Modified files:**
- admin/includes/class-post.php
- admin/includes/class-settings.php (M)
- admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.5.2[a] (2019-08-01)

- Changed the default value for datetime columns in the schema to avoid major errors during installation in newer versions of MySQL
- Tweaked some documentation in the `Post` class
- Created a function that checks whether a category slug exists in the database
- Categories can now be created
- Added more documentation to the `User` class
- Cleaned up the code in the `User::validateData` function
- The `id` parameter for the `User::usernameExists` function is no longer optional
- Categories can now be edited
- Added styling to the categories list
- Removed the parent list and added a categories list to the "Create Post" page
- Categories can now be added to posts (they cannot yet be removed)

**Modified files:**
- admin/includes/class-category.php
- admin/includes/class-post.php
- admin/includes/class-user.php
- admin/includes/css/style.css
- includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.5.1[a] (2019-07-30)

- Created a function that constructs the "Edit Category" form
- Created a function that deletes categories from the database
- Tweaked validation in the `Post::deleteEntry` function
- Added more documentation to the `User` class
- The category's parent can now be set to `none` on the "Create Category" form

**Modified files:**
- admin/categories.php
- admin/includes/class-category.php
- admin/includes/class-post.php (M)
- admin/includes/class-user.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.5.0[a] (2019-07-28)

- Created an admin "List Categories" page
- Added the "List Categories" page to the admin navigation
- Created a `Category` class
- The "Create <classname>" buttons have been relabeled as "Create New"
- Modified the logic of the status message display on the list entries pages
- Fixed some erroneous documentation in the `includes/functions.php` file
- Created a function that populates the taxonomies table
- Created a function that fetches a taxonomy's id based on its name
- Created a function that populates the terms table
- Created a function that constructs a list of all categories in the database
- A sample blog post is now created during the CMS installation in addition to the sample home page
- Created a function that populates the `term_relationships` table
- Created a function that fetches the post categories
- Created a function that fetches a category's parent
- Created a function that constructs the "Create Category" form
- Created a function that constructs a list of parent categories
- Created a function that checks whether the current category is a descendant of other categories
- Removed the `parent` column from the "List Posts" page (the `post` post type is not meant to be hierarchical)

**Modified files:**
- admin/categories.php (N)
- admin/header.php
- admin/includes/class-category.php (N)
- admin/includes/class-post.php
- admin/includes/class-user.php (M)
- admin/includes/functions.php
- admin/install.php

----------------------------------------------------------------------------------------------------
## Version 1.4.10[a] (2019-07-26)

- Added `terms`, `taxonomies`, and `term_relationships` tables to the schema
- Renamed the `rp_link` table to `rp_relationships`
- Added a line of documentation to the `install.php` file
- Added form validation to the `Post::editEntry` function
- Changed the link color on status messages
- Added a column for categories on the `post` post type's "List Posts" page
- Published pages now have proper permalinks for the "view" link
- The site url is now set during installation
- Required field labels now have a red asterisk next to them instead of "(required)"
- Improved styling on form pages using the form table layout
- Improved documentation for the `User` class
- Improved validation in the `User::editEntry` function
- Created a function that retrieves post metadata

**Modified files:**
- admin/includes/class-post.php
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/includes/functions.php
- admin/install.php
- includes/schema.php

----------------------------------------------------------------------------------------------------
## Version 1.4.9[a] (2019-07-23)

- Improved styling of the "Create Post" form
- Created a function that checks whether a post is in the trash
- Created a function that constructs a post permalink
- Created a function that checks whether the current post is a descendant of other posts
- Built the "Edit Post" page (posts cannot be submitted yet)

**Modified files:**
- admin/includes/class-post.php
- admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.8[a] (2019-07-22)

- Added form validation to the `Post::createEntry` function
- Created a function that checks whether a post slug already exists in the database
- Added styling to the "Create Post" form
- Tweaked styling on the admin footer
- Trashed posts will no longer appear in post parent dropdowns

**Modified files:**
- admin/footer.php (M)
- admin/includes/class-post.php
- admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.7[a] (2019-07-21)

- Improved styling on list entries pages
- Improved exception handling in the following `Post` class functions: `trashEntry`, `restoreEntry`, and `getParent`
- A post's status will now display next to the post title on the "List Posts" page (unless the post is published)
- Added a column to display the post's parent (if it has one) on the list posts page
- Posts can now be deleted
- Tweaked a previous entry in the changelog
- Posts can now be created (no validation yet)

**Modified files:**
- admin/includes/class-post.php
- admin/includes/css/style.css

----------------------------------------------------------------------------------------------------
## Version 1.4.6[a] (2019-06-20)

- Buttons will no longer have underlined text on mouse hover
- The "List Posts" table now tells whether metadata has been provided
- Posts can now be trashed and restored
- A post slug postmeta entry will no longer be created during the CMS installation

**Modified files:**
- admin/includes/class-post.php
- admin/includes/functions.php (M)
- includes/css/buttons.css (M)

----------------------------------------------------------------------------------------------------
## Version 1.4.5[a] (2019-05-29)

- Finished building the "Create Post" form
- Styled list entries pages
- Added an optional parameter to the `tableCell` function to allow a cell to span multiple columns
- Added a notice to be shown if no posts can be retrieved from the database on the "List Posts" page

**Modified files:**
- admin/includes/class-post.php
- admin/includes/css/style.css
- admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.4[a] (2019-05-03)

- Continued building the "Create Post" form
- Added a new parameter to the `formTag` function and functionality for building a `label` tag
- Fixed some issues in the `formRow` function caused by updates to the `formTag` function
- Created a function that constructs a list of post authors
- Created a function that constructs a list of parent posts
- Created a function that fetches a post's parent

**Modified files:**
- admin/includes/class-post.php
- admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.3[a] (2019-04-22)

- Added a `button` class to form submit buttons
- Added more documentation
- Added a placeholder to the `input` tag in the `formTag` function
- Fixed a minor bug in the `formRow` function
- Continued building the "Create Post" form

**Modified files:**
- admin/includes/class-post.php
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.2[a] (2019-04-10)

- Added more styling to the admin nav menu
- Cleaned up the `adminNavItem` function
- Added current page functionality to admin nav items (doesn't work for subnav items)

**Modified files:**
- admin/includes/css/style.css
- admin/includes/functions.php

----------------------------------------------------------------------------------------------------
## Version 1.4.1[a] (2019-04-09)

- Minor tweak to the changelog's formatting
- Created a function to contstruct the "Create Post" form (form is empty)
- Cleaned up the `User::createEntry` function
- Added and styled the admin header
- Styled the admin nav menu
- Put the admin page heading inside a wrapper
- Added more documentation

**Modified files:**
- admin/header.php
- admin/includes/class-post.php
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/css/style.css
- admin/index.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.4.0[a] (2019-04-05)

- Created admin "List Posts" page and `Post` class
- Replaced `intval` with `int` type casting on the users page
- Updated and added documentation to the `User` class
- The buttons stylesheet is now included in the admin dashboard
- Moved the `getStylesheet` and `getScript` functions to the `globals.php` file
- Added links to the post page in the admin nav menu
- The `User::getPageList` function now only retrieves published pages from the database
- Created a function that retrieves the post count based on post status (`Post` class)
- Created a function that constructs a table row
- Updated and added more documentation
- Created a function that constructs a list of all posts in the database by post type
- The `change.log` file has been renamed to `changelog.md` (it will henceforth be omitted from list of modified files)
- Converted the change log to markdown format
- Created a function that retrieve a post's author
- Cleaned up the `User::listEntries` function

**Modified files:**
- admin/header.php
- admin/includes/class-post.php (N)
- admin/includes/class-settings.php (M)
- admin/includes/class-user.php
- admin/includes/functions.php
- admin/posts.php (N)
- admin/users.php
- includes/functions.php
- includes/globals.php
- includes/logs/changelog.md (R)

----------------------------------------------------------------------------------------------------
## Version 1.3.8[a] (2019-03-29)

- The `robots.txt` file is now created on installation (added it to `.gitignore`)
- Replaced `\n` with `chr(10)` in the `logError` function
- Added `error_log` to `.gitignore` (home and admin directories)
- The `robots.txt` file is updated if the `do_robots` setting is changed
- The `intval` function will now be removed in favor of `int` type casting
- Added and updated more documentation
- Removed `LICENSE.md` from `.gitignore`

**Modified files:**
- .gitignore
- admin/includes/class-settings.php
- admin/install.php
- admin/setup.php
- includes/debug.php (M)
- includes/globals.php

----------------------------------------------------------------------------------------------------
## Version 1.3.7[a] (2019-03-28)

- Added and updated documentation in various places
- Cleaned up some admin files
- Created Settings admin class
- Version numbers will now be denoted as "Version `<version><[a/b]>`" or "`@since <version><[a/b]>`"
- Added the `maxlength` attribute to the `input` tag in the `formTag` function
- Added the `*` attribute to the `input` tag in the `formTag` function (this is for miscellaneous attributes like `readonly` or `checked`)
- Allowed `0` to be passed as a legitimate value to an input
- Fixed a bug in the `formRow` function that prevented adding a label to a single input argument
- Initialization now terminates if a database is not installed (prevents an error from generating in `error_log`)
- A sample page is now created on installation (it is set as the default home page)
- Cleaned up the changelog a bit (mostly rewording and adding a few things that had been omitted)

**Modified files:**
- admin/includes/class-settings.php (N)
- admin/includes/class-user.php
- admin/includes/functions.php
- admin/index.php
- admin/install.php
- admin/settings.php
- admin/users.php
- index.php
- init.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.3.6[a] (2019-03-27)

- Added a line of documentation to `init.php`
- Removed `PATH` from the `UPLOADS` filepath
- Added `__DIR__` to the admin `index.php` require statements

**Modified files:**
- admin/index.php (M)
- init.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.3.5[a] (2019-03-26)

- Moved `Query` object initialization to `init.php`
- Added `config.php` to `.gitignore` (prevents issues arising with db configs on different clients)
- Built the login form
- Styled the login page
- Created login captcha
- Added some documentation
- Rebranding "Really Simple CMS" to "ReallySimpleCMS"
- Minor styling tweaks to the installation page

**Modified files:**
- .gitignore
- admin/includes/css/install.css (M)
- admin/includes/functions.php (M)
- admin/install.php
- admin/setup.php (M)
- includes/captcha.php (N)
- includes/css/style.css
- includes/functions.php (M)
- includes/globals.php (M)
- init.php
- login.php

----------------------------------------------------------------------------------------------------
## Version 1.3.4[a] (2019-03-24)

- Created `LICENSE` and `README` files
- Created a `.gitignore` file (added `LICENSE` and `todo.txt`)

**Modified files:**
- .gitignore (N)
- LICENSE.md (N)
- README.md (N)

----------------------------------------------------------------------------------------------------
## Version 1.3.3[a] (2019-03-22)

- Created content directory
- Created CSS and JS files in the content directory for later use
- An exception is now thrown if the CMS has already been installed to prevent multiple installs
- Created a `Query` class function that shows all tables in the database
- Created a login page (without form)
- The installation page will now redirect to the setup page if `config.php` doesn't exist
- Buttons now have the cursor hand if hovered over
- Created functions to fetch stylesheets and scripts for the front end
- Moved `getSetting`, `trimWords`, and `trailingSlash` functions to `globals.php`
- Created a front end `style.css` file
- The `init.php` file now loads all required files
- Fixed a bug in the installation that allowed users to navigate to the login screen before they're supposed to
- Initialization and functions files are included in the header files of both the front end and admin dashboard
- Removed the `PATH` constant from config setup (`PATH` is defined in the `init.php` file)

**Modified files:**
- admin/header.php
- admin/install.php
- content/script.js (N)
- content/style.css (N)
- header.php
- includes/class-query.php
- includes/config-setup.php (M)
- includes/css/buttons.css (M)
- includes/css/style.css (N)
- includes/functions.php
- includes/globals.php
- init.php
- login.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.3.2[a] (2019-03-20)

- Moved `change.log` to its own directory (`includes/logs`)

**Modified files:**
- n/a

----------------------------------------------------------------------------------------------------
## Version 1.3.1[a] (2019-03-19)

- Fixed a bug in the setup process that allowed the `config.php` file to be created without data
- Fixed a file path issue in the `functions.php` file
- Created a function that adds a trailing slash to text
- Cleaned up the admin header
- Code cleanup
- Created a function to populate the users table
- Created an admin stylesheet
- Added basic styles to the admin area

**Modified files:**
- admin/header.php
- admin/includes/css/style.css (N)
- admin/includes/functions.php
- admin/install.php
- admin/setup.php (M)
- includes/config-setup.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.3.0[a] (2019-03-18)

- Created a file that initialize the CMS
- Added `__DIR__` to require statements
- Created a file that sets up the database connection
- Created a file for config setup
- Added stylesheet for the setup and installation pages
- Added stylesheet for buttons
- Created a file for installing the CMS
- Added a variable that tests the db connection
- Added exception handling to the `Query` class' constructor
- Created and tested installation functionality
- Created function for running generic SQL queries in the `Query` class
- Added more documentation

**Modified files:**
- admin/includes/css/install.css (N)
- admin/includes/functions.php
- admin/install.php (N)
- admin/setup.php (N)
- header.php (M)
- includes/class-query.php
- includes/config-setup.php (N)
- includes/css/buttons.css (N)
- includes/debug.php (M)
- includes/schema.php
- index.php (M)
- init.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.2.6[a] (2019-03-12)

- Created a function that constructs the database schema
- Created an admin "General Settings" page

**Modified files:**
- admin/settings.php (N)
- includes/schema.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.2.5[a] (2019-03-08)

- Created a function that retrieves statistics data
- Created a function that trims words from a set of text
- Created a function that retrieves settings from the database
- Created a function that constructs admin nav items

**Modified files:**
- admin/header.php
- admin/includes/functions.php
- admin/index.php

----------------------------------------------------------------------------------------------------
## Version 1.2.4[a] (2019-02-26)

- Created function that verifies passwords
- Created function that retrieves a user's avatar
- Fixed a small bug that was triggered if a query returns null
- Created a function that constructs and displays a statistics bar graph

**Modified files:**
- admin/includes/class-user.php
- admin/includes/functions.php
- includes/class-query.php (M)
- includes/globals.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.2.3[a] (2019-02-22)

- Added validation for edit user form data
- Created a function for deleting users
- Added reset password form
- Added validation for reset password form data

**Modified files:**
- admin/includes/class-user.php
- admin/users.php

----------------------------------------------------------------------------------------------------
## Version 1.2.2[a] (2019-02-21)

- Added more documentation
- Created a function that retrieves user metadata

**Modified files:**
- admin/includes/class-user.php
- admin/includes/functions.php (M)
- includes/class-query.php (M)
- includes/globals.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.2.1[a] (2019-02-20)

- Added more documentation
- Created a function that lists all users in a table
- Created admin pagination function
- Created functions that assemble admin tables
- Created a function that formats date strings
- Created a function that implements admin page navigation
- Added the "Edit User" page and form

**Modified files:**
- admin/includes/class-user.php
- admin/includes/functions.php
- admin/users.php
- includes/class-query.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.2.0[a] (2019-02-19)

- Added ReallySimpleCMS copyright and version to the admin footer
- Created a file to hold global functions
- Created a function that assembles form tags (works with the `formRow` function)
- Finished the "Create User" form and added validation
- Created functions for loading admin scripts and stylesheets
- Added documentation for numerous functions

**Modified files:**
- admin/footer.php
- admin/includes/class-user.php
- admin/includes/functions.php
- includes/config.php (M)
- includes/globals.php (N)

----------------------------------------------------------------------------------------------------
## Version 1.1.2[a] (2019-02-18)

- Created a function that assembles form rows
- Created a function that constructs the "Create User" form

**Modified files:**
- admin/includes/class-user.php
- admin/includes/functions.php
- admin/users.php

----------------------------------------------------------------------------------------------------
## Version 1.1.1[a] (2019-02-11)

- Minor updates to the `Query::select` function
- Created a function that selects single rows from the database

**Modified files:**
- includes/class-query.php

----------------------------------------------------------------------------------------------------
## Version 1.1.0[a] (2019-02-07)

- Rebuilt the functions for the `SELECT`, `INSERT`, and `UPDATE` statements
- Created a file to store deprecated functions (for potential future use)
- Extended the `Query` class' scope so it works on the back end
- Created admin "List Users" page

**Modified files:**
- admin/includes/class-user.php (N)
- admin/includes/functions.php
- admin/users.php (N)
- includes/class-query.php
- includes/config.php (M)
- includes/deprecated.php (N)
- includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.3[a] (2019-02-04)

- Renamed some functions in the `Query` class
- Created a function to check for query errors
- Rebuilt the function for the `DELETE` statement

**Modified files:**
- includes/class-query.php
- includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.2[a] (2019-01-17)

- Created and tested basic functionality for the `UPDATE` statement
- Created a class for retrieving post data
- Added class autoloading
- Added basic HTML to the `header.php` and `footer.php` files
- Created the `admin` directory and its core files

**Modified files:**
- admin/footer.php (N)
- admin/header.php (N)
- admin/includes/functions.php (N)
- admin/index.php (N)
- footer.php
- header.php
- includes/class-post.php (N)
- includes/class-query.php
- includes/config.php
- includes/functions.php (M)
- index.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.1[a] (2019-01-15)

- Created a changelog for tracking project milestones (`change.log` will henceforth be omitted from list of modified files)
- Created a file with basic debugging functions
- Created and tested basic functionality for query `SELECT`, `INSERT`, and `DELETE` statements

**Modified files:**
- change.log (N)
- includes/class-query.php
- includes/config.php
- includes/debug.php (N)
- includes/functions.php (M)

----------------------------------------------------------------------------------------------------
## Version 1.0.0[a] (2019-01-14)

- Set up the database and tables
- Created most of the necessary core files
- Created the `includes` directory
- Created the core database connection class
- Built the basic database query functionality

**Modified files:**
- .htaccess (N)
- 404.php (N)
- footer.php (N)
- header.php (N)
- includes/class-query.php (N)
- includes/config.php (N)
- includes/functions.php (N)
- index.php (N)
- page.php (N)
- robots.txt (N)