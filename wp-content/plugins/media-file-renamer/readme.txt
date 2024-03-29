=== Media File Renamer - Auto & Manual Rename ===
Contributors: TigrouMeow
Tags: rename, file, media, move, seo, files, renamer, optimize, library
Donate link: https://commerce.coinbase.com/checkout/d047546a-77a8-41c8-9ea9-4a950f61832f
Requires at least: 5.0
Tested up to: 5.7
Requires PHP: 5.6
Stable tag: 5.2.4

Renames your media files for better SEO and a nicer filesystem (automatically or manually).

== Description ==
Renames your media files for better SEO and a nicer filesystem (automatically or manually). For more information, please visit the official website: [Media File Renamer](https://meowapps.com/plugin/media-file-renamer/).

=== HOW IT WORKS ===
Media File Renamer, by default, automatically renames the filenames of your Media entries based on their titles. You can trigger this, or you can let it happen every time you modify titles. You can also rename the files manually. The references to those files will be also updated (posts, pages, custom types, metadata, etc...). You can use the Media Library, or the Media Edit screen.

However, it is highly recommended to use the pretty and very dynamic Renamer Dashboard. If you like to work fast and well, you will really love working with this modern dashboard.

[youtube https://youtu.be/XPbKE8pq0i0]

Please have a look at the [tutorial](https://meowapps.com/media-file-renamer-tutorial/).

=== COMPATIBILITY ===
It works with a lot of features of WordPress and other plugins, such as Retina files, WebP, rescaled image (since WP 5.3), PDF Thumbnails, UTF8 files, optimized images, various encodings, etc. There are too many handled and specific cases to be listed here, but we are doing our best to keep up with everything :)

=== PRO VERSION ===
More features are added in the [Pro Version](https://meowapps.com/plugin/media-file-renamer/), such as:
- Transliteration (replace various accents, emoticons, umlauts, cyrillic, diacritics, by their ASCII equivalent)
- Automatic renaming based on the attached posts, products (and other post types), or ALT text
- Anonymizer (rename the files with anonymous files)
- Move files to another directory
- Metadata syncing (ALT text, title, etc)
- Numbered files (to allow similar filenames to be renamed)
- Force Rename (if your install is broken, this will help you to re-link your media entries to your files)

=== BE CAREFUL: PREPARE A BACKUP ===
Renaming (or moving) files is a dangerous process. Before doing anything in bulk, try renaming your files on by one, then check if the references (in your pages) have been updated properly. The renaming can't cover all use cases, as some plugins are unfortunately using unconventional ways to encode the usage of the files. Therefore, **it is absolutely necessary to backup your files and database** in order to enjoy this plugin at its full extent. 

=== WHEN SOMETHING BAD HAPPENS ===
If your website seems broken after a few renames, try to **clear your cache**. The cached HTML is often using the old references. You can also enable the Undo feature and try to rollback to the previous filenames. If references aren't updated properly, please write a nice post (not an angry one) in the support threads :) I am trying my best to cover more and more use cases. Please have a look here: [Questions & Issues](https://meowapps.com/media-file-renamer-faq-issues/).

=== A SIMPLER PLUGIN ===
If you only need an editable field in order to modify the filename, please try [Phoenix Media Rename](https://wordpress.org/plugins/phoenix-media-rename). It's simpler, and just does that. And yes, we are friends and we collaborate! :)

=== FOR DEVELOPERS ===
The plugin can be tweaked in many ways, there are many actions and filters available. Through them, for example, you can customize the automatic renaming to your liking. There is also a little API that you can call. More about this [here](https://meowapps.com/media-file-renamer-faq/).

== Installation ==

1. Upload the plugin to your WordPress.
2. Activate the plugin through the 'Plugins' menu.
3. Try it with one file first! :)

== Upgrade Notice ==

1. Replace the plugin with the new one.
2. Nothing else is required! :)

== Screenshots ==

1. Type in the name of your media, that is all.
2. Special screen for bulk actions.
3. This needs to be renamed.
4. The little lock and unlock icons.
5. Options for the automatic renaming (there are more options than just this).

== Changelog ==

= 5.2.4 (2021/06/13) =
* Add: Remember the number of entries per page (dashboard).
* Fix: Limit the length of the manual filename.

= 5.2.3 (2021/05/29) =
* Fix: The 'Move' feature now also works with the original image (in case it has been scaled by WP).

= 5.2.2 (2021/05/18) =
* Fix: Better Windows support.

= 5.2.0 (2021/05/15) =
* Add: Move button (this was mainly added for tests, so it's a beta feature, it will be perfected over time).
* Add: Images Only option.
* Fix: Vulnerability report, a standard user access could potentially modify a media title with custom requests.

= 5.1.9 (2021/04/09) =
* Fix: The Synchronize Alt option wasn't working logically.
* Note: The plugin has no known bugs for a while, and I am now happy to work on littke extra features :) By the way, if you like it, please review the plugin [by clicking here](https://wordpress.org/support/plugin/media-file-renamer/reviews/?rate=5#new-post). Thank you!

= 5.1.8 (2021/03/04) =
* Add: Search.
* Add: Quick rename the title from the dashboard.

= 5.1.7 (2021/02/21) =
* Fix: The Synchronize Media Title option wasn't working logically.

= 5.1.6 (2021/02/12) =
* Fix: References for moved files were not updated.
* Add: Sanitize filename after they have been through the mfrh_new_filename filter.

= 5.1.3 (2021/02/06)  =
* Add: Greek support.
* Fix: Better sensitive file check.
* Fix: Manual rename with WP CLI.

= 5.1.2 (2021/01/10) =
* Add: Auto attach feature.
* Add: Added Locked in the filters.
* Update: Icons position.

= 5.1.1 (2021/01/05) =
* Fix: Issue with roles overriding and WP-CLI.
* Fix: Issue with REST in the Common Dashboard.

= 5.1.0 (2021/01/01) =
* Add: Support overriding roles.
* Fix: The layout of the dashboard was broken by WPBakery.
