# ReallySimpleCMS Changelog (Beta Snapshots)

----------------------------------------------------------------------------------------------------
*Legend: N - new file, D - deprecated file, R - renamed file, X - removed file, M - minor change*<br>
*Versions: X.x.x (major releases), x.X.x (standard releases), x.x.X (minor releases/bug fixes)*<br>
*Other: [a] - alpha, [b] - beta*

----------------------------------------------------------------------------------------------------
## Version 1.1.0[b][ss-01] (2020-09-21)

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