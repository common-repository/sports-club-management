=== Sports Club Management ===
Contributors: pstruik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6FNGJ754V93UY
Tags: sports, club, sportsclub, club management, member, competition, league, knockout, bracket, match, team, competitor
Author URI: http://www.sportplugins.com
Plugin URI: http://www.sportplugins.com
Requires at least: 4.0
Tested up to: 6.1
Stable tag: 1.12.9
License: GPLv2 or later


Create members, competitions (leagues, ladder, knockout) (and, optional, invoices) for your (sports) club. Easy to manage and to publish on your site. For all sports.

== Description ==

This plugin saves you a lot of work and time in managing your club. You can manage data for all your members, manage your club's competitions and matches, and manage invoices.   

You can start small by creating members. By enabling the competitions feature, you can create as many competitions for your members as you wish. There are built-in competition formats for leagues, knockout tournaments, and individual scoring (applicable for golf, shooting, trainings statistics, etc.); all for individual competitors and teams. Plan your matches and add scores. Rankings are automatically generated. It's optional to create invoices (e.g. for the annual fee that members pay). 

A full set of shortcodes and widgets enables publication on your WordPress site.

This plugin in written with extendability in mind to support any sport and competition format. 

Unlike other sports plugins, you not only manage and publish matches and leagues, but everything is centered around the most important people in your club: your members. A member can readily get an overview of his/her personal data (address, e-mail, etc.), of his/her invoices, and of all completed and open competitions.

= Main Features =

**Manage members**

* Typical fields are name, address, phone, e-mail, etc. 
* Extend pre-defined fields by defining up to 4 custom fields (e.g. for a bank account, membership number of your national organization, etc.)
* Use member categories to partition your members into groups
* Import members from a csv-file
* Export member data to a csv-file
* Option to exclude privicy related data from publishing
* Content for the member can be entered like done for a normal post
* Members can have a featured image
 
**Manage competitions**

* Adding competitions is included, but optional
* Create competitions
* Built-in formats: league, knockout tournament, ladder, and individual scoring
* Matches and competitor data is automatically generated at competition creation
* Types of competitors: individual member, team (of members), external competitor (by name)
* Knockout tournament forwards the winner of a match to the next match automatically
* Individual scoring supports a user-configurable number of rounds (example: choose 9 rounds for a golf competition over 9 holes)
* Ranking is computed automatically
* League Ranking supports several scoring systems, like Win-Draw-Loose equals 2-1-0 or 3-1-0, or 'points won'
* Individual Scoring Ranking supports a number of scoring systems, like sum of rounds, sum of (the N best of) multiple rounds, average sum of multiple rounds
* Competitions that belong together can be grouped
* Export competition and match data to a csv-file
* Content can be entered for competitions, matches, and competitors (e.g. a team)
* Competitions, matches, and competitors (e.g. a team) can have a featured image
 
**Manage invoices**

* Adding invoices is included, but optional
* Typical fields are service, invoice date, due date, etc.
* Extend pre-defined fields by defining up to 2 custom fields 
* Create an invoice for a single member
* Keep track of payment status
* Create invoices for all member in a specific member category ("bulk creation")
* Export invoice data to a csv-file
 
**Shortcodes (see documentation for a complete overview)**

* Publish member data, member lists
* Publish invoice data 
* Publish competitions, matches, rankings, and teams
* Publish current matches (within a date window relative to today)

**Widgets**

* Display member birthdays (within a date window relative to today)
* Display current matches (within a date window relative to today)

**Generic features**

