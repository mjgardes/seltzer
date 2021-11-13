<?php
/*
    Copyright 2014 Edward L. Platt <ed@elplatt.com>
    
    This file is part of the Seltzer CRM Project
    template.inc.php - Template for contributed modules

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

/**
 * @return This module's revision number.  Each new release should increment
 * this number.
 */
function services_revision () {
    return 1;
}

/**
 * @return An array of the permissions provided by this module.
 */
function services_permissions () {
    return array(
        'services_view'
        , 'services_edit'
        , 'services_delete'
    );
}

/**
 * Install or upgrade this module.
 * @param $old_revision The last installed revision of this module, or 0 if the
 *   module has never been installed.
 */
function services_install($old_revision = 0) {

    global $db_connect;

    if ($old_revision < 1) {
        // Create databases here
        $roles = array(
            '1' => 'authenticated'
            , '2' => 'member'
            , '3' => 'director'
            , '4' => 'president'
            , '5' => 'vp'
            , '6' => 'secretary'
            , '7' => 'treasurer'
            , '8' => 'webAdmin'
        );
        $default_perms = array(
            'director' => array('services_view', 'services_edit', 'services_delete')
        );
        foreach ($roles as $rid => $role) {
            $esc_rid = mysql_real_escape_string($rid);
            if (array_key_exists($role, $default_perms)) {
                foreach ($default_perms[$role] as $perm) {
                    $esc_perm = mysql_real_escape_string($perm);
                    $sql = "INSERT INTO `role_permission` (`rid`, `permission`) VALUES ('$esc_rid', '$esc_perm')";
                    $sql .= " ON DUPLICATE KEY UPDATE rid=rid";
                    $res = mysqli_query($db_connect, $sql);
                    if (!$res) crm_error(mysqli_error($db_connect));
                }
            }
        }
    }
}

// Utility functions ///////////////////////////////////////////////////////////


// DB to Object mapping ////////////////////////////////////////////////////////

/**
 * Implementation of hook_data_alter().
 * @param $type The type of the data being altered.
 * @param $data An array of structures of the given $type.
 * @param $opts An associative array of options.
 * @return An array of modified structures.
 */
// function services_data_alter ($type, $data = array(), $opts = array()) {
//     switch ($type) {
//         case 'contact':
//             foreach ($data as $i => $contact) {
//                 $data[$i]['nickname'] = services_nickname($data[$i]);
//             }
//             break;
//     }
//     return $data;
// }

// Tables //////////////////////////////////////////////////////////////////////
// Put table generators here
/**
 * Return a table structure for a table of service links.
 *
 * @param $opts The options to pass to service_data().
 * @return The table structure.
*/
function services_table ($opts) {

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
        "id" => '',
        "class" => '',
        "rows" => array(),
        "columns" => array()
    );

    // Add columns
    if (user_access('services_view') || $opts['cid'] == user_id()) {
        if ($export) {
            $table['columns'][] = array("title"=>'cid', 'class'=>'', 'id'=>'');
        }
        $table['columns'][] = array("title"=>'Service', 'class'=>'', 'id'=>'');
        $table['columns'][] = array("title"=>'Username', 'class'=>'', 'id'=>'');
        $table['columns'][] = array("title"=>'Status', 'class'=>'', 'id'=>'');
    }

    // Add ops column
    if (!$export && (user_access('services_edit') || user_access('services_delete') || $opts['cid'] == user_id())) {
        $table['columns'][] = array('title'=>'Ops','class'=>'');
    }

    // Get list of available services
    $service_files = glob('modules/services/service_*.inc.php');
    $serviceList = array();
    foreach ($service_files as $filename) {
        require_once("$filename");
        preg_match('/service_(.*)\.inc.php/', $filename, $match);
        // serviceList = [filename, short name, description]
        $serviceList[] = $match[1];
    }

    // Add rows
    $row = array();
    foreach ($serviceList as $service) {
        $rowData = call_user_func('service_' . $service . '_addrow', $opts); // this will pull the _addrow from each service subpage
        $row = [$rowData['serviceName'],$rowData['userName'],$rowData['userStatus'],$rowData['ops']];
    }
    // finalize table
    $table['rows'][] = $row;
    return $table;
}

// Forms ///////////////////////////////////////////////////////////////////////
// Put form generators here

// Themeing ////////////////////////////////////////////////////////////////////

/**
 * Return themed html for a nickname.
 */
// function theme_services_nickname ($cid) {
//     $contact = crm_get_one('contact', array('cid'=>$cid));
//     return '<h3>Nickname</h3><p>' . services_nickname($contact) . '</p>';
// }

// Pages ///////////////////////////////////////////////////////////////////////

/**
 * @return An array of pages provided by this module.
 */
function services_page_list () {
    $pages = array();
//    if (user_access('key_view')) { // add access controls?:w
        $pages[] = 'services';
    return $pages;
}

/**
 * Page hook.  Adds module content to a page before it is rendered.
 *
 * @param &$page_data Reference to data about the page being rendered.
 * @param $page_name The name of the page being rendered.
 * @param $options The array of options passed to theme('page').
*/
function services_page (&$page_data, $page_name, $options) {
    switch ($page_name) {
        case 'contact':
            // Capture contact cid
            $cid = $options['cid'];
            if (empty($cid)) {
                return;
            }
            
            // Add Services Table
            if (user_access('services_view') || user_access('services_edit') || user_access('services_delete') || $cid == user_id()) {
                $services = theme('table', 'services', array('cid' => $cid));
                $services .= theme('services_form', $cid);
                page_add_content_bottom($page_data, $services, 'Services');
            }
    }
}

// Request Handlers ////////////////////////////////////////////////////////////

?>
