<?php

/*
    Copyright 2009-2014 Edward L. Platt <ed@elplatt.com>
    
    This file is part of the Seltzer CRM Project
    reports.inc.php - Independent reports module

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
function reports_revision () {
    return 1;
}

/**
 * @return An array of the permissions provided by this module.
 */
function reports_permissions () {
    return array(
        'reports_view'
        , 'reports_edit'
        , 'reports_delete'
    );
}

/**
 * Install or upgrade this module.
 * @param $old_revision The last installed revision of this module, or 0 if the
 *   module has never been installed.
 */
function reports_install($old_revision = 0) {

    global $db_connect;

    if ($old_revision < 1) {
        // No unique databases needed
                // Set default permissions
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
            'director' => array('reports_view', 'reports_edit', 'reports_delete')
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

// Initialization //////////////////////////////////////////////////////////////
if (stripos($_SERVER['REQUEST_URI'], 'export-csv.php')) {
  // load modules directly when called by export function
  $load_files = glob('modules/reports/report_*.inc.php');
  foreach ($load_files as $filename) {
    require_once("$filename");
  }
}

// Utility functions ///////////////////////////////////////////////////////////

// Forms ///////////////////////////////////////////////////////////////////////

// Tables //////////////////////////////////////////////////////////////////////
// Put table generators here
function reports_table ($reportList) {
    // Determine settings
    $export = false;
    // foreach ($opts as $option => $value) {
    //     switch ($option) {
    //         case 'export':
    //             $export = $value;
    //             break;
    //     }
    // }

    // Initialize table
    $table = array(
        'columns' => array(
            array('title' => 'Name')
            , array('title' => 'Description')
            , array('title' => 'Action')
        )
        , 'rows' => array()
    );

    foreach ($reportList as $report) {
        // Add secrets data
        $row = array();
        if (user_access('reports_view')) {
            // Add cells
            $row[] = $report[1];
            $row[] = $report[2];
            $row[] = '<a href=' . crm_url('reports&name=' . $report[0]) . '>Run</a> ';
        }
        $table['rows'][] = $row;  
    }
    return $table;
}

// Themeing ////////////////////////////////////////////////////////////////////

// Pages ///////////////////////////////////////////////////////////////////////

/**
 * @return An array of pages provided by this module.
 */
function reports_page_list () {
    $pages = array();
    if (user_access('reports_view')) {
        $pages[] = 'reports';
    }
    return $pages;
}

/**
 * Page hook.  Adds module content to a page before it is rendered.
 *
 * @param &$page_data Reference to data about the page being rendered.
 * @param $page_name The name of the page being rendered.
 * @param $options The array of options passed to theme('page').
*/
function reports_page (&$page_data, $page_name, $options) {
    switch ($page_name) {

        case 'reports':
        // Set page title
        page_set_title($page_data, 'Reports');
        
        // Include all report_*.inc.php files as those are the individual reports
        $report_files = glob('modules/reports/report_*.inc.php');
        $reportList = array();
        foreach ($report_files as $filename) {
            require_once("$filename");
            preg_match('/report_(.*)\.inc.php/', $filename, $match);
            // reportList = [filename, short name, description]
            $reportList[] = array(
                $match[1]
                , ${'report_'.$match[1].'_name'}
                , ${'report_'.$match[1].'_desc'}
                );
        }
        // List all reports
        if (user_access('reports_view')) {
            page_add_content_top($page_data, theme('table', 'reports', $reportList));
        }
        // show report if selected
        $view_content = '';
        if (isset($options['name'])) {
            $view_content .= '<h3>' . ${'report_'.$options['name'].'_name'} . '</h3>';
            $view_content .= theme(${'report_'.$options['name'].'_theme'}, ${'report_'.$options['name'].'_theme_opts'}, array('show_export'=>true));
            page_add_content_bottom($page_data, $view_content);
        }
        break;
    }
}
