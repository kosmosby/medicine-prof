

CREATE TABLE IF NOT EXISTS `#__docmanpaypal` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) NOT NULL,
  `price` float NOT NULL DEFAULT '0',
  `downloadslimit` mediumint(9) NOT NULL DEFAULT '0',
  `saleslimit` mediumint(9) NOT NULL DEFAULT '0',
  `offlineGood` mediumint(9) NOT NULL DEFAULT '0',
  `vendor` mediumint(9) NOT NULL DEFAULT '0',
  `buttons` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__docmanpaypalvendors` (
  `vendor_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `paypalemail` varchar(255) NOT NULL,
  `mypercent` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__docmanpaypalconfig` (
  `name` varchar(32) NOT NULL,
  `user_id` mediumint(9) NOT NULL,
  `value` text NOT NULL,
  KEY `name` (`name`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__docmanpaypalconfig` (`name`, `user_id`, `value`) VALUES
('thankyoupagecode', 62, '<h1>Thank you for your order!</h1>\r\n<p>You can download %%downloadname%% from here: <a href="%%downloadlink%%"><strong>DOWNLOAD</strong></a></p>'),
('useVat', 62, '0'),
('vatPercent', 62, '20'),
('ordercanceledpage', 62, '<h1>Oops, has something gone wrong?</h1>\r\n<p>If you had a problem completing your purchase, please let us know, and we will help you as soon as possible.</p>\r\n<p>To go back to our site, please <a href="index.php">click here</a>.</p>'),
('free_download_after_seconds', 62, ''),
('paypal_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to PayPal...</h1>\r\n<h1 style="text-align: center;"><a href="https://www.paypal.com/bg/cgi-bin/webscr?cmd=_home"><img src="https://www.paypal.com/en_US/i/logo/paypal_logo.gif" border="?" alt="PayPal" /></a></h1>'),
('email_link', 62, ''),
('thankyoupagecode', 62, '<h1>Thank you for your order!</h1>\r\n<p>You can download %%downloadname%% from here: <a href="%%downloadlink%%"><strong>DOWNLOAD</strong></a></p>'),
('moneybookers_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Moneybookers...</h1>\r\n<p style="text-align: center;"><a href="http://www.moneybookers.com/"><img src="http://www.moneybookers.com/images/mb-logo.gif" border="?" alt="http://www.moneybookers.com/images/mb-logo.gif" /></a></p>'),
('allow_resellers', 62, ''),
('merchant_header', 62, '<p><strong>Please select a merchant:</strong></p>'),
('merchants', 62, 'PayPal'),
('cancelurl', 62, ''),
('reseller_usertypes', 62, ''),
('license', 62, ''),
('live_site', 62, 'http://docmanpaypal.motov.net/'),
('free_for_usertypes', 62, 'Super Administrator'),
('saleslimitpage', 62, '<p>This is the sales limit page!</p>'),
('downloadslimitpage', 62, '<p>This is the download limit hit page!</p>'),
('moneybookers_notifyemail', 62, 'Your@Email'),
('moneybookers_email', 62, 'Your@Moneybookers'),
('moneybookers_currency', 62, 'EUR'),
('paypalemail', 62, 'pro_1295423950_biz@motov.net'),
('sandbox', 62, 'Yes'),
('currency', 62, 'EUR'),
('notifyemail', 62, 'deian@motov.net'),
('moreThanOnce', 62, '0'),
('googleCheckout_email', 62, ''),
('googleCheckout_notifyemail', 62, ''),
('googleCheckout_currency', 62, ''),
('googleCheckout_processing_page', 62, ''),
('googleCheckout_MerchantID', 62, ''),
('googleCheckout_description', 62, ''),
('emailDelivery', 62, '1'),
('emailDeliveryDownloadLink', 62, '1'),
('emailDeliverySubject', 62, 'Email Delivery - Order complete!'),
('emailDeliveryBody', 62, '<p><strong>Hello, please find the file you ordered in the attachment.</strong></p>\r\n<p>Thank you!</p>\r\n<p>%downloadlink%</p>'),
('emailDeliveryMaxSizeInMB', 62, '20'),
('requireRegistration', 62, '0'),
('emailDeliveryToAdmin', 62, '1'),
('priceFormat', 62, '<div id="DMP_PRICE_DIV"><span class="DMP_PRICE">%priceText%</span> <span class="DMP_PRICE_VALUE">%price%</span> <span class="DMP_PRICE_CURRENCY">%currency%</span></div>'),
('use_shopping_cart', 62, ''),
('buttonStaysBuyNow', 62, '0'),
('free_for_docman_groups', 62, '0'),
('allow_coupons', 62, ''),
('useVat', 62, '0'),
('vatPercent', 62, '20'),
('buttonStaysBuyNow', 62, '0'),
('free_for_docman_groups', 62, '0'),
('ordercanceledpage', 62, '<h1>Oops, has something gone wrong?</h1>\r\n<p>If you had a problem completing your purchase, please let us know, and we will help you as soon as possible.</p>\r\n<p>To go back to our site, please <a href="index.php">click here</a>.</p>'),
('paypal_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to PayPal...</h1>\r\n<h1 style="text-align: center;"><a href="https://www.paypal.com/bg/cgi-bin/webscr?cmd=_home"><img src="https://www.paypal.com/en_US/i/logo/paypal_logo.gif" border="?" alt="PayPal" /></a></h1>'),
('moneybookers_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Moneybookers...</h1>\r\n<p style="text-align: center;"><a href="http://www.moneybookers.com/"><img src="http://www.moneybookers.com/images/mb-logo.gif" border="?" alt="http://www.moneybookers.com/images/mb-logo.gif" /></a></p>'),
('allow_resellers', 62, 'Yes'),
('merchant_header', 62, '<p><strong>Please select a merchant:</strong></p>'),
('merchants', 62, 'PayPal'),
('cancelurl', 62, 'http://docmanpaypal.motov.net/yourcancelpage.html'),
('license', 62, ''),
('live_site', 62, 'http://docmanpaypal.motov.net/'),
('free_for_usertypes', 62, 'Super Administrator'),
('saleslimitpage', 62, '<p>This is the sales limit page!</p>'),
('downloadslimitpage', 62, '<p>This is the download limit hit page!</p>'),
('moneybookers_notifyemail', 62, 'Your@Email'),
('moneybookers_email', 62, 'Your@Moneybookers'),
('moneybookers_currency', 62, 'EUR'),
('paypalemail', 62, 'pro_1295423950_biz@motov.net'),
('sandbox', 62, 'Yes'),
('currency', 62, 'EUR'),
('notifyemail', 62, 'deian@motov.net'),
('moreThanOnce', 62, '0'),
('googleCheckout_email', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_notifyemail', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_currency', 62, 'GBP'),
('googleCheckout_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Google Checkout...</h1>\r\n<p style="text-align: center;"><a href="https://checkout.google.com/"><img src="https://www.google.com/images/logos/checkout_logo.gif" border="?" alt="Google Checkout" /></a></p>'),
('googleCheckout_MerchantID', 62, 'YourMerchantID'),
('googleCheckout_description', 62, 'Digital Download Delivery'),
('emailDelivery', 62, '1'),
('emailDeliveryDownloadLink', 62, '1'),
('emailDeliverySubject', 62, 'Email Delivery - Order complete!'),
('emailDeliveryBody', 62, '<p><strong>Hello, please find the file you ordered in the attachment.</strong></p>\r\n<p>Thank you!</p>\r\n<p>%downloadlink%</p>'),
('emailDeliveryMaxSizeInMB', 62, '20'),
('requireRegistration', 62, '0'),
('emailDeliveryToAdmin', 62, '1'),
('priceFormat', 62, '<div id="DMP_PRICE_DIV"><span class="DMP_PRICE">%priceText%</span> <span class="DMP_PRICE_VALUE">%price%</span> <span class="DMP_PRICE_CURRENCY">%currency%</span></div>'),
('thankyoupagecode', 62, '<h1>Thank you for your order!</h1>\r\n<p>You can download %%downloadname%% from here: <a href="%%downloadlink%%"><strong>DOWNLOAD</strong></a></p>'),
('useVat', 62, '0'),
('vatPercent', 62, '20'),
('buttonStaysBuyNow', 62, '0'),
('free_for_docman_groups', 62, '0'),
('ordercanceledpage', 62, '<h1>Oops, has something gone wrong?</h1>\r\n<p>If you had a problem completing your purchase, please let us know, and we will help you as soon as possible.</p>\r\n<p>To go back to our site, please <a href="index.php">click here</a>.</p>'),
('paypal_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to PayPal...</h1>\r\n<h1 style="text-align: center;"><a href="https://www.paypal.com/bg/cgi-bin/webscr?cmd=_home"><img src="https://www.paypal.com/en_US/i/logo/paypal_logo.gif" border="?" alt="PayPal" /></a></h1>'),
('moneybookers_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Moneybookers...</h1>\r\n<p style="text-align: center;"><a href="http://www.moneybookers.com/"><img src="http://www.moneybookers.com/images/mb-logo.gif" border="?" alt="http://www.moneybookers.com/images/mb-logo.gif" /></a></p>'),
('allow_resellers', 62, 'Yes'),
('merchant_header', 62, '<p><strong>Please select a merchant:</strong></p>'),
('merchants', 62, 'PayPal'),
('cancelurl', 62, 'http://docmanpaypal.motov.net/yourcancelpage.html'),
('license', 62, ''),
('live_site', 62, 'http://docmanpaypal.motov.net/'),
('free_for_usertypes', 62, 'Super Administrator'),
('saleslimitpage', 62, '<p>This is the sales limit page!</p>'),
('downloadslimitpage', 62, '<p>This is the download limit hit page!</p>'),
('moneybookers_notifyemail', 62, 'Your@Email'),
('moneybookers_email', 62, 'Your@Moneybookers'),
('moneybookers_currency', 62, 'EUR'),
('paypalemail', 62, 'pro_1295423950_biz@motov.net'),
('sandbox', 62, 'Yes'),
('currency', 62, 'EUR'),
('notifyemail', 62, 'deian@motov.net'),
('moreThanOnce', 62, '0'),
('googleCheckout_email', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_notifyemail', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_currency', 62, 'GBP'),
('googleCheckout_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Google Checkout...</h1>\r\n<p style="text-align: center;"><a href="https://checkout.google.com/"><img src="https://www.google.com/images/logos/checkout_logo.gif" border="?" alt="Google Checkout" /></a></p>'),
('googleCheckout_MerchantID', 62, 'YourMerchantID'),
('googleCheckout_description', 62, 'Digital Download Delivery'),
('emailDelivery', 62, '1'),
('emailDeliveryDownloadLink', 62, '1'),
('emailDeliverySubject', 62, 'Email Delivery - Order complete!'),
('emailDeliveryBody', 62, '<p><strong>Hello, please find the file you ordered in the attachment.</strong></p>\r\n<p>Thank you!</p>\r\n<p>%downloadlink%</p>'),
('emailDeliveryMaxSizeInMB', 62, '20'),
('requireRegistration', 62, '0'),
('emailDeliveryToAdmin', 62, '1'),
('priceFormat', 62, '<div id="DMP_PRICE_DIV"><span class="DMP_PRICE">%priceText%</span> <span class="DMP_PRICE_VALUE">%price%</span> <span class="DMP_PRICE_CURRENCY">%currency%</span></div>'),
('paypalCountry', 62, '0'),
('icon_size', 62, '32x32'),
('netcash_notifyemail', 62, 'deian@motov.net'),
('dmPath', 62, '/home/motov/public_html/docmanpaypal.motov.net/jppd'),
('thankyoupagecode', 62, '<h1>Thank you for your order!</h1>\r\n<p>You can download %%downloadname%% from here: <a href="%%downloadlink%%"><strong>DOWNLOAD</strong></a></p>'),
('useVat', 62, '0'),
('paypalCountry', 62, '0'),
('vatPercent', 62, '20'),
('buttonStaysBuyNow', 62, '0'),
('free_for_docman_groups', 62, '0'),
('ordercanceledpage', 62, '<h1>Oops, has something gone wrong?</h1>\r\n<p>If you had a problem completing your purchase, please let us know, and we will help you as soon as possible.</p>\r\n<p>To go back to our site, please <a href="index.php">click here</a>.</p>'),
('paypal_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to PayPal...</h1>\r\n<h1 style="text-align: center;"><a href="https://www.paypal.com/bg/cgi-bin/webscr?cmd=_home"><img src="https://www.paypal.com/en_US/i/logo/paypal_logo.gif" border="?" alt="PayPal" /></a></h1>'),
('moneybookers_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Moneybookers...</h1>\r\n<p style="text-align: center;"><a href="http://www.moneybookers.com/"><img src="http://www.moneybookers.com/images/mb-logo.gif" border="?" alt="http://www.moneybookers.com/images/mb-logo.gif" /></a></p>'),
('allow_resellers', 62, 'Yes'),
('merchant_header', 62, '<p><strong>Please select a merchant:</strong></p>'),
('merchants', 62, 'PayPal'),
('cancelurl', 62, 'http://docmanpaypal.motov.net/yourcancelpage.html'),
('license', 62, ''),
('live_site', 62, 'http://docmanpaypal.motov.net/'),
('free_for_usertypes', 62, 'Super Administrator'),
('saleslimitpage', 62, '<p>This is the sales limit page!</p>'),
('downloadslimitpage', 62, '<p>This is the download limit hit page!</p>'),
('moneybookers_notifyemail', 62, 'Your@Email'),
('moneybookers_email', 62, 'Your@Moneybookers'),
('moneybookers_currency', 62, 'EUR'),
('paypalemail', 62, 'pro_1295423950_biz@motov.net'),
('sandbox', 62, 'Yes'),
('currency', 62, 'EUR'),
('notifyemail', 62, 'deian@motov.net'),
('moreThanOnce', 62, '0'),
('googleCheckout_email', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_notifyemail', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_currency', 62, 'GBP'),
('googleCheckout_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Google Checkout...</h1>\r\n<p style="text-align: center;"><a href="https://checkout.google.com/"><img src="https://www.google.com/images/logos/checkout_logo.gif" border="?" alt="Google Checkout" /></a></p>'),
('googleCheckout_MerchantID', 62, 'YourMerchantID'),
('googleCheckout_description', 62, 'Digital Download Delivery'),
('emailDelivery', 62, '1'),
('emailDeliveryDownloadLink', 62, '1'),
('emailDeliverySubject', 62, 'Email Delivery - Order complete!'),
('emailDeliveryBody', 62, '<p><strong>Hello, please find the file you ordered in the attachment.</strong></p>\r\n<p>Thank you!</p>\r\n<p>%downloadlink%</p>'),
('emailDeliveryMaxSizeInMB', 62, '20'),
('requireRegistration', 62, '0'),
('emailDeliveryToAdmin', 62, '1'),
('priceFormat', 62, '<div id="DMP_PRICE_DIV"><span class="DMP_PRICE">%priceText%</span> <span class="DMP_PRICE_VALUE">%price%</span> <span class="DMP_PRICE_CURRENCY">%currency%</span></div>'),
('encryptPDF', 62, '0'),
('netcash_password', 62, 'jYh80Df4343'),
('pdfOrientation', 62, 'portrait'),
('thankyoupagecode', 62, '<h1>Thank you for your order!</h1>\r\n<p>You can download %%downloadname%% from here: <a href="%%downloadlink%%"><strong>DOWNLOAD</strong></a></p>'),
('useVat', 62, '0'),
('paypalCountry', 62, '0'),
('vatPercent', 62, '20'),
('buttonStaysBuyNow', 62, '0'),
('pdfOrientation', 62, 'portrait'),
('free_for_docman_groups', 62, '0'),
('ordercanceledpage', 62, '<h1>Oops, has something gone wrong?</h1>\r\n<p>If you had a problem completing your purchase, please let us know, and we will help you as soon as possible.</p>\r\n<p>To go back to our site, please <a href="index.php">click here</a>.</p>'),
('paypal_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to PayPal...</h1>\r\n<h1 style="text-align: center;"><a href="https://www.paypal.com/bg/cgi-bin/webscr?cmd=_home"><img src="https://www.paypal.com/en_US/i/logo/paypal_logo.gif" border="?" alt="PayPal" /></a></h1>'),
('moneybookers_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Moneybookers...</h1>\r\n<p style="text-align: center;"><a href="http://www.moneybookers.com/"><img src="http://www.moneybookers.com/images/mb-logo.gif" border="?" alt="http://www.moneybookers.com/images/mb-logo.gif" /></a></p>'),
('allow_resellers', 62, 'Yes'),
('encryptPDF', 62, '0'),
('merchant_header', 62, '<p><strong>Please select a merchant:</strong></p>'),
('merchants', 62, 'PayPal'),
('cancelurl', 62, 'http://docmanpaypal.motov.net/yourcancelpage.html'),
('license', 62, ''),
('live_site', 62, 'http://docmanpaypal.motov.net/'),
('free_for_usertypes', 62, 'Super Administrator'),
('saleslimitpage', 62, '<p>This is the sales limit page!</p>'),
('downloadslimitpage', 62, '<p>This is the download limit hit page!</p>'),
('moneybookers_notifyemail', 62, 'Your@Email'),
('moneybookers_email', 62, 'Your@Moneybookers'),
('moneybookers_currency', 62, 'EUR'),
('paypalemail', 62, 'pro_1295423950_biz@motov.net'),
('sandbox', 62, 'Yes'),
('currency', 62, 'EUR'),
('notifyemail', 62, 'deian@motov.net'),
('moreThanOnce', 62, '0'),
('googleCheckout_email', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_notifyemail', 62, 'YourGoogle@Checkout.COM'),
('googleCheckout_currency', 62, 'GBP'),
('googleCheckout_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Google Checkout...</h1>\r\n<p style="text-align: center;"><a href="https://checkout.google.com/"><img src="https://www.google.com/images/logos/checkout_logo.gif" border="?" alt="Google Checkout" /></a></p>'),
('googleCheckout_MerchantID', 62, 'YourMerchantID'),
('googleCheckout_description', 62, 'Digital Download Delivery'),
('emailDelivery', 62, '1'),
('emailDeliveryDownloadLink', 62, '1'),
('emailDeliverySubject', 62, 'Email Delivery - Order complete!'),
('emailDeliveryBody', 62, '<p><strong>Hello, please find the file you ordered in the attachment.</strong></p>\r\n<p>Thank you!</p>\r\n<p>%downloadlink%</p>'),
('emailDeliveryMaxSizeInMB', 62, '20'),
('requireRegistration', 62, '0'),
('emailDeliveryToAdmin', 62, '1'),
('priceFormat', 62, '<div id="DMP_PRICE_DIV"><span class="DMP_PRICE">%priceText%</span> <span class="DMP_PRICE_VALUE">%price%</span> <span class="DMP_PRICE_CURRENCY">%currency%</span></div>'),
('netcash_pin', 62, '729770'),
('netcash_terminal', 62, '5H89'),
('netcash_processing_page', 62, '<h1 style="text-align: center;">Your order is being processed, please wait while we transfer you to Netcash.Co.Za...</h1>\r\n<p style="text-align: center;"><a href="http://www.moneybookers.com/"><img src="http://netcash.co.za/site/images/Netcashlogo.jpg" border="?" alt="Netcash" width="317" height="175" style="border: ?px solid black;" /></a></p>'),
('netcash_username', 62, 'FAS012GATE'),
('authorizenet_login_id', 62, '7d3uyM2E3WL'),
('authorizenet_transaction_key', 62, '397EGDc2y4h2Nac6'),
('authorizenet_md5_setting', 62, '7d3uyM2E3WL'),
('authorizenet_processing_page', 62, '<p> </p>\r\n<p style="text-align: center;"><img src="http://www.authorize.net/resources/images/authorizenet_logo.gif" border="?" width="225" height="55" style="vertical-align: middle;" /></p>\r\n<p style="text-align: left;">Your credit card will be processed by "Authorize.Net".</p>'),
('authorizenet_notifyemail', 62, 'deian@motov.net'),
('authorizenet_test_mode', 62, '1'),
('useCart', 62, '1'),
('thankyoupagecodeCart', 62, '<h1>Thank you for your order!</h1>\r\n<p>%%cartDownloadList%%</p>');

CREATE TABLE IF NOT EXISTS `#__docmanpaypaldownloads` (
  `order_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  UNIQUE KEY `order_id_2` (`order_id`,`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__docmanpaypalorders` (
  `order_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) NOT NULL,
  `buyer_id` mediumint(9) NOT NULL,
  `file_id` VARCHAR( 255 ) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `organization` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `price` float NOT NULL,
  `datetime` datetime NOT NULL,
  `mc_currency` varchar(4) NOT NULL,
  `completed` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `transaction` varchar(32) NOT NULL,
  `merchant` varchar(16) NOT NULL,
  `src` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;




/* Cart update */
