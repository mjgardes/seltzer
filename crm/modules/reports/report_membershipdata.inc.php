<?php

/*
    Copyright 2009-2014 Edward L. Platt <ed@elplatt.com>
    
    This file is part of the Seltzer CRM Project
    report_membershipdata.inc.php - Show members in the "membershipdata" plan
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
$report_membershipdata_theme = 'table';
$report_membershipdata_theme_opts = 'membershipdata';
$report_membershipdata_name = "Membership Status (raw data)";
$report_membershipdata_desc = "Tabular history of memberships";
/**
 * @return A comma-separated list of user emails.
 * @param $opts - Options to pass to member_data().
 */
function report_membershipdata_earliest_date () {
    $sql = "SELECT `start` FROM `membership` ORDER BY `start` ASC LIMIT 1";
    global $db_connect;
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        return '';
    }
    return $row['start'];
}

/**
 * @return json structure containing membershipdata statistics.
 */
function get_membershipdata () {
    // Get plans and earliest date
    $plans = crm_map(member_plan_data(), 'pid');
    $results = array();
    foreach ($plans as $pid => $plan) {
        $results[$pid] = array();
    }
    $earliest = report_membershipdata_earliest_date();
    if (empty($earliest)) {
        message_register('No membership data available.');
        return '[]';
    }
    // Generate list of months
    $start = 12*(int)date('Y', strtotime($earliest)) + (int)date('m', strtotime($earliest)) - 1;
    $now = 12*(int)date('Y') + (int)date('m') - 1;
    $dates = array();
    for ($months = $start; $months <= $now; $months++) {
        $year = floor($months/12);
        $month = $months % 12 + 1;
        $dates[] = "('$year-$month-01')";
    }
    // Create temporary table with dates
    global $db_connect;
    $sql = "DROP TEMPORARY TABLE IF EXISTS `temp_months`";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    $sql = "CREATE TEMPORARY TABLE `temp_months` (`month` date NOT NULL);";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    $sql = "INSERT INTO `temp_months` (`month`) VALUES " . implode(',', $dates) . ";";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    // Query number of active membershipdatas for each month
    $sql = "
        SELECT
            `plan`.`pid`
            , `plan`.`name`
            , `temp_months`.`month`
            , UNIX_TIMESTAMP(`temp_months`.`month`) * 1000 AS `month_timestamp`
            , count(`membership`.`sid`) AS `member_count`
        FROM `temp_months`
        JOIN `plan`
        LEFT JOIN `membership`
        ON `membership`.`pid`=`plan`.`pid`
        AND `membership`.`start` <= `month`
        AND (`membership`.`end` IS NULL OR `membership`.`end` > `month`)
        GROUP BY `plan`.`pid`, `month`;
    ";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    // Build results
    while ($row = mysqli_fetch_assoc($res)) {
        $results[$row['month']][$row['name']] = (int)$row['member_count'];
    }

    // Get a list of all the plans and their order from the most recent month result
    $keys = array_keys(array_pop((array_slice($results, -1))));
    $indexed = array();
    foreach ($results as $month => $value) {
        if (!is_int($month)) {
            $myrow = array($month);
            foreach ($value as $plan => $count) {
                $myrow[] = $count;
            }
            $indexed[] = $myrow;
        }
    }
    $fullindex[] = $keys;
    $fullindex[] = $indexed;
    return ($fullindex);
    
}

// Tables ///////////////////////////////////////////////////////////////////////
function membershipdata_table ($opts) {
    // Determine settings
    $export = true;
    foreach ($opts as $option => $value) {
        switch ($option) {
            case 'export':
                $export = $value;
                break;
        }
    }

    $membershipdata = get_membershipdata();
    $headers = array_shift($membershipdata);

    $columns = array();
    $columns[] = array('title' => 'Month');
    foreach ($headers as $plan) {
        $columns[] = array('title' => $plan);
    }
    // Initialize table
    $table = array(
        'columns' => $columns
        , 'rows' => array()
    );

    // Add rows
    foreach ($membershipdata as $data) {
        // Add data
        $row = array();
        // Get info on member
        foreach ($data as $plandata) {
            $table['rows'][] = $plandata;
        }
        // $table['rows'][] = $row;  
    }   
    // Return table
    return $table;
}


// Themeing ////////////////////////////////////////////////////////////////////

// Pages ///////////////////////////////////////////////////////////////////////
// No pages
