=== Gravity Forms Dynamics CRM Add-On ===
Contributors: saintsystems, anderly
Tags: gravityforms, gravity, form, forms, gravity forms, crm, dynamics, dynamics crm
Requires at least: 3.3
Tested up to: 4.4.4
Stable tag: 1.3.7
Version: 1.3.7
=======

Gravity Forms Dynamics CRM Add-On allows you to quickly integrate Gravity Forms with Microsoft Dynamics CRM.

== Description ==
Easily create/update leads or contacts in Microsoft Dynamics CRM when a form is submitted.

== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
2. Activate the plugin
3. Follow the instructions here: [Setting Up the Gravity Forms Dynamics CRM Add-On](https://support.saintsystems.com/hc/en-us/articles/207843943-Setting-Up-the-Gravity-Forms-Dynamics-CRM-Add-On)

== Changelog ==

= 1.3.7
*	Fix for ADFS authentication.
* 	Fix to retrieve all SystemUsers.

= 1.3.6
*	Change to allow non-licensed users to be selected as lead/case owners.
* 	Changes to support upcoming case support.

= 1.3.5
* 	Small fix to array access after method calls for issues on some hosts and PHP versions.

= 1.3.4
* 	Small fix to cURL compatibility check.

= 1.3.3
* 	Fix small display issue with license key valid checkmark on plugin settings page.

= 1.3.2
* 	Added check for PHP cURL extension and version and associated admin notices.
*	Cleaned up script and styles loading.
*	Added additional logging to ADFS Auth client.

= 1.3.1
*	Squashed bugs related to license validation and on-premises ADFS authentication.

= 1.3.0
*	Added support for on-premises Dynamics CRM using ADFS
*	Added license activation/deactivation and check buttons to allow explicit activation/deactivate and license check
*	Added validation for Gravity Forms minimum version, WordPress minimum version and PHP minimum version
*	Cleaned up admin area to simplify UI
*	General code cleanup

= 1.2.0
*	Added merge tags for insert into Gravity Forms email notifications.
*	Default lead/contact owner to authenticated user if none selected.
* 	Fixed OptionSet field types for contact feed.
*	Fix issues for some clients on older versions of PHP 5.4 and 5.3

= 1.1.2
*	Additional logging and fix to reduce size of SystemUserSet response.

= 1.1.1
*	Added additional logging for debug purposes.

= 1.1.0
*	Support for custom fields.
*	Support for Picklist fields such as Lead Source.
* 	Updated to retrieve live entity metadata (entity fields) from Dynamics API.
*	Removed Azure AD Domain (no longer needed).

= 1.0.0 =
*	Initial release.
*	Create or update leads.
*	Create or update contacts.
*	Prevent duplicate leads by email.
*	Prevent duplicate contacts by email.

== Links ==

*	[Saint Systems](https://www.saintsystems.com/)

