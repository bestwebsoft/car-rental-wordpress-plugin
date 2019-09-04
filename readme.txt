=== Car Rental by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: cars, rental, renting, booking, retail, add cars, car rental plugin, renting cars, cars website, manufacturers, machine, vehicle
Requires at least: 3.9
Tested up to: 5.2.2
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create your personal car rental/booking and reservation website.

== Description ==

Car Rental plugin is a key tool which helps you to create fully functional WordPress website for renting cars. Add, edit and manage cars info, manufacturers, vehicle types, car classes, extras, orders, and many other advanced features with a single plugin.

Start your online car rental business on WordPress website today!

[View Demo](https://bestwebsoft.com/wordpress_demo_theme/renty/)

http://www.youtube.com/watch?v=Oq4pkADwTks

= Free Features =

* GDPR Compliant
* Add and manage:
	* Cars
	* Manufacturers
	* Vehicle types
	* Car classes
	* Extras
* Add widgets:
	* Car filters
	* Order info
* Manage orders
* Set working hours
* Customize minimum rental age
* Add slider with unlimited slides including:
	* Image
	* Title
	* Description
	* URL
* Set the order status:
	* Reserved
	* Free
	* In use
	* Custom
* Manage your car info:
	* Location
		* Choose from the list
		* Add custom one
	* Number of seats
	* Number of doors
	* Luggage quantity
		* Large suitcases
		* Small suitcases
	* Transmission
		* Unknown
		* Automatic
		* Manual
	* Air conditioning
	* Average consumption
	* Price per hour
* Edit your extras info:
	* Name
	* Slug
	* Parent
	* Description
	* Details
	* Price per hour
	* Quantity
	* Photo
* Choose currency:
	* From the list
	* Custom
* Set currency position
	* Before numerals
	* After numerals
* Choose the consumption unit:
	* l/100km
	* km/l
	* Custom
* Select the rent period per:
	* Hour
	* Day
* Enable select field for:
	* Pick up & drop off time
	* Return location
* Compatible with:
	* [Captcha](https://bestwebsoft.com/products/wordpress/plugins/captcha/?k=4da2d234b0a0d3eb784f4e489d22f1b9)
	* [Subscriber](https://bestwebsoft.com/products/wordpress/plugins/subscriber/?k=6afd5ac7a9888bf6ce52d8a53af54135)
	* [Google Captcha (reCAPTCHA)](https://bestwebsoft.com/products/wordpress/plugins/google-captcha/)
* Install/Delete demo data
* Add custom code via plugin settings page
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos
* Multilingual and RTL ready

> **Pro Features**
>
> All features from Free version included plus:
>
> * Add unlimited number of Cars and Extras
> * Get answer to your support question within one business day ([Support Policy](https://bestwebsoft.com/support-policy/))
>
> [Upgrade to Pro Now](https://bestwebsoft.com/products/wordpress/plugins/car-rental/?k=3f5c94058f6e182a4530050cbb63dd44)

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

== Documentation and videos ==

* [[Doc] Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)
* [[Doc] Purchase](https://docs.google.com/document/d/1EUdBVvnm7IHZ6y0DNyldZypUQKpB8UVPToSc_LdOYQI/)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help — <https://support.bestwebsoft.com/>

= Translation =

* French (fr_FR) (thanks to [Albert Bea](mailto:almadise84@yahoo.fr), www.readycar.fr)
* German (de_DE) (thanks to [Janes Lenz](mailto:mail@info-bit.org), www.info-bit.org)
* Russian (ru_RU)
* Ukrainian (uk_UA)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](http://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](http://www.poedit.net/download.php).

= Recommended Plugins =

* [Captcha](https://bestwebsoft.com/products/wordpress/plugins/captcha/?k=4da2d234b0a0d3eb784f4e489d22f1b9) - #1 super security anti-spam captcha plugin for WordPress forms.
* [Subscriber](https://bestwebsoft.com/products/wordpress/plugins/subscriber/?k=6afd5ac7a9888bf6ce52d8a53af54135) - Add email newsletter sign up form to WordPress posts, pages, and widgets. Collect data and subscribe your user.
* [Google Captcha (reCAPTCHA)](http://bestwebsoft.com/products/google-captcha/) – Protect WordPress website forms from spam entries with Google reCAPTCHA.
* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=0f949d8b3b87e3f7d52f08b79cb230a1) - Automatically check and update WordPress website core with all installed plugins and themes to the latest versions.

== Installation ==

1. Upload the folder 'car-rental' to the directory '/wp-content/plugins/'
2. Activate the plugin via the 'Plugins' menu in WordPress
3. You can adjust the necessary settings using your WordPress admin panel in "Cars" > "Settings".

== Frequently Asked Questions ==

= How can I add a Car? =

Please follow the next steps:
1. Click on "Cars" on the admin panel, then click on the "Add New";
2. Fill in all fields;
3. Click "Publish" button.

= How can I add a slide? =

Please complete the following steps:
1. Go to the "Cars" > "Slider" page in the admin panel.
2. Click "Add New Slide".
3. Fill in the necessary fields.
4. Click "Add Slide" button.

= How can I display the Slider on my homepage? =

If you want to use the plugin with another theme and enable the slider on your homepage, find the "header.php" file of your theme, and add the following code after `<header>` block or to any necessary place:
`<?php do_action( 'crrntl_display_slider' ); ?>`

= How can I add the Slider to a custom template? =

In order to display the Slider on any page (e.g. that uses some custom template), add the following code to the necessary place of the corresponding template file:
`<?php do_action( 'crrntl_display_slider_custom' ); ?>`

= How to change or override plugin templates? =

Plugin template files can be found in the '/wp-content/plugins/car-rental/templates/' directory.
You can edit these files in an upgrade-safe way using overrides. Copy them into a directory with your theme named '/bws-templates'.

Example: To override the 'BWS Choose Car' page template, please copy '/wp-content/plugins/car-rental/templates/page-choose-car.php' to 'wp-content/themes/your-theme/bws-templates/page-choose-car.php'.

Do not edit these files in the plugin's core directly as they are overwritten during the upgrade process and any customizations will be lost.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (https://support.bestwebsoft.com). If no, please provide the following data along with your problem's description:
- The link to the page where the problem occurs
- The name of the plugin and its version. If you are using a pro version - your order number.
- The version of your WordPress installation
- Copy and paste into the message your system status report. Please read more here: Instruction on System Status (https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/)

== Screenshots ==

1. Slider with the left search car form.
2. Slider with the right search car form.
3. Slider with the center search car form.
4. "Choose a car" page.
5. "Choose Extras" page.
6. "Review & Book" page.
7. "Car Rental Settings" page in the admin panel.
8. Slider settings on "Car Rental Settings" page in the admin panel.
9. "Add New Slide" page in the admin panel.
10. "Edit Slide" page in the admin panel.
11. "Cars Management" page in the admin panel.
12. "Edit Extra" page in the admin panel.
13. "Add New Car" page in the admin panel.
14. "Orders Management" page in the admin panel.
15. "Edit Order" page in the admin panel.

== Changelog ==

= V1.1.2 - 04.09.2019 =
* Update: The deactivation feedback has been changed. Misleading buttons have been removed.

= V1.1.1 - 07.02.2019 =
* Bugfix : Bug with the disappearance of checkboxes in the admin panel has been fixed.

= V1.1.0 - 06.02.2019 =
* Update : Settings page was updated.

= V1.0.9 - 08.01.2019 =
* Update : All functionality was updated for WordPress 5.0.2.

= V1.0.8 - 23.05.2018 =
* NEW : GDPR Compliance has been added.

= V1.0.7 - 06.03.2018 =
* NEW : Ability to choose the recipient of the order notifications has been added.
* NEW : Ability to change date format, set the minimum age to rent a car and select working hours has been added to the plugin.
* NEW : The compatibility with Google Captcha (reCAPTCHA) by BestWebSoft has been added.

= V1.0.6 - 18.07.2017 =
* Bugfix : The compatibility with Captcha by BestWebSoft has been fixed.

= V1.0.5 - 20.04.2017 =
* Update : The plugin's settings page has been updated.
* Update : 'cars' post type name has been changed to 'bws-cars'.
* Update : Functionality to remove unused locations has been added.
* NEW : User's name and email have been added to email and order info.
* NEW : Number of doors has been added to car's info.
* NEW : Ability to set price on request has been added.
* NEW : Ability to add Captcha by BestWebSoft has been added.
* NEW : RTL support has been added.
* NEW : The French language file has been added (thanks to [Albert Bea](mailto:almadise84@yahoo.fr), www.readycar.fr).
* NEW : The German language file has been added (thanks to [Janes Lenz](mailto:mail@info-bit.org), www.info-bit.org).
* Bugfix : Bug with max number of cars has been fixed.

= V1.0.4 - 21.10.2016 =
* NEW : Responsive design has been implemented.
* NEW : Slider script has been replaced with "Owl Carousel".

= V1.0.3 - 23.09.2016 =
* NEW : Rent option allowing to select time per hour/per day.
* NEW : Ability to disable "Return at different location".
* Bugfix : Price calculation for extras was fixed.

= V1.0.2 - 11.08.2016 =
* NEW : Ability to disable selecting pick-up&drop-off time.
* Update : All functionality for WordPress 4.6 was updated.

= V1.0.1 - 08.07.2016 =
* Bugfix : The phone number displaying has been added in dashboard and email.

= V1.0.0 - 09.06.2016 =
* NEW : Release date of Car Rental by BestWebSoft plugin.

== Upgrade Notice ==

= V1.1.2 =
* Usability improved.

= V1.1.1 =
* Bugs fixed.

= V1.1.0 =
* Functionality expanded.

= V1.0.9 =
* The compatibility with new WordPress version updated.

= V1.0.8 =
* Functionality improved.

= V1.0.7 =
* Functionality expanded.

= V1.0.6 =
* Bugs fixed.

= V1.0.5 =
* Functionality expanded.
* New languages added.
* Bugs fixed.

= V1.0.4 =
* Appearance improved.
* Bugs fixed.

= V1.0.3 =
* Functionality expanded.

= V1.0.2 =
* Functionality expanded.
* The compatibility with new WordPress version updated.

= V1.0.1 =
The phone number displaying has been added in dashboard and email.

= V1.0.0 =
Release date of Car Rental by BestWebSoft plugin.
