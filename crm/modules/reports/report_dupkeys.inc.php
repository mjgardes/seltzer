<?php

/*
    Copyright 2009-2014 Edward L. Platt <ed@elplatt.com>
    
    This file is part of the Seltzer CRM Project
    report_dupkeys.inc.php - Duplicate key assignments
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
$report_dupkeys_theme = 'table';
$report_dupkeys_theme_opts = 'dupkeys';
$report_dupkeys_name = "Duplicate Keys";
$report_dupkeys_desc = "List of keys that are assigned to multiple users";
/**
 * @return A comma-separated list of user emails.
 * @param $opts - Options to pass to member_data().
 */
function get_dupkeys () {

    // Query contacts who have no plans associated
    $sql = "SELECT *
    FROM `key`
    WHERE serial IN (
        SELECT serial
        FROM `key`
    	WHERE end IS NULL
        GROUP BY serial
        HAVING COUNT(serial) > 1
    )
    ORDER BY serial;";
    global $db_connect;
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    
    $keys=array();
    $row = mysqli_fetch_assoc($res);
    while (!empty($row)) {
        // Contents of row are name, value
        $keys[] = $row;
        $row = mysqli_fetch_assoc($res);
    }
    return $keys;
}

// Tables ///////////////////////////////////////////////////////////////////////
function dupkeys_table ($opts = NULL) {
    // Determine settings
    $export = false;
    foreach ($opts as $option => $value) {
        switch ($option) {
            case 'export':
                $export = $value;
                break;
        }
    }
    
     // Initialize table
    $table = array(
        'columns' => array(
            array('title' => 'Name')
            , array ('title' => 'Plan Name')
            , array ('title' => 'Plan Start')
            , array ('title' => 'Plan End')
            , array ('title' => 'Key')
            , array ('title' => 'Key Start')
            , array ('title' => 'Key End')
            
        )
        , 'rows' => array()
    );

    // Add rows
    $dupkeys = get_dupkeys();
    foreach ($dupkeys as $key) {
        // Add secrets data
        $row = array();
        
        // Get info on member
        $data = member_data(array('cid'=>$key['cid']));
        $member = $data[0];
        // name
        $contact = $member['contact'];
        $name = theme('contact_name', $contact['cid'], !$export);
        $row[] = $name;
        // plan
        $membership = $member['membership'];
        $recentPlan = array_pop((array_slice($membership, -1)));
        $row[] = $recentPlan['plan']['name'];
        $row[] = $recentPlan['start'];
        $row[] = $recentPlan['end'];
        //key
        $row[] = $key['serial'];
        $row[] = $key['start'];
        $row[] = $key['end'];
        
        $table['rows'][] = $row;  

    }   
    // Return table
    return $table;
}


// Themeing ////////////////////////////////////////////////////////////////////

// Pages ///////////////////////////////////////////////////////////////////////
// No pages