* Need admin role to modify general settings and options
* Need editor role to modify members, invoices, competitions, etc. (prevent authors from editting your club's administration)

= Planned Features =

* New competition formats and options
 
= Documentation =

The plugin's [documentation](http://www.sportplugins.com) includes a user manual, shortcode definitions

= Language Support =

Get involved and start translating this plugin into your own language using [WordPress PolyGlot](https://translate.wordpress.org/projects/wp-plugins/sports-club-management). Use the [contact form](http://www.sportplugins.com/contact/) to get in touch to become a translation editor for your language.  


== Installation ==

This plugin works like any standard Wordpress plugin. It does not depend on the installation of other plugins or tool packages.

Whenever installing or upgrading any plugin, or even Wordpress itself, it is always recommended you back up your database first!

= Installing =

1. For installation, go to Plugins > Add New in the admin area, and search for Sports Club Management.
2. Click install, once installed, activate and you're done!

Once installed, you can [get started](http://www.sportplugins.com/documentation/getting-started) and add member data. By default, managing competitions and managing invoices has been disabled. Enable these feature from the 'settings page', when needed. 

== Upgrade Notice ==

Plugin ported from a proven solution that exists for many years.

== Frequently Asked Questions ==

= Does this plugin only have a single FAQ? =

No, more information can be found on the plugin's [site](http://www.sportplugins.com)


== Screenshots ==

1. List of members in admin panel
2. Creating (or updating) a new member
2. Creating (or updating) a new invoice
3. After leage creation
4. After knockout creation
5. Member data on site
6. Competition information on site
7. Team information on site


== Other Notes ==

This plugin has been written with extension in mind. For example, it contains action hooks and filters to add new competition formats. 

== Changelog ==

= 1.12.9 =
* [fix] deprecated function in php 8

= 1.12.8 =
* [fix] import members from CSV: city was not handled correctly
* [fix] new competitor did not correctly assign the competition
* [enh] added new competitor creation to admin bar
* [enh] competition (group) now use Gutenberg editor by default

= 1.12.7 =
* [fix] in league ranking: game difference in multi-set match is now taken into account

= 1.12.6 =
* [enh] removed automatic creation of matches for a league (use bulk addition of competitors/matches instead)
* [enh] wizard for bulk addition of competitors to a competition
* [enh] wizard for bulk addition of matches to a competition
* [enh] wizard for bulk addition of members to a team

= 1.12.5 =
* [enh] wizard for improved bulk creation of members from a CSV file (does not require admin role anymore)
* [enh] wizard for improved bulk creation of invoices (does not require admin role anymore)

= 1.12.4 =
* [NEW] competition format 'ladder'
* [enh] add option to take best N results in ranking for individual format
* [fix] comma in field ended up in wrong column when exporting competitions

= 1.12.3 =
* [fix] exported CSV files declare comma as seperator

= 1.12.2 =
* [enh] add parameter to invoice shortcode to provide more displaying flexibility
* [enh] add more information to invoice metabox on member page in backend
* [fix] for bug that invoice data was shown when user not logged in 
* [enh] add category column to admin pages for competitions and competition groups
* [fix] exclude invoices, competitors, and team players from front-end search

= 1.12.1 =
* [enh] added option to customize separator between rounds in a match result
* [enh] in case a match result is the sum of all rounds, the sum is appended to the match result 

= 1.12 =
* [enh] improved scm_members shortcode by adding new features
* [fix] fix shortcodes (see documentation on the plugin's [site](http://www.sportplugins.com))

= 1.11 =
* [enh] added competitor's thumbnail in shortcode for listing a team
* [NEW] competition format 'individual' 
* [NEW] using WordPress Translate now 
* [fix] removed warning in match data shortcode for knockout match that is a 'bye' 

= 1.10 =
* [enh] 2 new options for widget to show current matches
* [NEW] shortcode to show current matches 
* [enh] clean-up and fixes for the 'individual' competition format
* [fix] finished conversion to full CSS support with plugin specific spclmgt.css (all tables have been converted)

= 1.9 =
* [NEW] competition format: individual (beta) 

= 1.8 =
* [enh] added widget to show list of current matches 
* [enh] added spclmgt.css file for better style customization (and converted tables (partly))

= 1.7 =
* [enh] added widget to show list of birthdays 
* [fix] ranking league
* [fix] prevent excel converting scores into dates for exported csv-files

= 1.6 =
* [enh] Extend team_data shortcode to show contact info for team members 
* [enh] Add export of competitions (matches) to a csv-file

= 1.5 =
* [fix] Matches metabox sometimes empty
* [fix] Draw not handled correctly 
* [enh] Include shortcode overview in admin sections for improved useability

= 1.4 =
* [fix] Change text-domain for wordpress language pack
* [enh] Refine score format 
* [enh] Improve 'scm_match_data' shortcode

= 1.3 =
* [enh] New shortcode 'scm_members' to display list of all members
* [enh] Additional privacy setting to display contact info 
* [enh] Add parameter to 'scm_match_data' to determine order to display matches (by date: ASC or DESC)
* [enh] Code cleanup; tidy admin section

= 1.2 =
* [enh] show 'disable in ranking' for League format only
* [enh] Tidy admin section
* [fix] Knockout format: error message in ranking
* [fix] Knockout format: add link to competitors in admin section
* [fix] New match /w no competition shall not show any competitor

= 1.1 =
* [enh] Add competition group in team selection dropdown list for TeamPlayer
* [enh] Tidy displaying of active TeamPlayer in admin section

= 1.0 =
* First version, including members, invoices, competitions (league, knockout)
* Created from existing website bcnuenen.nl and ported to a plugin

