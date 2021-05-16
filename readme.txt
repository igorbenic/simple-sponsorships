=== Simple Sponsorships ===
Contributors: ibenic
Tags: sponsorships, sponsors, payment, podcasts, events
Requires at least: 4.4
Tested up to: 5.7.2
Stable tag: 1.8.0
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

This plugin can help you manage your sponsors, sponsor requests and all other related sponsor data.

**How this plugin works?**

1. A potential sponsor comes to your page. It goes to the Sponsor Page (it will create a /sponsor page),
2. Fills the Sponsor Form and you get an email,
3. A Sponsorship is created which you can then approve or reject,
4. Once the Sponsorship is approved, the Sponsor is created from the provided information,
5. If you want to accept payments through your site, you can use PayPal and the Sponsor will use it to pay for Sponsorship,
6. Once paid, the Sponsorship will become active and the Sponsor can be added to a content

Simple Sponsorships, to handle Sponsors, is composed of different types of data:

- **Sponsors** - can have more than 1 sponsorship. Edited and managed as regular posts, pages and similar,
- **Sponsorships** - connected to a sponsor and a package. Holds payment data,
- **Packages** - packages are like products in a shop. A sponsor can choose a package to purchase and then the Sponsorship is created.

**Use Cases**

***Podcast Site***
You have a podcast site where you accept Sponsors for your episodes.

Each month has 2 episodes. 1 year (season) has 24 episodes in total. 1 Episode sponsorship is $100.

You don't accept sponsorships for one episode but you actually have different packages:

1. Half Season: You create a package with a quantity of 12 and the amount of $1200.
2. Full Season: You create a package with a quantity of 24 and the amount of $2400.
3. Early-Bird Full Season: You create a package with a quantity of 24 and with a lower amount of $1800.

Once a sponsor pays for the Half Season, that sponsor will get an additional available quantity of 12. You can now assign this sponsor to 12 new episodes.

***Event/WordCamp Site***
You have a WordCamp site where you accept Sponsors for the WordCamp, a single event.

There are different ways people can sponsor your event and what they get for it.

So here are some of the sponsorship packages you can create

1. Platinum: enter and explain this package. This sponsorship is a huge one and it will give the most exposure. The quantity is 1 and the amount is $5000.
2. Gold: Gold sponsor has less exposure than platinum but still, they even get a place in the WordCamp venue where they can show what they do. Quantity is 1 and the amount is $2000.
3. Freelancer: This sponsorship package is suited for people who want to get a little more exposure in the Thank you note of the WordCamp. The Quantity is 1 and the amount is $150.

Each sponsor will get an additional available quantity of 1, but you can still show them anywhere with the shortcodes. Since this sponsorships are global on the whole event, you don't have to assign them to a content.

**Planned Features**

Some of the planned features to be implemented soon:

- Package availability - option to disable a package from being purchased anymore. Still active, but can't be purchased anymore.
- Ability to add more packages to a single sponsorship (Sponsor can choose more than 1 package option)
- Add more gateways
- Setup Wizard - for a better 1st time setup

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

= 1.8.0 - 2021-05-17 =
* New: Account tabs moved to right side.
* New: Ordering Sponsorships on Account Page from newest to oldest.
* New: Totals Report in Admin
* New: (Platinum) Showing Subscriptions in separate navigation

= 1.7.0 - 2020-11-10 =
* New: Sponsor Reports - Sponsors can now check how much their link has been clicked.
* Update: Freemius service updated.
* New: (Platinum) Package Pricing Tables - using order of entered package Ids to show them.
* New: (Premium) Restrict Content - Using ss_restrict_content to restrict part of content.

= 1.6.1 - 2020-05-06 =
* Fix: Adding new package shown an error due to an incorrect name in column.

= 1.6.0 - 2020-04-30 =
* New: Account - Sponsored Content. Sponsors can see what content they have sponsored. This way they can see all episodes/articles that they have sponsored.
* New: Placeholder Icon - You can now change the placeholder icon (add image HTML or SVG HTML).
* New: Sponsors Widget - Column option added so you can use the widget in page builders as well and provide a column layout.
* New: (Platinum) - Recurring Payments (Subscriptions). Create Recurring Plans/Packages and receive payments for them by daily, monthly or yearly frequency.
* Fix: If there are no available packages when using multiple packages option, there will be a message to tell the users there are no packages available for now.

= 1.5.2 - 2020-03-12 =
* New: Filter ss_create_sponsorships_package_availability_check where 2 values are passed null and array of packages. This can be used by other extensions to check if package is really available.
* Fix: (Premium) Package Slots were not taking into account the currently posted quantity of a package to check availability (Thanks Matt Medeiros from mattreport.com).


