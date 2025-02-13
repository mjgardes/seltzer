<?php

/*
    Copyright 2009-2014 Edward L. Platt <ed@elplatt.com>

    This file is part of the Seltzer CRM Project
    report_emailactive.inc.php - Membership graph report
    Part of the Reports module

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

// Installation functions //////////////////////////////////////////////////////
// No install as this is called by reports module

// Utility functions ///////////////////////////////////////////////////////////
/*
 * Set the page content based on report name. Used for autoinclude
 */
$report_emaillapsed_theme = 'report_emaillapsed';
$report_emaillapsed_theme_opts = array('filter'=>array('inactive'=>true));
$report_emaillapsed_name = "Lapsed Emails";
$report_emaillapsed_desc = "List of inactive member email addresses";
/**
 * @return A comma-separated list of user emails.
 * @param $opts - Options to pass to member_data().
 */
if (! function_exists('member_email_report')) {
    function member_email_report ($opts) {
        $result = array();
        $data = member_data(array('filter'=>array('inactive'=>true))); // Get active members
        foreach ($data as $row) {
            $email = trim($row['contact']['email']);
            if (!empty($email)) {
                $result[] = $email;
            }
        }
    return join($result, ', ');
    }
}

// Forms ///////////////////////////////////////////////////////////////////////
// Put form generators here


// Themeing ////////////////////////////////////////////////////////////////////
/**
 * @return The themed html for an active member email report.
*/
function theme_report_emaillapsed ($opts) {
    $output = '<div class="member-email-report">';
    $title = '';
    if (isset($opts['filter']) && isset($opts['filter']['active'])) {
        $title = $opts['filter']['active'] ? 'Active ' : 'Lapsed ';
    }
    $title .= 'Email Report';
    $output .= "<h2>$title</h2>";
    $output .= '<textarea rows="24" cols="120">';
    $output .= member_email_report($opts);
    $output .= '</textarea>';
    $output .= '</div>';
    return $output;
}

// Pages ///////////////////////////////////////////////////////////////////////
// No pages
