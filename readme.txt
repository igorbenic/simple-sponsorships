=== Simple Sponsorships ===
Contributors: ibenic
Tags: sponsorships, sponsors, payment
Requires at least: 4.4
Tested up to: 5.0.3
Stable tag: 0.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept Sponsorships for any type of site, event or product. Manage & Display Sponsors.

== Description ==

Simple Sponsorships is a complete Sponsorships manager plugin where you can:

* accept sponsor requests
* receive payments from approved sponsorships
* manage and display Sponsors
* create different sponsorship packages

If you want to handle payments differently, you can disable them and instruct sponsors with detailed information on how you will accept payments.

== Installation ==

1. Install it through the Plugins menu page
1.1. Upload `simple-sponsorships` to the `/wp-content/plugins/` directory or
1.2. Upload `simple-sponsorships.zip` under Plugins > Add New

2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How many Sponsors can I have? =

As many as you want. Sponsors are regular WordPress post types so your database size is the only limitation.

= Do you support payment gateways? =

PayPal is available in this version as a payment gateway.

= Can I accept payments offline? =

Yes, you don't have to use a payment gateway such as PayPal.
You can disable that (disabled by default) and set payment instructions for your sponsors.

= How can I assign Sponsors to an Event or Post? =

Under Sponsorships > Settings, you can enable various Post Types.
Then, under each post, you will have a metabox where you will search for sponsors to add.
Sponsors that will show are sponsors that have their available quantity higher than zero (0).
Each time you add a Sponsor to a post type, the quantity is reduced.

= Can a Person/Company sponsor more than 1 content? =

Each package has a Quantity option.
If you set the package quantity to 2, when a sponsor purchases a sponsorship for that package, the sponsor will get 2 additional quantities.
You can then add the sponsor to 2 more content.

= What happens if I remove a Sponsor from a Content? =

That sponsor will get 1 quantity back which you can then use to assign the sponsor to another content.

= How can I show Sponsors on my site? =

You can use a widget, shortcode [ss_sponsors] or block.

You can use the provided Widget and decide how to display them. You can choose to display all sponsors or for the current content.
If the "Current Content" is selected, it will display only the sponsors of the current content.

You can also use the shortcode [ss_sponsors] where there are a few options. You can use the option all="1" to show all sponsors.
By default it will show the current content sponsors. If the option content is set for example content="1" it will check for the sponsors of the post with ID of 1.
If you want to use logo, you will use logo="1" or to hide use logo="0". By default, it will show.
If you want to show description, you will use text="1" or to hide, use text="0". By default, it will show.

If your site is also using the new WordPress editor (Gutenberg), then you can also use blocks for showing Sponsors or Packages.

== Screenshots ==

1. A screenshot of Sponsors.
2. Adding a Sponsor to a Content.
3. List of Sponsorships.
4. Sponsorship Page.
5. List of Packages.
6. Single Edit Package Page.
7. Settings.
8. Payment Settings.


== Changelog ==

= 0.4.1 =
* Fixed: Settings did not save.
* Fixed: Forms did not process.
* Fixed: Reject Reason was not hidden at first when adding a new Sponsorship

= 0.4.0 =
* Adding package parameter to shortcode ss_sponsors
* Adding WP Editor for the Package description field
* Adding Sponsors Block
* Adding Packages Block
* Package Button pre-selects the package on the Sponsor Form

= 0.3.0 =
* Added Rejected Status on Sponsorships
* Added Email for Reject status on Sponsorships
* Improving search for available sponsors on content (excluding already added ones)
* Added Filters for Sponsorships (By Sponsors, Status and Package)

= 0.2.0 =
* Added Column Available Quantity on Sponsors
* Added Styles for Sponsors in widgets and under content
* Added a setting for displaying sponsors under the sponsored content
* Showing only available sponsors when searching them for a content (to add them)

= 0.1.1 =
* Sanitized and escaped data.

= 0.1.0 =
* First beta version.

== Upgrade Notice ==

= 0.1.0 =
First update and push.
