=== Encrypt Blogs ===
Contributors: bokumin
Tags: encryption, privacy, content protection, time-based encryption, gpg encryption
Requires at least: 5.0
Tested up to: 6.6.1
Stable tag: 1.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Encrypt your blog content with time-based encryption using either PHP or GPG encryption methods.
== Description ==
Encrypt Blogs is a WordPress plugin that allows you to encrypt specific content blocks in your posts and pages. It provides flexible time-based encryption with support for both PHP and GPG encryption methods.
= Key Features =

Time-based content encryption
Support for both PHP (AES-256-CBC) and GPG encryption
Multiple display modes for encrypted content
Custom Gutenberg block for easy content management
Flexible date/time format support

= Display Modes =

Hidden - Completely hide the encrypted content
Show Encrypted - Display the encrypted text
Show Message - Display a custom message
Redacted - Show a redacted placeholder

= Time Format Support =
Supports various date/time formats:

Full format: "2024-03-25 14:30"
Date only: "2024-03-25"
Month and day: "03-25"
Time only: "14:30"

== Installation ==

Upload the encrypt-blogs directory to the /wp-content/plugins/ directory
Activate the plugin through the 'Plugins' menu in WordPress
Go to Settings > Encrypt Blogs to configure your encryption settings
Start using the 'Encrypted Content' block in your posts

== Usage ==

Create a new post or edit an existing one
Add the 'Encrypted Content' block
Enter your content
Set encryption parameters in the block settings:

Start Date (optional)
End Date (optional)
Display Mode


Publish or update your post

== Configuration ==

Navigate to Settings > Encrypt Blogs
Choose your preferred encryption method:

PHP Encryption (AES-256-CBC)
GPG Encryption


Configure the chosen method:

For PHP: Set a secure passphrase
For GPG: Add your public/private keys and passphrase



== Frequently Asked Questions ==
= Which encryption methods are supported? =
The plugin supports two encryption methods:

PHP encryption using AES-256-CBC
GPG encryption using GnuPG

= How does time-based encryption work? =
You can specify start and end dates for encryption. The content will only be encrypted during this period. If no dates are specified, the content remains encrypted indefinitely.
= Is it secure? =
Yes. The plugin uses industry-standard encryption methods:

PHP encryption uses AES-256-CBC
GPG encryption uses your own GPG keys
Keys and passphrases are stored securely in WordPress options

= What happens to the content after the end date? =
After the end date passes, the content automatically becomes visible in its unencrypted form.
== Screenshots ==

Plugin settings page
Encrypted Content block in editor
Block settings panel
Various display modes

== Changelog ==
= 1.0.2 =

Added support for additional date/time formats
Improved error handling for GPG encryption
Enhanced security measures

= 1.0.1 =

Fixed block editor integration issues
Improved date parsing reliability

= 1.0.0 =

Initial release

== Upgrade Notice ==
= 1.0.2 =
This version adds more flexible date/time format support and important security improvements. All users should upgrade.
== Development ==

GitHub repository: https://github.com/bokumin/encrypt-blogs
Please report issues and contribute patches through GitHub
