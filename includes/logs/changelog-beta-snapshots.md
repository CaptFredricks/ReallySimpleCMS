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
- x.x.x{ss-xx} (snapshot release)

**Other**
- [a] = alpha
- [b] = beta

## Version 1.2.0[b]{ss-05} (2020-12-28)

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
- New functions:
  - Admin `Login` class (`loginRules`, `createRule`, `editRule`, `deleteRule`, `validateRuleData`, `formatDuration`)
  - `Login` class (`shouldBlacklist`)

**Modified files:**
- admin/includes/class-login.php
- admin/includes/functions.php
- admin/logins.php
- includes/class-login.php
- includes/class-query.php
- includes/schema.php

## Version 1.2.0[b]{ss-04} (2020-12-20)

- Added two new settings:
  - `track_login_attempts` (whether login attempts should be logged in the database or not)
  - `delete_old_login_attempts` (whether to delete login attempts more than 30 days old)
- The new settings are added to the database automatically for sites updating from `1.1.7[b]`
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

## Version 1.2.0[b]{ss-03} (2020-12-10)

- Tweaked a previous entry in the changelog
- All `select`, `update`, and `delete` queries can now use `OR` logic in their `where` clauses by supplying `'logic'=>'OR'` as an element of the `where` clause array
- If a logged in user is added to the logins blacklist, they are now logged out
- The `DISTINCT` keyword can now be added to `select` queries (it must be added to the `data` parameter's array)
- The `actionLink` function can now accept `classes` as a valid argument (allows for the action link to receive CSS classes)
- Tweaked documentation in the admin `Login` class
- Custom login blacklist entries can now be created (this is distinct from the "Login Attempts" blacklist options)
- New functions:
  - Admin `Login` class (`createBlacklist`)

**Modified files:**
- admin/includes/class-login.php
- admin/includes/functions.php
- admin/logins.php
- includes/class-query.php

## Version 1.2.0[b]{ss-02} (2020-12-08)

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
- New functions:
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

## Version 1.2.0[b]{ss-01} (2020-12-05)
*Feature Update: Login Tracking*

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
- New functions:
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

## Version 1.1.0[b]{ss-05} (2020-10-21)

- Tweaked documentation in the Carbon theme's `script.js` file
- Tweaked documentation in the front end `script.js` file
- When using the Carbon theme, the sticky header no longer covers the reply box when a reply link is clicked
- Users will now be redirected back to the admin page they were viewing upon logging back in if they are logged out unexpectedly
- Renamed some selectors for the comment section and tweaked some styling
- Users can now edit and update their comments
- Optimized and improved the action links functionality for the "List Users" page
  - Users who don't have the `can_edit_users` or `can_delete_users` privileges can no longer see the "Edit" or "Delete" action links
- New functions:
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

## Version 1.1.0[b]{ss-04} (2020-09-23)

- Added comments to the "Admin" admin bar dropdown
- Reply links are now hidden on existing comments if comments are disabled on the post, post type, or global level (existing comments are not hidden, however)
- Styled and added a reply form to the comment feed
- Comments can now be created and deleted
- If a comment is a reply to another comment, the child comment now has a link to its parent
- Tweaked previous entries in the changelog
- Added a container element to the comment feed
- The comment feed will now refresh whenever a new reply is posted or a comment is deleted
- Code cleanup in the front end `script.js` file
- New functions:
  - `Comment` class (`getCommentAuthorId`, `getCommentParent`, `getCommentCount`, `getCommentReplyBox`, `createComment`, `deleteComment`)
- Renamed functions:
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

## Version 1.1.0[b]{ss-03} (2020-09-22)

- Added two new settings:
  - `comment_status` (whether comments are enabled)
  - `comment_approval` (whether comments are automatically approved)
- The new settings are added to the database automatically for sites updating from `1.0.9[b]`
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
- New functions:
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

## Version 1.1.0[b]{ss-02} (2020-09-21)

- Tweaked a previous entry in the changelog
- Tweaked documentation in the `update.php` file
- Added most of the core functions of the admin `Comment` class
  - Comments can be viewed, edited, deleted, approved, and unapproved
- Tweaked the styling of data tables on the admin dashboard
- Tweaked documentation in the `Post` and `Term` classes
- Added an additional CSS class to data table columns to ensure that they don't conflict with other page elements
- Tweaked the schema for the `comments` database table
- Cleaned up code in the `Post` class
- New functions:
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

## Version 1.1.0[b]{ss-01} (2020-09-20)
*Feature Update: Comments*

- Created a database schema for the `comments` table
- Created a file that will handle safely updating things such as the database schema
- The `update.php` file is included in the `init.php` file
- The `comments` database table is now created when the version is higher than `1.0.9[b]`
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