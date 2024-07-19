<?php

/*
    Copyright 2009-2013 Edward L. Platt <ed@elplatt.com>

    This file is part of the Seltzer CRM Project
    config.inc.php - Sample configuration

    Seltzer is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    Seltzer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Seltzer.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once($crm_root . '/config_db.inc.php');
require_once($crm_root . '/api/db.inc.php');
require_once($crm_root . '/modules/variable/variable.inc.php');

// Site info

// The title to display in the title bar
$config_site_title = 'i3 Detroit';

// The name of the organization to insert into templates
$config_org_name = 'i3 Detroit';

// The currency code for dealing with payments, can be GBP, USD, or EUR
$config_currency_code = 'USD';

// The From: address to use when sending email to members
$config_email_from = 'treasurer@i3detroit.org';

// The email address to notify when a user is created
$config_email_to = variable_get('contact_email', 'contact@i3detroit.com');

// The hostname of the server
$config_host = $_SERVER['SERVER_NAME'];

// The url path of the crm directory
$config_base_path = '/crm/';

// The name of the theme you want to use
// (currently there is only one, "inspire".)
$config_theme = "inspire";

// Amazon signatures version 2 keys
$config_amazon_payment_secret = '';
$config_amazon_payment_access_key_id = '';

// Base modules
$config_modules = array(
    "contact",
    "user",
    "variable",
    "member"
);

// Optional modules, uncomment to enable

// Track RFID key serial numbers
$config_modules[] = "key";

// Track payments, manual entry
$config_modules[] = "payment";

// FoxyCart integration
//$config_modules[] = "foxycart_payment";

$config_modules[] = "services";

$config_modules[] = "secrets";

// Reports module
$config_modules[] = "reports";

// Amazon payment integration
$config_modules[] = "amazon_payment";

// Paypal integration
//$config_modules[] = "paypal_payment";

// Automated billing
$config_modules[] = "billing";

// Assign members a mentor
//$config_modules[] = "mentor";

// Show profile pictures
// This feature is broken in 2.0
//$config_modules[] = "profile_picture";

// Silly nickname module
//$config_modules[] = "template";

// Developer tools
//$config_modules[] = "devel";

// Storage module
$config_modules[] = "storage";

// Debugging functions
$config_modules[] = "debug";

// Links to show in the main menu
$config_links = array(
    '<front>' => 'Home'
    , 'members' => 'Members'
    , 'plans' => 'Plans'
    , 'keys' => 'Keys'
    , 'payments' => 'Payments'
    , 'accounts' => 'Accounts'
    , 'storage' => 'Storage'
    , 'reports' => 'Reports'
    , 'permissions' => 'Permissions'
    , 'upgrade' => 'Upgrade'
    , 'secrets' => 'Secrets'
);
