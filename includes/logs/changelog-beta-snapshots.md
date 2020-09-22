# ReallySimpleCMS Changelog (Beta Snapshots)

----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - renamed file, X - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.1.0[b]{ss-02} (2020-09-22)

- Tweaked a previous entry in the changelog
- Tweaked documentation in the `update.php` file
- Created a function that constructs and displays all comments in the database
- Tweaked the styling of data tables on the admin dashboard
- Tweaked documentation in the `Post` and `Term` classes
- Created a function that fetches a comment's post from the database
- Created a function that fetches a comment's author from the database
- Added an additional CSS class to data table columns to ensure that they don't conflict with other page elements
- Tweaked the schema for the `comments` database table
- Created a function that approves a comment
- Created a function that unapproves a comment
- Created a function that deletes a comment
- Cleaned up code in the `Post` class
- Created a function that constructs the "Edit Comment" form
- Created a function that validates form data for the `Comment` class
- Created a function that fetches a post's permalink (`Comment` class)

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

----------------------------------------------------------------------------------------------------
## Version 1.1.0[b]{ss-01} (2020-09-21)

- Created a database schema for the `comments` table
- Created a file that will handle safely updating things such as the database schema
- The `update.php` file is included in the `init.php` file
- The `comments` database table is now created when the version is higher than `1.0.9[b]`
- Added a new `comments` argument to the `registerPostType` function (if set to true, comments will be allowed for that post type; default is false)
- Set comments to display for the `post` post type
- Two new metadata entries are now created for posts of any type that has comments enabled (`comment_status` and `comment_count`)
- The comment count is now listed on the "List <post type>" page as its own column
- Added a comments block to the "Create <post type>" and "Edit <post type>" pages
- Created a `Comment` admin class and admin `comments.php` file
- Added comments to the admin nav menu (below the post types and above 'Customization')
- Tweaked documentation in the admin `functions.php` file
- Tweaked a previous entry in the Beta changelog
- Created a new changelog for Beta Snapshots
- Fixed an issue with comment privileges not properly being assigned to default user roles
- Adjusted privileges for the default user roles (the CMS will automatically reinstall the `user_privileges` and `user_relationships` tables)
- Individual posts with comments disabled will now display an emdash on the "Comments" column of the "List <post_type>" page
- When a post with comments is deleted, its comments are now deleted along with it

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