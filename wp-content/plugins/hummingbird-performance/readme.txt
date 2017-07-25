=== Hummingbird Page Speed Optimization ===
Plugin Name: Hummingbird Page Speed Optimization
Version:
Author: WPMU DEV
Author URI: http://premium.wpmudev.org/
Contributors: WPMUDEV
Tags: Optimization, Caching, Speed, Performance, Faster, PageSpeed, Gzip, Compression, Minify, Minification, CloudFlare
Requires at least: 3.5
Tested up to: 4.7.3
Stable tag: 1.5.5
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Make your site load faster with file compression, minification and browser caching – because when it comes to pagespeed, every millisecond counts!



== Description ==

<strong>Hummingbird</strong> zips through your site and finds new ways to boost page speed with fine-tuned controls over <strong>file compression</strong>, <strong>minification</strong> and <strong>browser caching</strong>. Load your pages quicker and score higher on <a href="https://developers.google.com/speed/pagespeed/insights/">Google PageSpeed Insights</a> with Hummingbird site optimization.

= Features Available in Hummingbird Include: =

* Performance Reports - Pro tips for running your site at super speed
* Minification - Position, minify and combine files for top performance
* Browser Caching - Reward repeat visitors with faster load times
* GZIP Compression - Blazing fast HTML, JavaScript, and stylesheet transfer


= Scans and Recommendations =

Hummingbird will scan your site, find files that are slowing it down and provide tips and fixes for making your site run at top speed.

Hummingbird even has one-click improvements for quickly optimizing performance.

★★★★★ <br>
“Hummingbird is so easy to use. I thought it wouldn't change my speed much because I already made improvements. I ran the scan, it gave me recommendations, I pushed a button to apply them and it made my site even faster!” - <a href="https://premium.wpmudev.org/profile/juancamilobuitrago">Camilo</a>

★★★★★ <br>
“Hummingbird took me from 32 to 84 on Google page speed plus made my site 50% faster on GTmetrix!” - <a href="https://premium.wpmudev.org/profile/nicolastergiopoulos">Nicolas</a>

= Minification =

Did you know that the more files you add to your site’s the header, the slower it will load? With Hummingbird, you can easily customize the load position of your files to increase your page speed.

Quickly reorder, compress and reposition files with Hummingbird’s minification feature. But play carefully – minification is a powerful tool (though if you need it, you can reset any changes you make in one click).

= Transfer Data at Top Speed with GZIP =

Hummingbird has GZIP powers to make sharing your site more efficient.
Sending zipped files is faster and can save you money on hosting. And don’t worry about setup, send Hummingbird instructions with the click of a button and she’ll handle the rest.

= Browser Cache =

Make your site load even faster for returning visitors. Browser caching stores content in your visitor’s browser so they don't have to download things twice.

= Built-in Cloudflare Integration =

Hummingbird can be used to control your <a href="https://www.cloudflare.com/">CloudFlare</a> browser cache settings as well! Simply add your CloudFlare API details and configure away.

Hummingbird is a one of a kind WordPress performance optimization plugin that can make your site run at superspeed, for free!

However, if you'd like automated scanning, uptime monitoring, enhanced minify compression with up to 2x the regular optimization, CDN hosted minification and Smush Pro image optimization you can always take the next step with a <a href="https://premium.wpmudev.org/">WPMU DEV Membership.</a>



== Frequently Asked Questions ==

= Do I need Hummingbird if I already have WP Rocket or W3 Total Cache? =

Yes! If you already use WP Rocket or W3 Total Cache the special monitoring tools Hummingbird offers makes it worth it. To ensure compatibility only activate overlapping features in one of the plugins.

= Does Hummingbird work with CloudFlare? =

Yes! Hummingbird is built to take full advantage of CloudFlare features. Just plug in your API to get started.

= Does Hummingbird work with plugins like WP Super Cache, Batcache or the full page caching feature my Managed WordPress host offers? =

Yes! Plugins like WP Super Cache and Batcache and Managed WordPress Hosting that offer full page only HTML caching are actually recommended by Hummingbird to give your users the fastest browsing experience possible for your site.

= Does Hummingbird work with WooCommerce? =

Yes! Hummingbird only caches browser assets, like images, stylesheets & scripts (not cookies). Because we leave full page caching to dedicated plugins, Hummingbird works great with ecommerce plugins like WooCommerce and MarketPress.

= Somebody help! I turned on Minification and it broke my site!? =

Minification is a great way to speed up your site but moving combining and rearranging files can also mess with styling and break functionality. We recommend making incremental changes and testing your site as you go so you can undo the changes if something breaks. And remember, don’t panic. If something goes wrong you can start over with the click of a button :)



== Screenshots ==

1. Minification lets you combine and reposition files.
2. Scan your site for optimization tips and tricks.
3. Use suggestions to make improvments and speed up your site.



