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
$report_futureplan_theme = 'table';
$report_futureplan_theme_opts = 'futureplan';
$report_futureplan_name = "Contact Future Plan";
$report_futureplan_desc = "Contacts whose plans start in the future";
/**
 * @return A comma-separated list of user emails.
 * @param $opts - Options to pass to member_data().
 */
function get_cids_with_future_plan () {
    $result = array();

    // Query contacts who have no plans associated
    $sql = "
        SELECT c.cid, p.name, m.start, m.end
            FROM contact c, plan p, membership m
            WHERE m.start > NOW()
            AND m.cid = c.cid 
            AND m.pid = p.pid;
    ";
    global $db_connect;
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));
 
    $cids=array();
    while ($row = mysqli_fetch_row($res)) $cids[]=$row;
   
    return $cids;
}

// Tables ///////////////////////////////////////////////////////////////////////
function futureplan_table ($opts = NULL) {
    // Determine settings
    $export = false;
    foreach ($opts as $option => $value) {
        switch ($option) {
            case 'export':
                $export = $value;
                break;
        }
    }
    
    $futureplan_cids = get_cids_with_future_plan();

     // Initialize table
    $table = array(
        'columns' => array(
            array('title' => 'Name'),
            array('title' => 'Plan'),
            array('title' => 'Start'),
            array('title' => 'End')
        )
        , 'rows' => array()
    );

    // Add rows
    foreach ($futureplan_cids as list($cid,$plan,$date,$end)) {
        // Add secrets data
        $row = array();
        
        // Get info on member
        $data = member_data(array('cid'=>$cid));
        $member = $data[0];
        $contact = $member['contact'];
        $name = theme('contact_name', $contact['cid'], !$export);
       // $name_link = theme('contact_name', $member, true);
   
        $row[] = $name;
        $row[] = $plan;
        $row[] = $date;
        $row[] = $end;

        $table['rows'][] = $row;  

    }   
    // Return table
    return $table;
}


// Themeing ////////////////////////////////////////////////////////////////////

// Pages ///////////////////////////////////////////////////////////////////////
// No pages
