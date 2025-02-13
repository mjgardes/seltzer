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
$report_nonmemberstorage_theme = 'table';
$report_nonmemberstorage_theme_opts = 'nonmemberstorage';
$report_nonmemberstorage_name = "Non-Member Storage";
$report_nonmemberstorage_desc = "List of non-member contacts with storage plots";
/**
 * @return A comma-separated list of user emails.
 * @param $opts - Options to pass to member_data().
 */
function get_storage_cids_without_plan () {
    $storage_contacts = array();

    // get all cids with plot
    // Query contacts who have no plans associated
    $storage_data = storage_data();
    foreach ($storage_data as $storage_contact) {
        $storage_contacts[] = $storage_contact['cid'];
    }
// var_dump_pre($storage_contacts);

    // $cids = array();
    foreach (member_data(array('cid'=>$storage_contacts,'filter'=>array('inactive'=>true))) as $contact) {
        $inactive_cids[] = $contact['cid'];
    }
    // $cids = array_diff($storage_contacts, array_merge($active_cids, ["",null]));

// var_dump_pre($cids);
    return $inactive_cids;
}

// Tables ///////////////////////////////////////////////////////////////////////
function nonmemberstorage_table ($opts = NULL) {
    // Determine settings
    $export = false;
    foreach ($opts as $option => $value) {
        switch ($option) {
            case 'export':
                $export = $value;
                break;
        }
    }
    $noncurrent_cids = get_storage_cids_without_plan();

     // Initialize table
    // $table = array(
    //     'columns' => array(
    //         array('title' => 'Name')
    //         , array('title' => 'Plan')
    //         , array('title' => 'Start')
    //         , array('title' => 'End')
    //         , array('title' => 'Plot#')
    //         , array('title' => 'Description')
    //     )
    //     , 'rows' => array()
    // );

// Add columns
    $table['columns'] = array();
    $table['columns'][] = array('title'=>'Name');
    if ($export) {
        $table['columns'][] = array('title'=>'Phone');
        $table['columns'][] = array('title'=>'eMail');
    }
    $table['columns'][] = array('title'=>'Plan');
    $table['columns'][] = array('title'=>'Start');
    $table['columns'][] = array('title'=>'End');
    $table['columns'][] = array('title'=>'Plot#');
    $table['columns'][] = array('title'=>'Description');
    $table['rows'] = array();


    // Add rows
    foreach ($noncurrent_cids as $cid) {
        // Add secrets data
        $row = array();

        // Get info on member
        $data = member_data(array('cid'=>$cid));
        $storage_data = storage_data(array('cid'=>$cid));
        $storage_data = $storage_data[0];
        $member = $data[0];
        $contact = $member['contact'];
        $name = theme('contact_name', $contact['cid'], !$export);
        $phone = $contact['phone'];
        $email = $contact['email'];
        // $recentMembership = end($member['membership']);
        if (is_array($member['membership'])) {
            $recentMembership = end($member['membership']);
            $plan = $recentMembership['plan']['name']; // then this is an active plan
            $planstart = $recentMembership['start'];
            $planend = $recentMembership['end'];
        } else {
            $recentMembership = "";
            $plan = ""; // then this is an active plan
            $planstart = "";
            $planend = "";
        }

        $row[] = $name;
        if ($export) {
            $row[] = $phone;
            $row[] = $email;
        }
        $row[] = $plan;
        $row[] = $planstart;
        $row[] = $planend;
        $row[] = $storage_data['pid'];
        $row[] = $storage_data['desc'];

        $table['rows'][] = $row;

    }
    // Return table
    return $table;
}


// Themeing ////////////////////////////////////////////////////////////////////

// Pages ///////////////////////////////////////////////////////////////////////
// No pages
