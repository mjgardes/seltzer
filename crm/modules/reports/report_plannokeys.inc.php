<?php

/*
    Copyright 2009-2014 Edward L. Platt <ed@elplatt.com>
    
    This file is part of the Seltzer CRM Project
    report_planinfo.inc.php - Membership plan reports
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
$report_plannokeys_theme = 'table';
$report_plannokeys_theme_opts = 'plannokeys';
$report_plannokeys_name = "Plan NoKeys";
$report_plannokeys_desc = "List of contacts with active plans but no keys";
/**
 * @return A comma-separated list of user emails.
 * @param $opts - Options to pass to member_data().
 */
function get_cids_without_keys ($list = array()) {

    $cidlist = '(' . join ("),(",$list) . ')';
    // Query contacts who have no plans associated
    $sql = "
    CREATE TEMPORARY TABLE cidlist (
        `cid` varchar(255) NOT NULL 
    );";
    global $db_connect;
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));

    $sql="INSERT INTO cidlist (cid) VALUES $cidlist;";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
    
    $sql="
    SELECT cid
    FROM cidlist
    WHERE cid NOT IN (SELECT cid FROM `key`);
    ";
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
function plannokeys_table ($opts = NULL) {
    // Determine settings
    $export = false;
    foreach ($opts as $option => $value) {
        switch ($option) {
            case 'export':
                $export = $value;
                break;
        }
    }
    
    $active_members = member_data(array('filter'=>array('active'=>true, 'scholarship'=>true))); // Get inactive members

    // build cid index
    $cidlist = array();
    foreach ($active_members as $member) {
        $cidlist[] = $member['cid'];
    }
    $plannokeys = get_cids_without_keys($cidlist);
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
    foreach ($plannokeys as $cid) {
        // Add secrets data
        $row = array();
        
        // Get info on member
        $data = member_data(array('cid'=>$cid));
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
        $row[] = isset($cid['serial']) ? $cid['serial'] : '';
        $row[] = isset($cid['start']) ? $cid['start'] : '';
        $row[] = isset($cid['end']) ? $cid['end'] : '';
        
        $table['rows'][] = $row;  

    }   
    // Return table
    return $table;
}


// Themeing ////////////////////////////////////////////////////////////////////

// Pages ///////////////////////////////////////////////////////////////////////
// No pages
