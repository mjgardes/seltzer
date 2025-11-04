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
$report_memberchange_theme = 'memberchange';
$report_memberchange_theme_opts = '';
$report_memberchange_name = "Membership Changes";
$report_memberchange_desc = "Search for membership plan changes based on date";

/*
 * Query DB for exit dates
 */
function memberchange_query ($type, $from, $to) {
    $result = array();

    // Query contacts who have no plans that ended
    $sql = "
        SELECT membership.cid,membership." . $type . " as " . $type ."date, plan.name as " . $type ."name FROM membership
        INNER JOIN plan ON membership.pid=plan.pid
        WHERE " . $type . " BETWEEN '" . $from . "' AND '". $to ."';
    ";
    global $db_connect;
    $res = mysqli_query($db_connect, $sql);
    if (!$res) crm_error(mysqli_error($db_connect));

    $row = mysqli_fetch_assoc($res);
    while (!empty($row)) {
        // Contents of row are kid, cid, start, end, serial, slot
        $result[] = $row;
        $row = mysqli_fetch_assoc($res);
    }
   
    return $result;
}

// Request Handlers ////////////////////////////////////////////////////////////
// function command_memberchange_search() {

//     // if (!user_access('reports_view')) {
//     //     error_register('Permission denied: reports_view');
//     //     return crm_url('reports');
//     // }
//     //var_dump_pre(crm_url('reports', array('query'=>array('name'=>'memberchange','from'=>$_POST['from'],'to'=>$_POST['to']))));
//     $returnString='http://beaglebone.local/crm/index.php?q=reports&name=memberchange&from='.$_POST['from'].'&to='.$_POST['to'];
//     var_dump_pre($returnString);
//     return $returnString;
// //    return crm_url('reports', array('query'=>array('name'=>'memberchange','from'=>$_POST['from'],'to'=>$_POST['to'])));
// }

// Forms ////////////////////////////////////////////////////////////////////////
function memberchange_search_form () {

    $dates = $_GET;
    if (empty($dates['from'])) {$dates['from'] = date("Y-m-d", strtotime("-1 month"));}
    if (empty($dates['to'])) {$dates['to'] = date("Y-m-d");}

    // Set Dates
    // Create form structure
    $form = array(
        'type' => 'form'
        , 'method' => 'get'
        // http://beaglebone.local/crm/index.php?q=reports&name=memberchange&from=2015-03-02&to=2015-03-31
//        , 'action' => 'index.php?q=reports&name=memberchange&from=2015-03-02&to=2015-03-31'
        // , 'action' => crm_url('reports', array('query'=>array('name'=>'memberchange','from'=>$_GET['from'],'to'=>$_GET['to'])))
        , 'hidden' => array(
            'q' => 'reports'
            , 'name' => 'memberchange'
        )
        , 'fields' => array(
            array(
                'type' => 'fieldset'
                , 'label' => 'Search Dates'
                , 'fields' => array(
                    array(
                        'type' => 'text'
                        , 'label' => 'From'
                        , 'name' => 'from'
                        , 'value' => $dates['from']
                        , 'class' => 'date float'
                    )
                    , array(
                         'type' => 'text'
                        , 'label' => 'To'
                        , 'name' => 'to'
                        , 'value' => $dates['to']
                        , 'class' => 'date float'
                    )
                    , array(
                        'type' => 'submit'
                        , 'value' => 'Search'
                    )
                )
            )
        )
    );

    return $form;
}

// Tables ///////////////////////////////////////////////////////////////////////
function memberchange_results_table ($opts = NULL) {
    // Determine settings
    $export = false;
    foreach ($opts as $option => $value) {
        switch ($option) {
            case 'export':
                $export = $value;
                break;
        }
    }
    $dates = $_GET;
    if (empty($dates['from'])) {$dates['from'] = date("Y-m-d");}
    if (empty($dates['to'])) {$dates['to'] = date("Y-m-d");}

    $query_end_result = memberchange_query('end',$dates['from'],$dates['to']);
    $query_start_result = memberchange_query('start',$dates['from'],$dates['to']);
    $query_result = array();
    // var_dump_pre($query_end_result);
    // var_dump_pre($query_start_result);
  
    // parse start and end arrays and combine into a single results array
    foreach ($query_end_result as $plan_end ) {
        $query_result[$plan_end['cid']]['enddate'] = $plan_end['enddate'];
        $query_result[$plan_end['cid']]['endplan'] = $plan_end['endname'];
    };
    foreach ($query_start_result as $plan_start ) {
        $query_result[$plan_start['cid']]['startdate'] = $plan_start['startdate'];
        $query_result[$plan_start['cid']]['startplan'] = $plan_start['startname'];
    };

//    var_dump_pre($query_result);
    
    // Initialize table
    $table = array(
        'columns' => array(
            array('title' => 'Name')
            , array('title' => 'FromPlan')
            , array('title' => 'EndDate')
            , array('title' => 'ToPlan')
            , array('title' => 'StartDate')
        )
        , 'rows' => array()
    );

    // Add rows
    foreach ($query_result as $cid => $exitdata) {
        // Add secrets data
        $row = array();
        // Get Member Name
        $data = member_data(array('cid'=>$cid));
        $member = $data[0];
        $contact = $member['contact'];
        $name = theme('contact_name', $contact['cid'], !$export);
        $row[] = $name;
        
        // Plan Data
        $row[] = $exitdata['endplan'];
        $row[] = $exitdata['enddate'];
        $row[] = $exitdata['startplan'];
        $row[] = $exitdata['startdate'];
    

        $table['rows'][] = $row;  

    }   
    // Return table
    return $table;
}


// Themeing ////////////////////////////////////////////////////////////////////
/**
 * Return the themed html for an add secret assignment form.
 *
 * @param $name The name of the secret to add a value assignment for.
 * @return The themed html string.
 */
function theme_memberchange ($name) {
    $theme = theme('form', crm_get_form('memberchange_search', $name));
    $theme .= theme('table', 'memberchange_results', array('show_export'=>true));
    return $theme;
}

// Pages ///////////////////////////////////////////////////////////////////////

