1.7.4 *August 23, 2017*
---
* Performance - Database optimizations


1.7.3 *August 15, 2017*
---
* Performance - Implement new background processor


1.7.2 *July 10, 2017*
---
* Tweak - Various minor internal updates
* Fix - Rare PHP notice on the account page


1.7.1 *June 23, 2017*
---
* New - Added search functionality to referral codes and advocates admin views 
* New - Add customer referral count rules **Customer Approved Referral Count**, **Customer Total Referral Count**, etc
* Tweak - Button icons are now included as a custom font rather than relying on FontAwesome


1.7.0 *May 8, 2017*
---
* New - Added admin page to view and manage referral codes
* New - The advocates admin page now lists any users who have sent an invite in addition to users with referrals
* New - Added filtering to referral invites admin page
* Tweak - Internal refactoring and UI improvements


1.6.2 *April 28, 2017*
---
* New - Added rule **Advocate Total Referral Count**
* Tweak - All advocate referral count rules now work on any trigger with a user object, previously it was just referral based triggers


1.6.1 *April 21, 2017*
---
* New - Any store credit applied to an automatic subscription renewal is now available when completing that order manually, e.g. if payment fails
* Tweak - Added WPML support for batched invite emails


1.6.0 *April 8, 2017*
---
* New - Added option to block sharing from users who have not placed an order
* New - Added pending referrals count to admin menu
* New - Added separate default share text for Twitter
* New - Added option to set minimum purchase amount for store credit use
* New - Made relevant settings translatable with WPML
* Tweak - Modified referral widget CSS to improve appearance for different widths
* Tweak - Hide store credit coupon notices as they would be confusing for the end user
* Tweak - Namespacing and performance improvements
* Fix - Issue where store credit could not be applied to an initial subscription payment if the cart total was zero


1.5.3 *April 7, 2017*
---
* Fix - WooCommerce 3.0 issue with referral coupons not being added to cart


1.5.2 *March 28, 2017*
---
* Fix - Ensure store credit coupon is applied after normal coupons
* Fix - Remove coupon added message when store credit coupon is applied
* Fix - WooCommerce 3.0 issue with store credit coupon


1.5.1 *March 17, 2017*
---
* New - Added variables **user.referral_coupon** and **user.referral_link**


