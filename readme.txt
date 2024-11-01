=== WP Replace Unlicensed and Broken Images ===

Contributors: CK MacLeod
Donate Link: http://ckmacleod.com/wordpress-plugins/#donate
Tags: copyright, takedown, images, usage rights, DMCA, licensing, SEO, site errors, broken links, CK MacLeod
Requires at least: 4.3
Tested up to: 4.7.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Selectively replace broken, unlicensed, and other problematic images on a site's public-facing "Front End." 

== Description ==

WP Replace Unlicensed and Broken Images (WP-RUBI) replaces images selected by date, post, category, author, file location, and/or image type with a user-adjustable default image on a site's public-facing Front End - in other words, on display - and will also, unlike common Javascript/jQuery solutions for broken images and links, prevent load errors and search engine "crawl" errors that can harm Search Engine Ranking. A primary use will be for sites where - due to policy change or concerns relating to usage rights and copyright infringement - administrators need to expunge images that have been used without permission or under lapsed or lapsing usage licenses, while avoiding a time-consuming, complex, hard-to-reverse, potentially costly and inefficient database purge. Another use will be for sites whose image archives have been corrupted or lost.

WP-RUBI will do nothing until the site administrator has determined which images or types of images to remove and replace. All post images and image links that are matched in posts selected either individually or globally - for instance, all posts from before a certain publication date - will be replaced by links to a simple, customizable "image removed" image when the page is displayed: The change is made just-in-time as the post is loaded, altering the page "source" as rendered, but *not* affecting the database. If at some future time the site operator wishes to restore lost images, or "whitelist" a post, author, category, time frame, or image type, then the original links and formatting information will still be easily accessible.

**WP-RUBI is a powerful plug-in, potentially allowing for far-reaching alterations in your site's appearance, but it makes no permanent changes to your posts database or image library or any other files: Changes can be immediately rolled back by resetting to defaults via Main Settings, or by de-activating (or uninstalling). Still, as generally with WordPress Plug-Ins, the further your installation diverges from a "basic" WordPress site - by employing unique frameworks, complex themes or plug-ins, or specialized customizations - and the higher your traffic, the more care and caution you should employ when installing, activating, and configuring WP-RUBI.**

Also note that WP-RUBI is designed with typical WordPress sites in mind. Though it can also be set to remove and replace copy-protected and externally served images (as long as display relies on `<img src=` tags), it may not function optimally, if at all, or may require careful adjustment, when used with sites or themes relying on unusual image delivery and display methods, or that significantly diverge from other standard practices: "gallery"-style themes that may extract image links from post content and convert them into CSS backgrounds, for example. More commonly, caching and copy protection plug-ins, CDNs lacking purge options, and so on, may need to be flushed and reset, or in some instances disabled, for WP-RUBI to achieve the intended results. 

###Background###

In recent years, with the maturation of the internet and especially of the "blogosphere," sites where photos and other images have been copied and displayed without concern for the rights of artists and services have come under enhanced scrutiny, sometimes resulting in costly lawsuits and threats of lawsuits. In addition, some site operators, especially as they have gotten more successful, have simply undergone a change in thinking about common internet practices viewed by some artists as theft.

Many or perhaps most bloggers still operate without concern for image licensing, and some have discovered - sometimes at significant cost - that, as one victim of a "copyright-trolling" lawyer put it, "Current Fair Use image copyright laws say that you’re financially liable for posting copyrighted images."\* You may be liable for unlicensed use of images even if:

* You did it by accident
* You immediately take down the picture after receiving a DMCA takedown notice
* The picture is resized
* The picture is licensed to your web developer
* You link back to the photo source and cite the photographer’s name
* Your site isn’t commercial and you make no money from your blog
* You have a disclaimer on the site
* The pic is embedded instead of saved on your server
* You found it on the Internet (that’s not an excuse!)

The core of this plug-in was originally implemented on a large site in one such predicament: The operators, after having been challenged by a copyright lawyer demanding thousands of dollars in payment for the use of a single unpaid-for image, and facing a steep legal bill even in pursuit of an eventually successful defense, decided to stop displaying any images whose usage rights were not fully cleared. 