= 1.5.1 - 2020-03-11 =
* Fix: When Multiple Packages was off, the dropdown for packages would not show any package. (Thanks Matt Medeiros from mattreport.com)

= 1.5.0 - 2020-01-24 =
* New: Account - Potential sponsors can create an account and use it to see their sponsorships and sponsored content.
* New: (Premium) Package Minimum Quantity - set a minimum quantity that can be purchased for each package.

= 1.4.1 - 2019-11-29 =
* Fix: Package quantities were not calculated correctly when the sponsorship was paid for.
* Fix: (Premium) Package Slots were setting packages as unavailable even if the slots were set to be empty/0 which means they were not used.

= 1.4.0 - 2019-11-18 =
* New: Placeholder Widget. Add a Call to Action widget to get sponsors much faster.
* New: Columns option added to Packages shortcode and block.
* New: Hide title of sponsors in the shortcode/block (shortcode attribute is hide_title=1).
* New: Placeholder Text can be changed under Settings.
* New: Click on the sponsor logo will not open in a new window/tab.
* New: Terms and Privacy Policy page settings. Terms page used in the Terms and conditions checkbox.
* Fix: Sponsor metabox for showing sponsored content was showing all content that has any sponsor. Fixed to show only the ones sponspored by that sponsor.
* Fix: When clicking to sponsor a specific content, the sponsor form showed all text if the More tag was not used. Now it shows the excerpt.
* Fix: When using Multiple Packages, when clicking on a package purchase button, that package did not have the quantity updated.

= 1.3.0 - 2019-09-03 =
* New: Option to allow the purchase of multiple packages.
* New: Email option under Settings > Emails to define where should sponsorship emails be sent to.
* New: Shortcode attribute link_sponsors (0 or 1). Default is 1 to link the sponsors.
* New: Sponsors Block got the Link Sponsors option.
* New: Integrations page where you can activate or deactivate various integrations (for example: Gravity Forms).
* Fix: Sponsor website was not used when linking sponsors.
* Fix: (Premium) Stripe JS loaded even if Stripe was not enabled.

= 1.2.2 - 2019-07-09 =
* Fix: Payment Form incorrect classes.
* Fix: JavaScript code for handling payments was not loaded.
* Update: Licensing software to newest version.

= 1.2.1 - 2019-07-03 =
* Fix: When sponsorship request is made, two sponsor data would be added on accidental double refresh.
* Fix: (Premium) Stripe was using wrong amount and currency.

= 1.2.0 - 2019-06-19 =
* New: Content Availability - Set how much a content can have sponsors.
* New: Sponsor Form available as a Block.
* New: Sponsors Widget can also display a description.
* New: (Premium) Stripe Payment Gateway
* New: (Platinum) Package Timed Availability - Set the date range when the package is available.
* Refactor: Moved Premium translations out of the Free version.

= 1.1.0 - 2019-06-02 =
* Fix: Package Meta Table was not Installing
* Fix: Error in JavaScript for Blocks where it was using the JavaScript object used elsewhere
* New: Each Sponsor Form can display different packages to choose from using the attribute packages.
* New: (Premium) Sponsors can edit their information and upload an image once the Sponsorship is paid.
* New: (Platinum) Package Features (list) and Package Pricing Tables (block and shortcode).

= 1.0.0 - 2019-05-05 =
* New: Introduced Sponsorship Items for a more stable future development.
* New: Redesigned the admin Sponsorship page introducing the new sponsorship items table.
* New: Added Heading option in Packages Block
* New: Placehoder under each content to drive more sponsorships
* New: Sponsor will be automatically added to a content if it arrived from a placeholder
* New: Freemius integration
* New: (Premium) Package Slots

= 0.7.0 - 2019-03-29 =
* New: Gravity Forms Integration.
* New: Prices/Amounts are formatted with the number format.
* New: Sponsor microdata from Schema.org added to each Sponsor.

= 0.6.0 =
* Fix: When a sponsorship has been paid, the emails are not sent twice.
* Fix: Package description does not use the the_content filter anymore.
* New: Added Email Link Color.
* New: Added PayPal documentation on PDT and IPN settings under the PayPal Gateway settings.
* New: Package Statuses. You can now leave the package as Unavailable, and people won't be able to choose that package.
* New: Package prices show in the Sponsor form.


= 0.5.0 =
* New: Ability to add/remove available sponsor quantities on the Sponsors list
* New: Ability to change the available sponsor quantity while editing a Sponsor
* New: Displaying all the sponsored content on a Sponsor edit page

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