== Installation ==

1. Upload the `wp-hummingbird` plugin to your `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure and manage using the `hummingbird` menu item in the WordPress dashboard.
1. Done!



== Changelog ==

= 1.5.4.1 ( 2017-07-17 ) =
- Fix: Bug in Performance Report
- Fix: Fatal error on activation on the free versioon
- Fix: HTML tags in Cloudflare module

= 1.5.4 ( 2017-07-13 ) =
- Fix: Cron reports were stuck on a loop on some WordPress installations
- Fix: Issue with reports and calculating UTC time
- Fix: Minification was duplicating inline styles
- Fix: IE incompatibility in magnification screen
- Fix: Compatibility with Contact Form 7
- Fix: Mobile menus not working on Performance and Minification pages
- Enhance: Remove jQuery and jQuery migrate from minification process
- Enhance: Minor style changes and updates
- Enhance: Cron reports functionality
- Enhance: Code optimization
- Enhance: Admin area performance improvements

= 1.5.3 ( 2017-06-21 ) =
- Fix: Avada & Google Maps scripts incompatibility
- Fix: Bulk update not working when selecting all items in minification screen
- Fix: Minification engine sometimes forget about files and did not clean them
- Fix: Minification was always showing 0% saved in some cases
- Fix: Enjoy pro features notice does not go away
- Fix: Performance scans were not active for some users of the free version of the plugin
- Enhance: Some code optimizations in few areas
- Enhance: Minified files are now processed in a background task and in a bigger batch
- Enhance: Some minor styles changes
- Enhance: Use CDN option is not available in sites of a multisite anymore and is controlled from network admin
- Enhance: Improved activation logic for free/pro versions
- Some other minor fixes and refactoring

= 1.5.2 ( 2017-06-05 ) =
- First WordPress.org version
- Fix: Minification actions in bulk update modal were not functioning properly

= 1.5.1 ( 2017-06-05 ) =
- New: Allow to customise reports with WordPress hooks
- Enhance: Reports notifications now allow user name to be customised
- Enhance: When free and pro versions are activated, free version is auto deactivated
- Fix: Do not assign any user for reports notifications by default
- Fix: Performance settings were not properly saved if there were no user emails in the list
- Fix: Reports notifications could not be switched off
- Performance notifications is now pro feature only
- Moved dashboard class to pro folder
- Introduced ES6 in some scripts
- Some minor styles fixes
- Some other minor fixes

= 1.5 ( 2017-05-25 ) =
- New: User Interface has been completely redesigned
- New: Quick setup welcome modal
- New: Performance Reports. This new feature allow users to schedule performance reports and get them right into their email boxes
- New: Performance tests are now available for free users by triggering them manually
- New: Clear Cache button
- Enhance: Caching and Gzip are now auto checked when the plugin is activated
- Enhance: Do not combine assets by default
- Enhance: Uptime data is synced with WPMU DEV servers every 3 minutes
- Enhance: Minification stats calculations improvements
- Enhance: CloudFlare admin menu has been removed and integrated into Browser Cache screen
- Fix: When performance test is run for the first time, previous score improvement was "Pending"
- Fix: stylesheets with @import directives were always displayed as "Pending"
- Fix: Various minor improvements and bug fixes

= 1.4.3 ( 2017-04-25 ) =
- Fix: Sometimes minification stats appeared as a negative number
- Enhance: Minification: Make all post metadata private
- New: Added new actions to modify where HB saves the files

= 1.4.2 ( 2017-03-02 ) =
- Fix: MarketPress incompatibility
- Fix: In some cases, assets were not moved to footer
- Fix: Assets were always combined if some of them were marked as "not combined" in the same group
- Fix: Keep handle name when only one handle is enqueued in a group for better compatibility with wp_script_is function
- Fix: add_inline_scripts/styles did not work for plugins that inject inlines too late
- Fix: Sometimes assets are not enqueued under https if the site is SSL
- Enhance: CloudFlare box - Show a message if there are no zones available
- Some other minor fixes/improvements

= 1.4.1 ( 2017-01-12 ) =
- Fix: Upgrade process was triggered too early provoking a fatal error when Smart Crawler was active
- Fix: Minify Group warning
- Fix: Error when WPMUDEV Dashboard was not activated

= 1.4 ( 2017-01-10 ) =
- New: Brand new minification engine
- New: Minified files are now processed in WPMUDEV's servers
- New: Users can use WPMUDEV's CDN to store minified files
- New: Clear CloudFlare cache feature
- Fix: PHP code in stylesheets could potentially be exposed
- Enhancement: stylesheets with @import directives are not minified anymore due to multiple issues that were causing
- Some admin styles improvements

= 1.3.5 ( 2016-10-24 ) =
- New: Divi 3.0 integration

= 1.3.4.1 ( 2016-10-07 ) =
- Fixed: Minification was always enabled in some sites.

= 1.3.4 ( 2016-10-03 ) =
- PHP 5.3 required for minification
- Fix: Javascript improvements in CloudFlare box
- Fix: Missing Hummingbird icon in admin bar
- Enhancement: CloudFlare now auto selects domain
- Enhancement: Improved caching detection
- Other minor improvements

= 1.3.3 ( 2016-09-02 ) =
- Fix: Parse error in CloudFlare module when ASP tags detection was active in PHP

= 1.3.2 ( 2016-08-22 ) =
- Fix: Error when detecting CloudFlare on activation

= 1.3.1 ( 2016-08-17 ) =
- Fix: CloudFlare was only showing 20 sites max
- Fix: Parse error in PHP < 5.3 installations

= 1.3 ( 2016-08-15 ) =
- New: CloudFlare integration
- New: LiteSpeed server detection
- Enhance: Clear minification cache when a plugin/theme is activated/deactivated
- Enhance: New better Javascript compressor
- Enhance: Plugin performance improvements
- Enhance: Better Uptime error display
- Fixed: Notice in minification screen when a plugin was deleted manually
- Fixed: replaced buttons by inputs as sometimes settings cannot be submitted in minification screen
- Fixed: Some minified URLs where prepended 'http' even if they had already http prepended
- Fixed: Some admin styles

= 1.2.4 ( 2016-08-10 ) =
- Fixed fatal error on activation when PHP version is < 5.3

= 1.2.3 ( 2016-07-22 ) =
- Enhance: Updated WPMU DEV styles library

= 1.2.2 ( 2016-06-16 ) =
- Fixed: Hummingbird Exception throwing fatal error in old versions of PHP (<5.3)
- Fixed: Notice in minification screen when a plugin was deleted manually
- Fixed: replaced buttons by inputs as sometimes settings cannot be submitted in minification screen
- Fixed: Some minified URLs where prepended 'http' even if they had already http prepended
- Enhance: Better Uptime error handling
- Replaced the javascript compressor for a better one
- Some styles improvements in admin


= 1.2.1 ( 2016-05-17 ) =
Fix: Sometimes excluded files did not appear on list after re checking files
Fix: Server type not updated when user switches it
Fixes to Google Chart API
Enhance: Faster minification scan
Enhance: Better GZIP htaccess code
Enhance: Search by extension in minification page

= 1.2 ( 2016-04-18 ) =
- New: Filter by plugin, theme or filename in minification screen
- New: Bulk actions in minification screen
- New: Divi integration
- New: Splitted minification chart into header and footer charts
- Fix: Unconsistent count of enqueued files in Dashboard
- Fix: BuddyPress warning
- Fix: PHP 5.2.4 error
- Fix: GZIP and caching results were cleared after 1 hour
- Fix: Login screen errors due to minification process
- Fix: Sometimes minificated sources were enqueued twice.
- Admin styles improvements
- Other minor fixes and improvements

= 1.1.2 ( 2016-03-18 ) =
- Fixed: Performance test waiting time set back to 5 minutes

= 1.1.1 ( 2016-03-17 ) =
- Fixed: Uptime not displaying chart
- Fixed: Warning in Uptime page

= 1.1 ( 2016-03-16 ) =
- New: New filetype in Caching results
- New: wphb_add_meta_box filter
- New: wphb_code_snippet filter
- Enhance: Get Uptime results when the plugin is activated
- Enhance: Start a performance test when the plugin is activated
- Enhance: Better errors handling in Plugin API Classes
- Enhance: Better cache clean on deactivation
- Fixed: Minification cached resources not being loaded via https
- Fixed: Permissions errors in multisites
- UX Improvements

= 1.0.1 ( 2016-03-03 ) =
- Enhance: Added plugins list action link
- Enhance: If Browser Caching is already recommended or better, Enable button is now hidden
- Enhance: Activate API log by using WP_DEBUG_LOG constant
- Fixed: Warning when deleting cached files
- Fixed: Empty file was appearing when all assets were sent to footer
- Fixed: settings were reset to default when plugin was deactivated.
- Some other minor enhancements/fixes

= 1.0 ( 2016-02-23 ) =
- First release

== About Us ==
WPMU DEV is a premium supplier of quality WordPress plugins and themes. For premium support with any WordPress related issues you can join us here:
<a href="https://premium.wpmudev.org/?utm_source=wordpress.org&utm_medium=readme">https://premium.wpmudev.org/</a>

Don't forget to stay up to date on everything WordPress from the Internet's number one resource:
<a href="https://premium.wpmudev.org/blog/?utm_source=wordpress.org&utm_medium=readme">WPMU DEV Blog</a>

Hey, one more thing... we hope you <a href="http://profiles.wordpress.org/WPMUDEV/">enjoy our free offerings</a> as much as we've loved making them for you!