1.5.0 *March 13, 2017*
---
[Read release post...](https://automatewoo.com/refer-friend-version-1-5-released/)
* New - Changed to store credit system due to WooCommerce 3.0
* New - Referral reward amounts are now editable in backend
* New - Option to set referral rewards to percentage based
* New - Added two new shortcodes


1.4.2 *March 4, 2017*
---
* Fix - Error in admin referrals list if the advocate of a referral had been deleted


1.4.1 *March 1, 2017*
---
* Fix - Issue where a fixed coupon discount could apply to more than one product
 

1.4.0 *February 25, 2017*
---
* New - Added ability to give referral rewards for new account signups
* New - Allow use of advocate.first_name and advocate.full_name variables in the **Default Share Text** option 
* New - Implement changes from AutomateWoo core such as new object caching and namespacing
* New - Begin support for WooCommerce 3.0


1.3.5 *January 12, 2017*
---
* Tweak - Added filter to modify referral reward amount 'automatewoo/referrals/reward_amount'
* Fix - Minor issue with the referral update_status() method


1.3.4 *January 3, 2017*
---
* Fix - Issue where database tables might fail to install on plugin activation
* Tweak - Added filter to invite email class before sending 'automatewoo/referrals/invite_email/mailer'


1.3.3 *December 18, 2016*
---
* Fix - Date comparision issue on some server environments


1.3.2 *December 15, 2016*
---
* Tweak - Added filter 'automatewoo/referrals/invite_email/variable_values' to allow creation of custom variables in the invite email content
* Fix - Modify account tab template to prevent duplicate messages


1.3.1 *December 8, 2016*
---
* New - Added filters to account tab text and minor improvements to account tab template


1.3 *December 5, 2016*
---
* New - Added admin view listing advocates and new related metrics
* New - Data for email invites is now being stored and displayed in a new admin view
* New - Added option to display the referral share widget on order confirmation emails
* New - Advocates now see their total credit in the referral account tab and always see a link to the share page 
* Fix - Store credit now accounts for fees added to orders 


1.2.16 *November 21, 2016*
---
* New - Added status filtering on admin referrals page
* New - Added filter 'automatewoo/referrals/generate_advocate_key'


1.2.15 *November 17, 2016*
---
* Fix - Issue where translations failed to load


1.2.14 *November 16, 2016*
---
* New - Send referral emails in batches if more 5 emails are shared at once
* Tweak - Break up frontend templates into smaller parts so they are easier to customize


1.2.13 *November 6, 2016*
---
* Fix - Ensure share page inputs are 100% width
* Fix - Potential PHP warning in my referrals account tab


1.2.12 *November 4, 2016*
---
* Tweak - When existing customers referrals are enabled referrals are limited to 1 referral for the customer
* Tweak - Changed the default minimum coupon value setting from 100 to 0 
* Fix - Make email 'Send' button translatable


1.2.11 *November 4, 2016*
---
* Tweak - Add filter 'automatewoo/referrals/block_existing_customer_share'
* Fix - Error if add-on was activated without AutomateWoo installed


1.2.10 *November 2, 2016*
---
* Tweak - Order counting functions now excludes cancelled, failed and refunded orders 


1.2.9 *November 1, 2016*
---
* Feature - Added option to allow existing customers referrals


1.2.8 *October 31, 2016*
---
* Tweak - Added support for custom checkouts that do not require the billing email field


1.2.7 *October 27, 2016*
---
* Performance - Added database indexes for all custom tables
* Tweak - Updates and clean up as per AutomateWoo 2.7


1.2.6 *October 19, 2016*
---
* Fix - Issue where tax on the store credit was not factored into to credit reduction query


1.2.5 *October 14, 2016*
---
* Tweak - Workflow log modals now show the relevant referral info
* Tweak - Follow through AW core changes to logs
* Tweak - Added shortcode [automatewoo_referrals_account_tab] to display referral account tab if using a custom account tab plugin
* Fix - Issue where fraud notice would display unnecessarily


1.2.4 *October 11, 2016*
---
* Tweak - Improved frontend CSS theme compatibility
* Fix - Issue with Divi page builder


1.2.3 *September 27, 2016*
---
* Fix - Tax issues around referral store credit


1.2.2 *September 15, 2016*
---
* Tweak - Added wc_print_notices() to the share page template in case notices did not get printed in the header
* Minor tweaks and fixes to admin area


1.2.1 *September 8, 2016*
---
* Minor tweaks and improvements


1.2 *August 29, 2016*
---
* Feature - **Link Based Referrals** are possible as an alternative to coupons referrals
* Tweak - Coupon expiry can now be disabled if expiry is set to '0'
* Tweak - Improved coupon validation


1.1.11 *August 25, 2016*
---
* Tweak - Implement changes in AW 2.6.1


1.1.10 *August 17, 2016*
---
* Feature - Add some referral rules ready for AW 2.6
* Fix - PHP list table warning


1.1.9 *August 7, 2016*
---
* Fix - Login button on share page missing link
* Fix - Minor PHP warning 


1.1.8 *August 2, 2016*
---
* Tweak - Update renamed class in AutomateWoo core 


1.1.7 *July 27, 2016*
---
* Tweak - Tidy up admin page URLs
* Tweak - Add filter 'automatewoo/referrals/show_share_widget'


1.1.6 *July 22, 2016*
---
* Fix - Issue where the referral reward type option could not be set to 'none'


1.1.5 *July 21, 2016*
---
* Fix - Change referral coupon type to 'Cart Discount' instead of 'Product Discount'


1.1.4 *July 21, 2016*
---
* Feature - Add ability for referral coupons to expire
* Tweak - Improve coupon validation notices


1.1.3 *July 19, 2016*
---
* Feature - WooCommerce subscriptions support - Allow store credit to be applied to recurring payments


1.1.2 *July 14, 2016*
---
* Tweak - Use an alternate method in advocate key generation


1.1.1 *July 13, 2016*
---
* Feature - Add option to display post purchase widget at bottom of page


1.1.0 *July 12, 2016*
---
* Feature - Add Post Purchase Share Widgets


1.0.0 *July 1, 2016*
---
* Initial release
