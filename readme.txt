=== Advanced Custom Fields: File Path Picker Field ===
Contributors: Mike Walker <mike@incredimike.com>
Tags: ACF, plugin
Requires at least: 4.0
Tested up to: 4.7.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a field that returns a path to a file.

== Description ==

Specify a directory which is scanned for files. Files are presented in a drop-down list. Admin can select 1 file per field instance. Use get_field to return full path to file

= Compatibility =

This ACF field type is compatible with:
* ACF 5

== Installation ==

1. Copy the `acf-path-picker` folder into your `wp-content/plugins` folder
2. Activate the File Path Picker plugin via the plugins admin page
3. Create a new field via ACF and select the File Path Picker type
4. Please refer to the description for more info regarding the field type settings

== Changelog ==

= 1.1.0 =
* Added composer.json loading & cleaned up code.


= 1.0.0 =
* Initial Release.