The operators were left with thousands of unlicensed images associated with archived posts - along with many images that were used fairly (because original to the site or author, or in the public domain). Finding and replacing images as well as image links presented numerous complications: Simply deleting image archives rather than editing the posts would produce numerous load errors, which would harm the site's search engine ranking, produce unprofessional "broken images" display, and make restoration of good posts difficult where not impossible. Using a Javascript solution that obscured broken images and links might improve display, but would leave errors in place, and have no effect on unwanted images that were not deleted. 

###Using WP Replace Unlicensed and Broken Images###

WP Replace Unwanted and Broken Images enables an Administrator to bring his or her site into effective compliance quickly, and then to recover "good" images if desired, while preserving archives.  The plug-in adds numerous additional choices and other improvements to the earlier "WP Replace Old Images," making it easier for an Administrator to refine the "purge," and for the site's editors and authors to restore old posts and archives to full health.

Improvements include:

* Posts can be excluded or included via Edit Post
* Posts can be excluded or included via All Posts Quick and Bulk Editing
* Featured images (or thumbnails) also can be removed/replaced
* Easy Inclusions or exclusion from image removal/replacement for authors and categories, and for "after" as well as "before" dates
* Replacement images provided with "cache-busting" queries (so changes to Front End can take effect immediately)
* User can upload own replacement image from Settings page, or choose "blank" and "erasure" replacement modes
* Installation routine preserves old settings on upgrade
* Detailed usage notes/tips/documentation
* Translation-ready


*ALSO NOTE DISCLAIMER: Developer makes no promises or guarantees that use of this plug-in will secure a site against all claims of copyright infringement based on past practices. No one can do that!*

\*Description and list of Fair Use liability issues from ["The $8,000 Mistake That All Bloggers Should Beware"](http://www.contentfac.com/copyright-infringement-penalties-are-scary/)

== Installation ==

Use the normal WordPress add new plugin routine, or:

1. Download the plugin.
1. Upload the plugin to your site's wp-content/plugins directory.
1. Activate the plugin in the WordPress Admin Panel.

After Activation:

1. If upgrading from WP Replace Old Images, make sure settings have transferred properly, then delete
1. Adjust settings to include or exclude images and image-links for replacement and removal
1. Include or exclude Posts and Pages individually via Edit Post or All Posts Quick/Bulk Edit
1. Verify results, deleting or purging site, browser, and CDN caches if necessary

== Changelog ==

= 1.0.5 =

Removed source of possible "function write" errors for users with pre-5.5 PHP; PHP usage; readme revisions/corections 
Thanks to [thatgrrl](https://profiles.wordpress.org/thatgrrl)!

= 1.0.4 =

Made warnings/advice for use in non-standard installations more emphatic; other minor revisions/housekeeping.

= 1.0.3 =
Upgraded upgrade routine. Additional minor edits. 

= 1.0.2 =
Tested for 4.6. Fixed some typos and other minor problems that will not affect users until and unless there are translations.

= 1.0.1 =
Missing closing div in "badge"-link - fixed.

= 1.0 =
*First version of the plugin*


== Upgrade Notice ==

= 1.0.1 =
Added closing div in "badge"-link: Without it, the badge-link may disrupt layout. 

= 1.0.3 =
Ensures revisions - including to upgrade routine itself - occur whether upgraded "silently" via automatic processes or manually via re-installation.

= 1.0.5 =
Better PHP usage, checked for 4.7.3 compatibility

== Frequently Asked Questions ==

*So, like how much of a problem is copyright-trolling really?*

Setting aside the "moral" issue - on respecting the rights and interests of creative artists - given the zillions of blogs out there, including popular ones operated for a profit and still using whatever images they can find without even crediting the sources - you might not think it's a big deal. I didn't think so myself - until I found myself working at a site where the editors/administrators had received a demand letter from an aggressive lawyer threatening to sue, demanding a settlement payment, and putting the future of the site itself in peril just for the costs of defending themselves legally. Seemed like a big deal to me as a developer when I discovered that the editor-in-chief, who didn't really know what he was doing, had deleted whole image folders without even making a backup first...

*OK, but how do we get good images for our site then?*

Colleagues and I have had great experiences with [ImageInject](https://wordpress.org/plugins/wp-inject/), a plugin by Thomas Hoefter and WPscoop that makes searching, using, and crediting images from Flickr and Pixabay easy - and fun. You can also do pretty well searching [Wikimedia Commons](https://commons.wikimedia.org/wiki/Main_Page). US government-produced images - like NASA images - are generally by definition in the public domain. You're probably safe with old advertising and fine art, with publicity images generally, and often with screen captures used for critical purposes - i.e., to criticize [television station] for its coverage, but not to steal their images or graphics - but at that point you're starting to get at gray areas and danger zones, as there are potential exceptions to every rule. The biggest danger seems to be using - stealing - images from aggressive, rights-protecting news photo vendors and also from individual creative artists. On the the other hand, there are a lot of photographers and artists out there who will grant a license to their work in exchange for a credit. Sometimes you can find them at places like [DeviantArt](http://creative-commons.deviantart.com/). Then there's always the option of creating your own illustrations...

...or, if you really, really want just the right professional-quality image you can even, gulp!, pay for it.

*What're "load errors" and "crawl errors," and why should I care and not just go with a "lightweight" Javascript solution?*

If you've found a Javascript solution that works for you as far as replacing broken images goes, maybe that'll be fine! However, Javascript broken image solutions typically *do not* eliminate the errors they "fix." In fact, they entirely depend on them to function. Leaving image errors in place will produce slow loads on affected pages, which may be bothersome to users going through your archives, and both the slower loads and the existence of the errors (both broken images and bad links) will (or are said to) harm your site's Search Engine Rankings - more on load errors at ["Comparative Page Loads with and without Image Errors"](http://ckmacleod.com/2016/07/14/comparative-page-loads-without-image-errors/).

*I think I set everything right, but, when I look at the results, the old images are still there! Why?*

As noted elsewhere - for instance, under Usage Notes and in the description of this plug-in - you may need to clear (delete, purge, flush, etc.) site and other image caches, including Content Delivery Network caches. In some instances - for instance with JetPack's Photon Module, which does not include a clearing option - de-activating it for a a couple of weeks seemed to solve the problem on re-activation, but this experience should not be taken as a guarantee: You may need to try [some other CDN](http://www.wpexplorer.com/free-cdn-services-for-wordpress/), or go without one for a while, if you want to use WP-RUBI. 

You can also try restoring Photon or other problematic add-on back after you've "cleared" your site, but check the results first to be sure that old images are not still displaying.

*OK, but what if I want to have one kind of replacement image for one type of post, and one for another, or randomly select from a gallery of images... and what if I want to divide up different time segments intead of just using one timespan, and.. and...*

[Let me know](http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/get-replace-images-support/), and I'll think about adding the capacities and options to a future version of the plug-in... or I guess we can discuss a custom job if it's urgent! (Though that thing about selecting from a gallery you could already get if you linked to a "rotater" PHP file...)

*Why do my old thumbnail images show up in different proportions from new ones in All Posts?*

That would be because the new size for "Featured Preview" thumbnails is generated when a post is uploaded. If you'd like to generate the new size retroactively, because you're kind of a stickler for that kind of thing, you might want to try the [Regenerate Thumbnails Plugin](https://wordpress.org/plugins/regenerate-thumbnails/).  

== Screenshots ==

1. Settings Page 
2. Before
3. After
4. After - Selective
5. New Columns in All Posts and Quick Edit

For more visit the WP-RUBI [image gallery]((http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/screenshots/).

== Additional Info ==

= Still to Come =

1. "Recuperation" workflow - list and select cured/uncured posts/authors/categories
1. Additional image selection patterns and inclusion/exclusion options
1. Optional database purge
1. Development of Digital Artists Alliance site and network

Check the [WP-RUBI home pages](http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/) for additional background, usage tips, and minor updates, or to [contact the developer](http://ckmacleod.com/wordpress-plugins/wordpress-replace-unlicensed-and-broken-images/get-replace-images-support/).

= Thanks! =

To all of the developers and everyday code-hackers, far too numerous to name, upon whose work I have depended. And  Thanks to [thatgrrl](https://profiles.wordpress.org/thatgrrl) for catching a bad link to the plug-in's home page!