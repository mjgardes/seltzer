<?php
/*
    Copyright 2014 Edward L. Platt <ed@elplatt.com>

    This file is part of the Seltzer CRM Project
    storage.inc.php - Manage member storage assigments

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
function storage_revision () {
    return 1;
}

/**
 * @return An array of the permissions provided by this module.
 */
function storage_permissions () {
    return array(
        'storage_view'
        , 'storage_edit'
        , 'storage_delete'
    );
}

/**
 * Install or upgrade this module.
 * @param $old_revision The last installed revision of this module, or 0 if the
 *   module has never been installed.
 */
function storage_install($old_revision = 0) {

    global $db_connect;

    if ($old_revision < 1) {
// create master list table
        $sql = 'CREATE TABLE IF NOT EXISTS `storage_plot` (
            `pid` mediumint(8) unsigned NOT NULL,
            `desc` varchar(255) NOT NULL,
            `cid` varchar(255) NOT NULL,
            `email` varchar(255),
            `reapmonth` mediumint(8) unsigned NOT NULL default 1,
            `reapdate` date NOT NULL,
            UNIQUE (`pid`),
            PRIMARY KEY (`pid`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ';
        $res = mysqli_query($db_connect, $sql);
        if (!$res) die(mysqli_error($res ));
// create storage log
        $sql = 'CREATE TABLE IF NOT EXISTS `storage_log` (
            `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `user` varchar(255) NOT NULL,
            `action` varchar(255) NOT NULL,
            `pid` mediumint(8) unsigned NOT NULL,
            `desc` varchar(255) NOT NULL,
            `cid` varchar(255) NOT NULL,
            `email` varchar(255),
            `reapmonth` mediumint(8) unsigned NOT NULL default 1,
            `reapdate` date NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ';
        $res = mysqli_query($db_connect, $sql);
        if (!$res) die(mysqli_error($db_connect));
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
            , '9' => 'keymaster'
        );
        $default_perms = array(
            'director' => array('storage_view', 'storage_edit', 'storage_delete')
        );
        foreach ($roles as $rid => $role) {
            $esc_rid = mysqli_real_escape_string($db_connect, $rid);
            if (array_key_exists($role, $default_perms)) {
                foreach ($default_perms[$role] as $perm) {
                    $esc_perm = mysqli_real_escape_string($db_connect, $perm);
                    $sql = "INSERT INTO `role_permission` (`rid`, `permission`) VALUES ('$esc_rid', '$esc_perm')";
                    $sql .= " ON DUPLICATE KEY UPDATE rid=rid";
                    $res = mysqli_query($db_connect, $sql);
                    if (!$res) die(mysqli_error($db_connect));
                }
            }
        }
    }
}

// Utility functions ///////////////////////////////////////////////////////////

function text_replace ($opts) {
    // message_register('text_replace($opts)='.var_export($opts,true));

    $repFrom = array();
    $repTo = array();
    $monthNames = array(
        1 => "January" , 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
        7 => "July", 8 => "August", 9 => "Sepember", 10 => "October", 11 => "November", 12 => "December"
    );
    $monthName = $monthNames[$_SESSION['reap_month_filter_option']];

    // {{plotlist}} - list plot numbers in comma-separated format
    if (strpos($opts['text'], '{{plotlist}}') !== false) {
        $pidsToReap = preg_replace(array('/,/','/,([^,]*)$/'), array(', ',' and\1'), $opts['pidsToReap']);
        $repFrom[] = '/{{plotlist}}/';  $repTo[] = $pidsToReap;
    }

    // {{plotowners}} List plots as "pid - owner" format on separate lines
    if (strpos($opts['text'], '{{plotowners}}') !== false) {
        $pidsToReap = explode(",", $opts['pidsToReap']);
        $contact_data = crm_get_data('contact', '');
        $cid_to_contact = crm_map($contact_data, 'cid');
        $plotOwnersList = '';
        foreach ($pidsToReap as $pid) {
            $plot = crm_get_one('storage',array('pid'=>$pid));
            if (!empty($plot['cid']) && array_key_exists($plot['cid'],$cid_to_contact)) {
                $contact = $cid_to_contact[$plot['cid']];
            } else {
                $contact = '';
            }
            if (!empty($contact)) {
                $owner = theme('contact_name', $contact, false);
            } else {
                $owner = $plot['cid'];
            }
            $plotOwnersList .= "$pid - ".preg_replace('/\'/','',(var_export($owner,true)))."\n"; // remove ' from results
        }
        $repFrom[] = '/{{plotowners}}/';  $repTo[] = $plotOwnersList;
    }

    // {{name}} replace with full name of plot owner
    if (strpos($opts['text'], '{{name}}') !== false) {
        if (array_key_exists('cid', $opts)) {
            $contact = !empty(crm_get_data('contact', array('cid'=>$opts['cid']))) ? theme_contact_name($opts['cid']) : $opts['cid'];
            $repFrom[] = '/{{name}}/';  $repTo[] = $contact;
        }
    }

    // {{month}} - name of reaping month
    if (strpos($opts['text'], '{{month}}') !== false) {

        $repFrom[] = '/{{month}}/';  $repTo[] = $monthName;
    }

    // {{date to be out by}} - third Tuesday of month
    if (strpos($opts['text'], '{{outby}}') !== false) {
        $repFrom[] = '/{{outby}}/';  $repTo[] = date('l jS \of F Y', strtotime('third tuesday of '.$monthName));
    }

    // {{first day to return}} - fourth Tuesday if month
        if (strpos($opts['text'], '{{returnon}}') !== false) {
        $repFrom[] = '/{{returnon}}/';  $repTo[] = date('l jS \of F Y', strtotime('fourth tuesday of '.$monthName));
    }

    // {{last day to return}} - last day of the month
        if (strpos($opts['text'], '{{returnby}}') !== false) {
        $repFrom[] = '/{{returnby}}/';  $repTo[] = date('l jS \of F Y', strtotime('last day of '.$monthName));
    }

    // message_register('preg_replace('.var_export($repFrom,true).', '.var_export($repTo,true).', '.$opts['text'].')');
    return preg_replace($repFrom, $repTo, $opts['text']);
}
// DB to Object mapping ////////////////////////////////////////////////////////

/**
 * Return data for one or more storage plots.
 *
 * @param $opts An associative array of options, possible keys are:
 *   'pid' If specified, returns a single plot with the matching plot id;
 *   'cid' If specified, returns all plots assigned to the contact with specified id;
 *   'reapafter' If specified, returns all plots reaped on or after this date;
 *   'reapbefore' If specified, returns all plots reaped on or before this date;
 * @return An array with each element representing a single plot assignment.
*/
function storage_data ($opts = array()) {
// Query database
    global $db_connect;
    $sql = "
        SELECT *
        FROM storage_plot
        WHERE 1 ";
    if (array_key_exists('pid', $opts) && !empty($opts['pid'])) {
        $esc_name = mysqli_real_escape_string($db_connect, $opts['pid']);
        $sql .= " AND pid='" . $esc_name . "'";
    }
    if (array_key_exists('cid', $opts) && !empty($opts['cid'])) {
        $esc_name = mysqli_real_escape_string($db_connect, $opts['cid']);
        $sql .= " AND cid='" . $esc_name . "'";
    }
    if (array_key_exists('reapbefore', $opts) && !empty($opts['reapbefore'])) {
        $esc_reapbefore = mysqli_real_escape_string($db_connect, $opts['reapbefore']);
        $sql .= " AND reapdate < '" . $esc_reapbefore . "' ";
    }
    if (array_key_exists('reapafter', $opts) && !empty($opts['reapafter'])) {
        $esc_reapafter = mysqli_real_escape_string($db_connect, $opts['reapafter']);
        $sql .= " AND reapdate > '" . $esc_reapafter . "' ";
    }
    if (array_key_exists('reapmonth', $opts) && !empty($opts['reapmonth'])) {
        $esc_reapmonth = mysqli_real_escape_string($db_connect, $opts['reapmonth']);
        $sql .= " AND reapmonth='" . $esc_reapmonth . "' ";
    }

    $sql .= "ORDER BY pid ASC";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) die(mysqli_error($db_connect));
    // Store data
    $storage = array();
    $row = mysqli_fetch_assoc($res);
    while (!empty($row)) {
        // Contents of row are name, value
        $storage[] = $row;
        $row = mysqli_fetch_assoc($res);
    }
    return $storage;
}

function storage_log_data ($opts = array()) {
  global $db_connect;
// Query database
    $sql = "
        SELECT *
        FROM storage_log";
    if (!empty($opts['order'])) {
        if ($opts['order'] == "reverse") {
            $esc_order = mysqli_real_escape_string($db_connect, "DESC");
        } else {
            $esc_order = mysqli_real_escape_string($db_connect, "ASC");
        }
        $sql .= " ORDER BY timestamp " . $esc_order;
    }
    if (!empty($opts['count']))  {
        $esc_count = mysqli_real_escape_string($db_connect, $opts['count']);
        $sql .= " LIMIT ".$esc_count;
    }
    $res = mysqli_query($db_connect, $sql);
    if (!$res) die(mysqli_error($db_connect));
    // Store data
    $log = array();
    $row = mysqli_fetch_assoc($res);
    while (!empty($row)) {
        // Contents of row are name, value
        $log[] = $row;
        $row = mysqli_fetch_assoc($res);
    }
    return $log;
}


/**
 * Add a storage plot
 * @param $plot The plots id,description,reapdate
 * @return The plot structure with as it now exists in the database.
 */
function storage_add ($plot) {
  global $db_connect;
    // Escape values
    $fields = array('pid', 'desc', 'reapdate');
    if (isset($plot['pid'])) {
        // Add key if nonexists, otherise Update existing key
        $pid = $plot['pid'];
        $esc_pid = mysqli_real_escape_string($db_connect, $plot['pid']);
        $esc_desc = mysqli_real_escape_string($db_connect, $plot['desc']);
        $esc_reapdate = mysqli_real_escape_string($db_connect, $plot['reapdate']);
        $esc_reapmonth = mysqli_real_escape_string($db_connect, $plot['reapmonth']);
        $sql = "INSERT INTO storage_plot (pid, `desc`, reapdate, reapmonth) ";
        $sql .="VALUES ('" . $esc_pid . "', '" . $esc_desc . "', '" . $esc_reapdate . "', '" . $esc_reapmonth . "') ";
        $res = mysqli_query($db_connect, $sql);
        if (!$res) {
            message_register('ERROR: ' . mysqli_error($db_connect));
        } else {
            $plot['action'] = 'Add';
            storage_log($plot);
            message_register('Storage Plot Added');
        }
        return crm_get_one('storage', array('pid'=>$pid));
    }
}

/**
 * Update an existing plot
 * @param $opts The plot's id,description,occupant,reapdate
 * @return The plot structure as it now exists in the database.
 */
function storage_edit ($opts) {
// message_register(var_export($opts,true));
    if (isset($opts['pid'])) {
      global $db_connect;
        // Get current info
        $plot = crm_get_one('storage', array('pid'=>$opts['pid']));
        // Add key if nonexists, otherise Update existing key
        $pid = $opts['pid'];
        $esc_pid = mysqli_real_escape_string($db_connect, $opts['pid']);
        $sql = "UPDATE storage_plot ";
        $sql .= "SET ";
        $sql_opts = array();
        if (isset($opts['desc'])) {
            $esc_desc = mysqli_real_escape_string($db_connect, $opts['desc']);
            $sql_opts[] = "`desc` = '" . $esc_desc . "'";
        }
        if (isset($opts['cid']) && $opts['cid'] != '0') {
            $esc_cid = mysqli_real_escape_string($db_connect, $opts['cid']);
            $sql_opts[] = "cid = '" . $esc_cid . "'";
            $sql_opts[] = "email = ''";
        } else {
            if (isset($opts['contact_name'])) {
                $esc_name = mysqli_real_escape_string($db_connect, $opts['contact_name']);
                $sql_opts[] = "cid = '" . $esc_name . "'";
            }
            if (isset($opts['contact_email'])) {
                $esc_email = mysqli_real_escape_string($db_connect, $opts['contact_email']);
                $sql_opts[] = "email = '" . $esc_email . "'";
            }
        }
        if (isset($opts['reapdate'])) {
            $esc_reapdate = mysqli_real_escape_string($db_connect, $opts['reapdate']);
            $sql_opts[] = "reapdate = '" . $esc_reapdate . "'";
        }
        if (isset($opts['reapmonth'])) {
            $esc_reapmonth = mysqli_real_escape_string($db_connect, $opts['reapmonth']);
            $sql_opts[] = "reapmonth = '" . $esc_reapmonth . "'";
        }
        $sql .= implode(", ", $sql_opts);
        $sql .= "WHERE pid = '" . $esc_pid . "' ";
        $res = mysqli_query($db_connect, $sql);
        if (!$res) {
           message_register('SQL: ' . $sql . '<br>ERROR: ' . mysqli_error($db_connect));
        } else {
            if (!array_key_exists('action',$opts)) { $opts['action'] = 'Edit'; }
            storage_log($opts);
            if (!isset($opts['quiet'] )) { message_register('Storage Plot updated'); } // multiple calls for reaping, skip notice
        }
        return crm_get_one('storage', array('pid'=>$pid));
    }
}

/**
 * Delete an existing plot
 * @param $opts the pid of the plot to delete
 */
function storage_delete ($opts) {
    if (isset($opts['pid'])) {
      global $db_connect;
        $plot = crm_get_one('storage', array('pid'=>$opts['pid']));
        $plot['action'] = 'Delete';
        $esc_name = mysqli_real_escape_string($db_connect, $opts['pid']);
        $sql = "DELETE FROM storage_plot WHERE pid = '" . $esc_name . "'";
        $res = mysqli_query($db_connect, $sql);
        if (!$res) die(mysqli_error($db_connect));
        if (mysqli_affected_rows() > 0) {
            storage_log($plot);
            message_register('Storage Plot '.$esc_name.' deleted.');
        }
    } else {
        message_register('No such Storage Plot');
    }
}

function user_plot_vacate ($opts) {
if (isset($opts['pid'])) {
  global $db_connect;
        // $plot = crm_get_one('storage', array('pid'=>$opts['pid']));
        $esc_name = mysqli_real_escape_string($db_connect, $opts['pid']);
        $sql = "UPDATE storage_plot SET cid = NULL WHERE pid = '" . $esc_name . "'";
        $res = mysqli_query($db_connect, $sql);
        if (!$res) { die(mysqli_error($db_connect));} 
        else {
            $opts['action'] = 'Vacate';
            storage_log($opts);
            message_register('Storage Plot '.$esc_name.' vacated.');
        }
    } else {
        message_register('No such Storage Plot');
    }
}

function storage_log ($opts) {

    if (isset($opts['pid'])) {
      global $db_connect;
        // Add to logfile
        $myid = user_id();
        $esc_myid = mysqli_real_escape_string($db_connect, $myid);
        $esc_action = mysqli_real_escape_string($db_connect, $opts['action']);
        $esc_pid = mysqli_real_escape_string($db_connect, $opts['pid']);
        $plot = crm_get_one('storage', array('pid'=>$opts['pid']));
        if (!empty($opts['desc'])) {
            $esc_desc = mysqli_real_escape_string($db_connect, $opts['desc']);
        } else {
            $esc_desc = mysqli_real_escape_string($db_connect, $plot['desc']);
        }
        if (!empty($opts['cid'])) {
            $esc_cid = mysqli_real_escape_string($db_connect, $opts['cid']);
        } else {
            $esc_cid = mysqli_real_escape_string($db_connect, $plot['cid']);
        }
        if (!empty($opts['email'])) {
            $esc_email = mysqli_real_escape_string($db_connect, $opts['email']);
        } else {
            $esc_email = mysqli_real_escape_string($db_connect, $plot['email']);
        }
        if (isset($opts['reapdate'])) {
            $esc_reapdate = mysqli_real_escape_string($db_connect, $opts['reapdate']);
        } else {
            $esc_reapdate = mysqli_real_escape_string($db_connect, $plot['reapdate']);
        }
        if (isset($opts['reapmonth'])) {
            $esc_reapmonth = mysqli_real_escape_string($db_connect, $opts['reapmonth']);
        } else {
            $esc_reapmonth = mysqli_real_escape_string($db_connect, $plot['reapmonth']);
        }

        $sql = "INSERT INTO storage_log (user, action, pid, `desc`, cid, email, reapdate, reapmonth) ";
        $sql .= "VALUES (".$esc_myid.",'".$esc_action."',".$esc_pid.",'".$esc_desc."','".$esc_cid."','".$esc_email."','".$esc_reapdate."','".$esc_reapmonth."');";
        $res = mysqli_query($db_connect, $sql);
        // if (!$res) die(mysqli_error($db_connect));
        // message_register('Secret updated');
        if (!$res) {
            message_register('SQL: ' . $sql . '<br>ERROR: ' . mysqli_error($db_connect));
        // } else {
        //     message_register('Storage Log updated');
        }
    }
}

function storage_reap ($opts) {
        foreach (explode(",", $opts['pidsToReap']) as $pid) { $plots[] = array('pid' => $pid); }
        $today = date('Y-m-d');
        foreach ($plots as $plot) {
            $plotinfo = crm_get_one('storage', array('pid'=>$plot['pid']));
            $contact = crm_get_one('contact', array('cid'=>$plotinfo['cid']));
            if (!empty($contact)) { // get email from contact name, or from email field
                $contact_email = $contact['email'];
            } else if (!empty($plotinfo['email'])) {
                $contact_email = $plotinfo['email'];
            }

            if ($_SESSION['reap_filter_option'] == 'weekThree') {
                // update reap date on week three reaping
                storage_edit(array('pid'=>$plot['pid'],'reapdate'=>$today, 'quiet'=>true, 'action'=>'Reap'));
            }

            if (!empty($contact_email) && variable_get('storage_send_members',true)) {
                $to = $contact_email;
                $subject = $opts['subject'];
                $message = text_replace(array('text'=>$opts['content'],'cid'=>$plotinfo['cid']));
                $fromheader = "From: \"i3Detroit CRM\" <crm@i3detroit.org>\r\n";
                if (variable_get('storage_send_html',false)) {
                    $contentheader = "Content-Type: text/html; charset=ISO-8859-1\r\n";
                } else {
                    $contentheader = "Content-Type: text/plain; charset=ISO-8859-1\r\n";
                }
                $ccheader = "Cc: ".variable_get('storage_admin_email','')."\r\n";
                // $bccheader = "Bcc: ".implode(",", $contact_email)."\r\n";
                $headers = $fromheader.$contentheader.$ccheader.$bccheader;
                if (variable_get('storage_email_headers',false)) {
                    message_register("Sending email: [To:$to] [Subject:$subject] [Message:$message] [Headers:$headers]");
                }
                if(mail($to, $subject, $message, $headers)) {
                    message_register("email sent successfully");
                    storage_log(array('pid'=>$plot['pid'], 'cid'=>$plotinfo['cid'], 'action'=>'email'));
                } else {
                    message_register("email failure");
                }
            }
        }
        if (variable_get('storage_send_announce',false)) {
            // -announce email
            $to = variable_get('storage_announce_address','');
            $subject = $opts['subject_announce'];
            $message = $opts['content_announce'];
            $fromheader = "From: \"i3Detroit CRM\" <crm@i3detroit.org>\r\n";
            if ($sendHTML) {
                 $contentheader = "Content-Type: text/html; charset=ISO-8859-1\r\n";
            } else {
                 $contentheader = "Content-Type: text/plain; charset=ISO-8859-1\r\n";
            }
            $ccheader = "Cc: ".variable_get('storage_admin_email','')."\r\n";
            $headers = $fromheader.$contentheader.$ccheader;
            if (variable_get('storage_email_headers',false)) {
                    message_register("Sending -announce email: [To:$to] [Subject:$subject] [Message:$message] [Headers:$headers]");
            }
            if(mail($to, $subject, $message, $headers)) {
                message_register("-Announce email sent successfully");
            } else {
                message_register("-Announce email failure");
            }
        }

}

/**
 * @return Array mapping payment method values to descriptions.
 */
function reaping_options () {
    $options = array();
    $options['first'] = 'Initial Notification (Monday before 1st Tuesday)';
    $options['second'] = 'Reminder Notification (2nd Tuesday)';
    $options['third'] = 'Reap plots (3rd Tuesday)';
    $options['fourth'] = 'Plot return (4th Tuesday)';
    return $options;
}

function storage_reap_config ($opts) {
    // message_register('storage_reap_config($opts)='.var_export($opts,true));

    switch ($opts['action']) {
        // Plot management
        case 'Update Months':
            $newMonths = "";
            for ($m=1; $m<=12; $m++) {
                if (array_key_exists($m,$opts['reap'])) {
                    $newMonths .= "1";
                } else {
                    $newMonths .= "0";
                }
            }
            variable_set('storage_reap_months',$newMonths);
            message_register('Reaping months updated');
        break;

        case 'Recalculate All Plots':
            $storage = crm_get_data('storage');
            if (count($storage) < 1) {
                return array();
            }
            // count the number of months we reap things
            $storage_reap_months = str_split(variable_get('storage_reap_months','000000000000'),1); //convert to array
            $num_reap_months = substr_count (variable_get('storage_reap_months','000000000000'), "1" );
            $plots_per_month = intval(count($storage)/$num_reap_months); // grab integer portion
            $leftover_plots = count($storage)%$num_reap_months; // grab remainder
            // message_register('total plots'.count($storage).' | months: '.$num_reap_months.' | per month: '.$plots_per_month.' | leftover: '.$leftover_plots);

            // TODO: Figure out how to update the reapmonth based on calucation of number plots per month and active reap months
            $myMonthList = array();
            for ($i=0;$i<=11;$i++) {
                if ($storage_reap_months[$i] == 1) { $myMonthList[] = $i+1; } // list of active storage months
            }
            $myMonth = 0;
            $myCount = 1;
            // message_register(var_export($myMonthList,true));
            foreach ($storage as $plot) {
                // message_register(var_export($plot,true));
                if ($myMonth < $leftover_plots ) { // les than becuase month is offset one
                    // add one since we have leftovers to deal with
                    $plots_this_month = $plots_per_month + 1;
                } else {
                    // no more leftovers
                    $plots_this_month = $plots_per_month;
                }
                storage_edit(array('pid'=>$plot['pid'],'reapmonth'=>$myMonthList[$myMonth], 'action'=>'recalc','quiet'=>true));
                // message_register("storage_edit(array('pid'=>".$plot['pid'].",'reapmonth'=>".$myMonthList[$myMonth].", 'quiet'=>true))");
                $myCount++;
                if ($myCount > $plots_this_month) {
                    $myCount = 1;
                    $myMonth++;
                }
            }
            message_register('All storage plot reap months have been recalculated.');
        break;

        case 'Recalculate Unreaped Plots':
            $year = date('Y') - 1;
            $beforedate = $year.'-12-31';

            $storage = crm_get_data('storage', array('reapbefore'=>$beforedate));
            if (count($storage) < 1) {
                return array();
            }
            // message_register(var_export($storage,true));
            // count the number of months we reap things
            $fourthThu = date('d', strtotime('fourth thursday of this month'));
            if (date('d') > $fourthThu) {
                $fromdate = date('Y-m-d', strtotime('fourth tuesday of next month'));
                $month = date('n', strtotime('next month'));
                $monthName = date('F', strtotime('next month'));
            } else {
                $fromdate = date('Y-m-d', strtotime('fourth tuesday of this month'));
                $month = date('n');
                $monthName = date('F');
            }

            $var_storage_reap_months = variable_get('storage_reap_months','000000000000');
            $storage_reap_months = str_split($var_storage_reap_months,1); //convert to array
            $num_reap_months = substr_count(substr(variable_get('storage_reap_months','000000000000'),$month-1), "1" );
            $plots_per_month = intval(count($storage)/$num_reap_months); // grab integer portion
            $leftover_plots = count($storage)%$num_reap_months; // grab remainder
            // message_register('unreaped plots'.count($storage).' | months: '.$num_reap_months.' | per month: '.$plots_per_month);

            $myMonthList = array();
            for ($i=$month-1;$i<=11;$i++) {
                if ($storage_reap_months[$i] == 1) { $myMonthList[] = $i+1; } // list of active storage months
            }
            $myMonth = 0;
            $myCount = 1;
            // message_register(var_export($myMonthList,true));
            foreach ($storage as $plot) {
                if ($myMonth < $leftover_plots ) { // les than becuase month is offset one
                    // add one since we have leftovers to deal with
                    $plots_this_month = $plots_per_month + 1;
                } else {
                    // no more leftovers
                    $plots_this_month = $plots_per_month;
                }
                storage_edit(array('pid'=>$plot['pid'],'reapmonth'=>$myMonthList[$myMonth], 'action'=>'recalc', 'quiet'=>true));
                $myCount++;
                if ($myCount > $plots_this_month) {
                    $myCount = 1;
                    $myMonth++;
                }
            }
            message_register('All unreaped storage plot reap months have been recalculated.');
        break;

        case 'Update Email':
            variable_set('storage_send_html', $opts['storage_send_html']);
            variable_set('storage_email_headers', $opts['storage_email_headers']);
            variable_set('storage_send_members', $opts['send_members']);
            variable_set('storage_send_announce', $opts['send_announce']);
            variable_set('storage_announce_address', $opts['announce_address']);
            variable_set('storage_subject_'.$opts['thisweek'], $opts['subject_'.$opts['thisweek']]);
            variable_set('storage_body_'.$opts['thisweek'], $opts['body_'.$opts['thisweek']]);
            variable_set('storage_subject_announce_'.$opts['thisweek'], $opts['subject_announce_'.$opts['thisweek']]);
            variable_set('storage_body_announce_'.$opts['thisweek'], $opts['body_announce_'.$opts['thisweek']]);
            message_register('Email templates updated');
            break;

        case 'Update Storage Admins':
            variable_set('storage_admin_email',$opts['storage_admin_email']);
            message_register('Storage Admin emails updated');
            break;
    }
}
// Tables //////////////////////////////////////////////////////////////////////
// Put table generators here
function storage_table () {
    // Determine settings
    $export = false;
    // foreach ($opts as $option => $value) {
    //     switch ($option) {
    //         case 'export':
    //             $export = $value;
    //             break;
    //     }
    // }
    // Get storage data
    $storage = crm_get_data('storage', array());
    if (count($storage) < 1) {
        return array();
    }

    // Initialize table
    $table = array(
        'columns' => array(
            array('title' => 'Plot#', 'class' => 'skip-filter')
            , array('title' => 'Description')
            , array('title' => 'Contact')
            , array('title' => 'Reap Month')
            , array('title' => 'Last Reaping', 'class' => 'skip-filter')
        )
        , 'rows' => array()
        , 'filter' => true
    );

    if (user_access('storage_edit') || user_access('storage_delete')) {
        $table['columns'][] = array('title'=>'Ops','class'=>'');
    }
    // Add rows
    $contact_data = crm_get_data('contact', '');
    foreach ($storage as $plot) {
        // Add storage data
        $row = array();
        if (user_access('storage_view')) {
            // Add cells
            $row[] = $plot['pid'];
            $row[] = $plot['desc'];
            if ( $plot['cid'] ) {
                $crm_user = crm_get_one('contact',array('cid'=>$plot['cid']));
                if ($crm_user) {
                    $cid_to_contact = crm_map($contact_data, 'cid');
                    $row[] = theme('contact_name', $cid_to_contact[$plot['cid']], !$export);
                } else {
                   $row[] = empty($plot['email']) ? $plot['cid'] : '<a href="mailto:'.$plot['email'].'">'.$plot['cid'].'</a>';
                }
            } else {
                $row[] = '';
            }
            $row[] = date('F', mktime(0, 0, 0, $plot['reapmonth'], 10));
            $row[] = $plot['reapdate'];
       }
        // Construct ops array
        $ops = array();
        // Add edit op
        if (user_access('storage_edit')) {
            $ops[] = '<a href=' . crm_url('storage_edit&pid=' . $plot['pid']) . '>edit</a> ';
            // $ops[] = '<a href=' . crm_url('storage_edit&name=' . $key['pid'] . '#tab-edit') . '>edit</a> ';
        }
        // Add delete op
        if (user_access('storage_delete')) {
            $ops[] = '<a href=' . crm_url('delete&type=storage&id=' . $plot['pid']) . '>delete</a>';
        }
        // Add ops row
        $row[] = join(' ', $ops);
        $table['rows'][] = $row;
    }
  return $table;
}

function storage_log_table ($opts) {
    // Determine settings
    $export = false;
    // foreach ($opts as $option => $value) {
    //     switch ($option) {
    //         case 'export':
    //             $export = $value;
    //             break;
    //     }
    // }
    // Get storage data
    $storage_log = crm_get_data('storage_log', $opts);
    if (count($storage_log) < 1) {
        return array();
    }

    // Initialize table
    $table = array(
        'columns' => array(
            array('title' => 'Timestamp')
            , array('title' => 'Action')
            , array('title' => 'User')
            , array('title' => 'Plot#')
            , array('title' => 'Description')
            , array('title' => 'Contact')
            , array('title' => 'Email')
            , array('title' => 'Reap Month')
            , array('title' => 'Last Reaping')
        )
        , 'rows' => array()
    );

   // Add rows
    $contact_data = crm_get_data('contact', '');
    foreach ($storage_log as $log) {
        // Add storage data
        $row = array();
        if (user_access('storage_view')) {
            // Add cells
            $row[] = $log['timestamp'];
            $row[] = $log['action'];
            $log_user = crm_get_one('contact',array('cid'=>$log['user']));
            $user_to_contact = crm_map($contact_data, 'cid');
            $row[] = theme('contact_name', $user_to_contact[$log['user']], !$export);
            $row[] = $log['pid'];
            $row[] = $log['desc'];
            if ( $log['cid'] ) {
                $crm_user = crm_get_one('contact',array('cid'=>$log['cid']));
                if ($crm_user) {
                    $cid_to_contact = crm_map($contact_data, 'cid');
                    $row[] = theme('contact_name', $cid_to_contact[$log['cid']], !$export);
                    $row[] = '';
                } else {
                    $row[] = $log['cid'];
                    $row[] = $log['email'];
                }
            } else {
                $row[] = '';
                $row[] = '';
            }
            $row[] = date('F', mktime(0, 0, 0, $log['reapmonth'], 10));
            $row[] = $log['reapdate'];
        }
    $table['rows'][] = $row;
    }
  return $table;
}

function storage_plot_table ($opts) {
    // Determine settings
    $export = false;
    // foreach ($opts as $option => $value) {
    //     switch ($option) {
    //         case 'export':
    //             $export = $value;
    //             break;
    //     }
    // }
    // Get storage data
    $cid = $opts['cid'];
    $data = crm_get_data('storage', array('cid'=>$cid));
    if (count($data) < 1) {
        $data['cid'] = $opts['cid'];
    }

    // Initialize table

    $table = array(
        "id" => '',
        "class" => '',
        "rows" => array(),
        "columns" => array()
    );

    // Add columns
    if (user_access('storage_view') || $opts['cid'] == user_id()) {
        $table['columns'][] = array("title"=>'Name', 'class'=>'', 'id'=>'');
        $table['columns'][] = array("title"=>'Plot#', 'class'=>'', 'id'=>'');
        $table['columns'][] = array("title"=>'Description', 'class'=>'', 'id'=>'');
        $table['columns'][] = array("title"=>'Reap Month', 'class'=>'', 'id'=>'');
        $table['columns'][] = array("title"=>'Last Reaping', 'class'=>'', 'id'=>'');
    }

    // Add ops column
    if (!$export && user_access('storage_edit') || user_access('storage_delete') || $opts['cid'] == user_id()) {
        $table['columns'][] = array('title'=>'Ops','class'=>'');
    }

    // Add rows
    foreach ($data as $plot) {
        // Add storage data
        $row = array();
        if (user_access('storage_view') || $opts['cid'] == user_id()) {
            // Add cells
            $row[] = theme('contact_name', $opts['cid'], !$export);
            if ( isset($plot['pid'])) {
                $row[] = $plot['pid'];
                $row[] = $plot['desc'];
                $row[] = date('F', mktime(0, 0, 0, $plot['reapmonth'], 10));
                $row[] = $plot['reapdate'];
            } else {
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
            }
        }
        // Construct ops array
        $ops = array();
        if (user_access('storage_edit') || $opts['cid'] == user_id()) {
            // if plot already assigned, show "vacate" button
            if (isset($plot['pid'])) {
                    $ops[] = '<a href=' . crm_url('plot_vacate&pid=' . $plot['pid'].'&cid=' . $opts['cid']) . '>Vacate</a> ';
            } else {
                    $ops[] = '<a href=' . crm_url('plot_assign&cid=' . $opts['cid']) . '>Assign</a> ';
            }
        }
        // Add ops row
        $row[] = join(' ', $ops);
        $table['rows'][] = $row;
    }
  return $table;
}

function storage_reap_table () {
    // Determine settings
    $export = false;
    // foreach ($opts as $option => $value) {
    //     switch ($option) {
    //         case 'export':
    //             $export = $value;
    //             break;
    //     }
    // }

    // Get data
    $monthNum = $_SESSION['reap_month_filter_option'];
    $monthName = date("F", mktime(0, 0, 0, $monthNum, 10));
    $_SESSION['reap_month'] = $monthName;

    $storage = crm_get_data('storage', array('reapmonth'=>$monthNum));
    if (count($storage) < 1) {
        return array();
    }
    $numUnreaped = count($storage);

    // Initialize table
    $table = array(
        'caption' => "Reaping for $monthName"
        , 'columns' => array(
            array('title' => 'Plot#')
            , array('title' => 'Description')
            , array('title' => 'Contact')
            , array('title' => 'Reap Month')
            , array('title' => 'Last Reaping')
        )
        , 'rows' => array()
    );

    $contact_data = crm_get_data('contact', '');
    $toReap = array();
    foreach ($storage as $plot) {
        // var_dump_pre($plot);
        $row = array();
        $row[] = $plot['pid'];
        $toReap[] = $plot['pid'];
        $row[] = $plot['desc'];
        if ( $plot['cid'] ) {
            $crm_user = crm_get_one('contact',array('cid'=>$plot['cid']));
            if ($crm_user) {
                $cid_to_contact = crm_map($contact_data, 'cid');
                $row[] = theme('contact_name', $cid_to_contact[$plot['cid']], !$export);
            } else {
                $row[] = empty($plot['email']) ? $plot['cid'] : '<a href="mailto:'.$plot['email'].'">'.$plot['cid'].'</a>';
            }
        } else {
            $row[] = '';
        }
        $row[] = date('F', mktime(0, 0, 0, $plot['reapmonth'], 10));
        $row[] = $plot['reapdate'];
        // }
        $rows[] = $row;
        $table['rows'][] = $row;
    }
    $_SESSION['pids_to_reap'] = $toReap;
    return $table;
}
// Forms ///////////////////////////////////////////////////////////////////////
// Put form generators here

function storage_add_form () {

    // Ensure user is allowed to edit keys
    if (!user_access('storage_edit')) {
        return NULL;
    }
    $months = array(
        1 => "January" , 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
        7 => "July", 8 => "August", 9 => "Sepember", 10 => "October", 11 => "November", 12 => "December"
    );

    // Create form structure
    $form = array(
        'type' => 'form',
        'method' => 'post',
        'command' => 'storage_add',
        'hidden' => array(
            'action' => 'Add'
        ),
        'fields' => array(
            array(
                'type' => 'fieldset',
                'label' => 'Add Storage Plot',
                'fields' => array(
                    array(
                        'type' => 'text',
                        'label' => 'Plot#',
                        'name' => 'pid'
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Description',
                        'name' => 'desc'
                    ),
                    array(
                        'type' => 'select'
                        , 'label' => 'Reap Month'
                        , 'name' => 'reapmonth'
                        , 'options' => $months
                        , 'selected' => '1'
                    ),
                    array(
                       'type' => 'text'
                        , 'label' => 'Last Reaping'
                        , 'name' => 'reapdate'
                        , 'value' => date('Y-m-d')
                        , 'class' => 'date float'
                    ),
                    array(
                        'type' => 'submit',
                        'value' => 'Add'
                    )
                )
            )
        )
    );
    return $form;
}

function storage_edit_form ($name) {
    // Ensure user is allowed to edit keys
    if (!user_access('storage_edit')) {
        return NULL;
    }

    // Get secret
    $data = crm_get_one('storage', array('pid'=>$name));
    if (empty($data['pid']) || count($data['pid']) < 1) {
        return array();
    }
    $months = array(
        1 => "January" , 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
        7 => "July", 8 => "August", 9 => "Sepember", 10 => "October", 11 => "November", 12 => "December"
    );

    // Get list of active users to populate dropdown
    $contactlist = array('0'=>'');
    $contacts = member_data(array('filter'=>array('active'=>true, 'scholarship'=>true)));
    // message_register(count($contacts));
    // message_register(var_export($contacts,true));
    foreach ($contacts as $contact) {
        $contactlist[$contact['cid']] = member_name($contact['contact']['firstName'], $contact['contact']['middleName'], $contact['contact']['lastName']);
    }
    // message_register(var_export($contactlist,true));


    // Create form structure
    $form = array(
        'type' => 'form',
        'method' => 'post',
        'command' => 'storage_edit',
        'hidden' => array(
            'action' => 'Edit'
        ),
        'fields' => array(
            array(
                'type' => 'fieldset',
                'label' => 'Edit Storage Plot',
                'fields' => array(
                    array(
                        'type' => 'readonly',
                        'label' => 'Plot#',
                        'name' => 'pid',
                        'value' => $data['pid']
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Description',
                        'name' => 'desc',
                        'value' => $data['desc'],
                    ),
                  array(
                        'type' => 'select'
                        , 'label' => 'Member'
                        , 'name' => 'cid'
                        , 'options' => $contactlist
                        , 'selected' => $data['cid']
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'CID/Custom Name',
                        'name' => 'contact_name',
                        'value' => $data['cid'],
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Custom Contact Email',
                        'name' => 'contact_email',
                        'value' => $data['email'],
                    ),
                  array(
                        'type' => 'select'
                        , 'label' => 'Reap Month'
                        , 'name' => 'reapmonth'
                        , 'options' => $months
                        , 'selected' => $data['reapmonth']
                    ),
                    array(
                       'type' => 'text'
                        , 'label' => 'Last Reaping'
                        , 'name' => 'reapdate'
                        , 'value' => $data['reapdate']
                        , 'class' => 'date float'
                    ),
                    array(
                        'type' => 'submit',
                        'desc' => 'Save'
                    )
                )
            )
        )
    );

    return $form;
}

function storage_reap_filter_form ($opts) {
    // Available filters
    $filters = array(
        'weekOne' => 'Initial Notification (Monday before 1st Tuesday)'
        , 'weekTwo' => 'Reminder Notification (2nd Tuesday)'
        , 'weekThree' => 'Reap plots (3rd Tuesday)'
        , 'weekFour' => 'Plot return (4th Tuesday)'
    );

    // Default filter
    if (empty($_SESSION['reap_filter_option'])) { $_SESSION['reap_filter_option'] = 'weekOne'; }
    $selected = $_SESSION['reap_filter_option'];

    // Construct hidden fields to pass GET params
    $hidden = array();
    foreach ($_GET as $key=>$val) {
        $hidden[$key] = $val;
    }
    switch ($opts['tab']) {
        case 'reap' :
            $myTitle = 'Storage Reaping for '.$selected .' of '.$_SESSION['reap_month'];
            break;
        case 'config' :
            $myTitle = 'Notification Emails for '.$selected;
    }
    $form = array(
        'type' => 'form'
        , 'method' => 'get'
        , 'command' => 'reap_filter_'.$opts['tab']
        , 'hidden' => $hidden,
        'fields' => array(
            array(
                'type' => 'fieldset'
                , 'label' => $myTitle
                ,'fields' => array(
                    array(
                        'type' => 'select'
                        , 'name' => 'filter'
                        , 'options' => $filters
                        , 'selected' => $selected
                    ),
                    array(
                        'type' => 'submit'
                        , 'value' => 'Select'
                    )
                )
            )
        )
    );
    return $form;
}

function storage_reap_month_filter_form () {

    $monthNames = array(
        1 => "January" , 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
        7 => "July", 8 => "August", 9 => "Sepember", 10 => "October", 11 => "November", 12 => "December"
    );

    $storage_reap_months = str_split(variable_get('storage_reap_months','000000000000'),1); //convert to array
    $months = array();
    for ($i=1;$i<=12;$i++) {
        if ($storage_reap_months[$i-1] == 1) {
            $months[$i] = $monthNames[$i];
        }
    }

    // Construct hidden fields to pass GET params
    $hidden = array();
    foreach ($_GET as $key=>$val) {
        $hidden[$key] = $val;
    }

    // if this month isn't a valid reap month, show data for the next valid one
    if (!array_key_exists($_SESSION['reap_month_filter_option'], $months)) {
        $findlist = array_merge($storage_reap_months,$storage_reap_months);
        for ($i=$_SESSION['reap_month_filter_option']; $i<=24; $i++) {
            if ( $i > 12 ) { $j = $i-12; } else { $j=$i; }
            if ($findlist[$j-1] == 1) {
                $_SESSION['reap_month_filter_option'] = $j;
                $i=25; //end loop
            }
        }
    }

    $form = array(
        'type' => 'form'
        , 'method' => 'get'
        , 'command' => 'reap_month_filter'
        , 'hidden' => $hidden,
        'fields' => array(
            array(
                'type' => 'fieldset'
                , 'label' => 'Storage Reaping for month of '.$months[$_SESSION['reap_month_filter_option']]
                ,'fields' => array(
                    array(
                        'type' => 'select'
                        , 'name' => 'monthfilter'
                        , 'options' => $months
                        , 'selected' => $_SESSION['reap_month_filter_option']
                    ),
                    array(
                        'type' => 'submit'
                        , 'value' => 'Select'
                    )
                )
            )
        )
    );
    return $form;
}

function storage_reap_email_form() {
    // if no pids to reap then empty the $pidsToReap variable
    $pidsToReap = array_key_exists('pids_to_reap', $_SESSION) ? join(",", $_SESSION['pids_to_reap']) : '';
    $thisWeek = empty($_SESSION['reap_filter_option']) ? 'weekOne' : $_SESSION['reap_filter_option'];
    $storage_subject = text_replace(array('text'=>variable_get('storage_subject_'.$thisWeek,'')));
    $storage_body = text_replace(array('text'=>variable_get('storage_body_'.$thisWeek,''),'pidsToReap'=>$pidsToReap));
    $storage_subject_announce = text_replace(array('text'=>variable_get('storage_subject_announce_'.$thisWeek,'')));
    $storage_body_announce = text_replace(array('text'=>variable_get('storage_body_announce_'.$thisWeek,''),'pidsToReap'=>$pidsToReap));

    if (variable_get('storage_send_members',true)) {
        $member_subject = array(
            'type' => 'textarea'
            , 'label' => 'Subject - Contacts'
            , 'name' => 'subject'
            , 'value' => $storage_subject
            , 'cols' => '100'
            , 'rows' => '1'
        );
        $member_body = array(
            'type' => 'textarea'
            , 'label' => 'Message Body - Contacts'
            , 'name' => 'content'
            , 'value' => $storage_body
            , 'cols' => '100'
            , 'rows' => '10'
        );
    } else {
        $member_subject = array(
            'type' => 'message'
            , 'value' => 'NO e-mail will be sent to members, check box in config page to change'
        );
        $member_body = array(
            'type' => 'message'
            , 'value' => ''
        );
    }

    if (variable_get('storage_send_announce',true)) {
        $announce_subject = array(
            'type' => 'textarea'
            , 'label' => 'Subject - Announce'
            , 'name' => 'subject_announce'
            , 'value' => $storage_subject_announce
            , 'cols' => '100'
            , 'rows' => '1'
        );
        $announce_body = array(
            'type' => 'textarea'
            , 'label' => 'Message Body - Announce'
            , 'name' => 'content_announce'
            , 'value' => $storage_body_announce
            , 'cols' => '100'
            , 'rows' => '10'
        );
    } else {
        $announce_subject = array(
            'type' => 'message'
            , 'value' => 'NO e-mail will be sent to -Announce, check box in config page to change'
        );
        $announce_body = array(
            'type' => 'message'
            , 'value' => ''
        );
    }

    $form = array(
        'type' => 'form'
        , 'method' => 'post'
        , 'command' => 'storage_reap'
        , 'label' => 'Email To Send'
        , 'hidden' => array(
            'action' => 'Reap'
            , 'pidsToReap' => $pidsToReap
            , 'thisweek' => $thisWeek
        )
        , 'fields' => array(
            $member_subject
            , $member_body
            , $announce_subject
            , $announce_body
            , array(
                'type' => 'submit',
                'value' => 'REAP!'
            )
        )
    );
    // var_dump_pre($form);
    return $form;
}

function storage_delete_form ($plot) {
    // Ensure user is allowed to delete keys
    if (!user_access('storage_delete')) {
        return NULL;
    }

    // Get secret data
    $storage = crm_get_one('storage', array('pid'=>$plot));
    // Construct secret name
    $pid = $storage['pid'];
    // Create form structure
    $form = array(
        'type' => 'form',
        'method' => 'post',
        'command' => 'storage_delete',
        'submit' => 'Delete',
        'hidden' => array(
            'pid' => $pid,
            'action' => 'Delete'
        ),
        'fields' => array(
            array(
                'type' => 'fieldset',
                'label' => 'Delete Storage Plot',
                'fields' => array(
                    array(
                        'type' => 'message',
                        'value' => '<p>Are you sure you want to delete Storage Plot ' . $pid . '? This cannot be undone.',
                    // ),
                    // array(
                    //     'type' => 'submit',
                    //     'desc' => 'Delete'
                    )
                )
            )
        )
    );
    return $form;
}

function user_plot_assign_form ($opts) {
    global $db_connect;
    $esc_cid = mysqli_real_escape_string($db_connect, $opts['cid']);
    // Get available plots
    $sql = "SELECT pid, `desc` from storage_plot ";
    $sql .= "WHERE ( cid is NULL or cid = '' ) ";
    $sql .= "ORDER by pid;";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) die(mysqli_error($db_connect));
    while($rs=mysqli_fetch_array($res)){
        $openplots[$rs['pid']] = $rs['pid'] ." - ". $rs['desc'];
    }

    // Create form structure
    $form = array(
        'type' => 'form',
        'method' => 'post',
        'command' => 'user_plot_assign',
        'hidden' => array(
            'cid' => $opts['cid'],
            'action' => 'Assign'
        ),
        'fields' => array(
            array(
                'type' => 'fieldset',
                'label' => 'Assign Storage Plot',
                'fields' => array(
                array(
                        'type' => 'select',
                        'label' => 'Plot',
                        'name' => 'pid',
                        'options' => $openplots
                    ),
                    array(
                        'type' => 'submit',
                        'desc' => 'Assign'
                    )
                )
            )
        )
    );
    return $form;
}

function user_plot_vacate_form ($opts) {

    if (!user_access('storage_edit') && $opts['cid'] != user_id()) {
        return NULL;
    }

    // Get plot
    $data = crm_get_data('storage', array('pid' => $opts['pid']));
    // Create form structure

    $form = array(
        'type' => 'form',
        'method' => 'post',
        'command' => 'user_plot_vacate',
        'submit' => 'Vacate',
        'hidden' => array(
            'pid' => $opts['pid'],
            'cid' => $opts['cid'],
            // 'desc' => $data['desc'],
            // 'reapdate' => $opts['reapdate'],
            'action' => 'Vacate'
        ),
        'fields' => array(
            array(
                'type' => 'fieldset',
                'label' => 'Vacate Storage Plot',
                'fields' => array(
                    array(
                        'type' => 'message',
                        'value' => '<p>Are you sure you want to vacate Storage Plot ' . $opts['pid'] . '? This cannot be undone.',
                    // ),
                    // array(
                    //     'type' => 'submit',
                    //     'desc' => 'Delete'
                    )
                )
            )
        )
    );
    return $form;
}

function storage_reap_config_months_form () {
    // Ensure user is allowed to edit keys
    if (!user_access('storage_edit')) {
        return NULL;
    }

    // // //
    // Storage Reap Months
    // // //

    $storage_reap_months = str_split(variable_get('storage_reap_months','000000000000'),1); //convert to array
    // Form table rows and columns
    $columns = array();
    $rows = array();

    // Add column titles
    // $columns[] = array('title' => 'Month');
    // $columns[] = array('title' => 'Month');
    // $columns[] = array('title' => 'Reaping');

    for ($m = 1; $m <= 12; $m++) {
        $columns[] = array('title' => date('F', mktime(0, 0, 0, $m, 10)));
    }

    // Process rows
    $row = array();
    for ($m = 1; $m <= 12; $m++) {

        // $row[] = array(
        //     'type' => 'message'
        //     , 'value' => $m
        // );
        // $row[] = array(
        //     'type' => 'message'
        //     , 'value' => date('F', mktime(0, 0, 0, $m, 10))
        // );
        // check bitwise for month to see if set
        $storage_reap_months[$m-1] == "1" ? $checked = true : $checked = false;
        $row[] = array(
            'type' => 'checkbox',
            'name' => "reap[$m]",
            'checked' => $checked
        );
    }
    $rows[] = $row;

    // Create form structure
     $form = array(
        'type' => 'form'
        , 'method' => 'post'
        , 'command' => 'storage_reap_config'
        , 'fields' => array(
            array(
                'type' => 'table'
                , 'id' => 'storage_reap_config_months'
                , 'columns' => $columns
                , 'rows' => $rows
            )
            , array(
                'type' => 'submit',
                'name' => 'action',
                'value' => 'Update Months'
                , 'class' => 'float'
            )
            , array(
                'type' => 'message'
                , 'value' => '&nbsp'
                , 'class' => 'float'
             )
            , array(
                'type' => 'submit',
                'name' => 'action',
                'value' => 'Recalculate Unreaped Plots'
                , 'class' => 'float'
            )
            , array(
                'type' => 'message'
                , 'value' => '&nbsp'
                , 'class' => 'float'
             )
            , array(
                'type' => 'submit',
                'name' => 'action',
                'value' => 'Recalculate All Plots'
                , 'class' => 'float'
            )
            , array(
                'type' => 'message'
                , 'value' => '&nbsp'
             )

        )
    );
    return $form;
}

function storage_reap_config_email_form () {
    // Ensure user is allowed to edit keys
    if (!user_access('storage_edit')) {
        return NULL;
    }

    $thisWeek = empty($_SESSION['reap_filter_option']) ? 'weekOne' : $_SESSION['reap_filter_option'];

    $form = array(
        'type' => 'form'
        , 'method' => 'post'
        , 'command' => 'storage_reap_config'
        , 'hidden' => array(
            'thisweek' => $thisWeek
            , 'tab' => 'config'
        )
         , 'fields' => array(
            array(
                'type' => 'checkbox',
                'label' => 'Send email as HTML (unchecked is plain text)',
                'name' => 'storage_send_html',
                'checked' => variable_get('storage_send_html',false)
            )
            ,array(
                'type' => 'checkbox',
                'label' => 'Show full email headers',
                'name' => 'storage_email_headers',
                'checked' => variable_get('storage_email_headers',false)
            )
            , array(
                'type' => 'checkbox',
                'label' => 'Send Reaping email to members',
                'name' => 'send_members',
                'checked' => variable_get('storage_send_members',true)
            )
            , array(
                'type' => 'message'
                , 'value' => 'Member Email Subject'
            )
            , array(
                'type' => 'textarea'
                , 'name' => 'subject_'.$thisWeek
                , 'value' => variable_get('storage_subject_'.$thisWeek,'')
                , 'cols' => '100'
                , 'rows' => '1'
            )
            // , array(
            //     'type' => 'submit',
            //     'name' => 'action',
            //     'value' => 'Update Subject'
            // )
            , array(
                'type' => 'message'
                , 'value' => 'Member Email Body'
            )
            , array(
                'type' => 'textarea'
                , 'name' => 'body_'.$thisWeek
                , 'value' => variable_get('storage_body_'.$thisWeek,'')
                , 'cols' => '100'
                , 'rows' => '10'
            )
            // , array(
            //     'type' => 'submit',
            //     'name' => 'action',
            //     'value' => 'Update Body'
            // )
            , array(
                'type' => 'checkbox',
                'label' => 'Send Reaping email to -Announce',
                'name' => 'send_announce',
                'checked' => variable_get('storage_send_announce',true)
            )
            , array(
                'type' => 'message'
                , 'value' => '-Announce Email Address'
            )
            , array(
                'type' => 'textarea'
                , 'name' => 'announce_address'
                , 'value' => variable_get('storage_announce_address','')
                , 'cols' => '100'
                , 'rows' => '1'
            )
            , array(
                'type' => 'message'
                , 'value' => '-Announce Email Subject'
            )
            , array(
                'type' => 'textarea'
                , 'name' => 'subject_announce_'.$thisWeek
                , 'value' => variable_get('storage_subject_announce_'.$thisWeek,'')
                , 'cols' => '100'
                , 'rows' => '1'
            )
            // , array(
            //     'type' => 'submit',
            //     'name' => 'action',
            //     'value' => 'Update Announce Subject'
            // )
            , array(
                'type' => 'message'
                , 'value' => '-Announce Email Body'
            )
            , array(
                'type' => 'textarea'
                , 'name' => 'body_announce_'.$thisWeek
                , 'value' => variable_get('storage_body_announce_'.$thisWeek,'')
                , 'cols' => '100'
                , 'rows' => '10'
            )
            , array(
                'type' => 'submit',
                'name' => 'action',
                'value' => 'Update Email'
            )
            , array(
                'type' => 'message'
                , 'value' => 'Storage Admin email addresses'
            )
            , array(
                'type' => 'textarea'
                , 'name' => 'storage_admin_email'
                , 'value' => variable_get('storage_admin_email','')
                , 'cols' => '100'
                , 'rows' => '3'
            )
            , array(
                'type' => 'submit',
                'name' => 'action',
                'value' => 'Update Storage Admins'
            )
            , array(
                'type' => 'message',
                'value' => 'List of valid email variable substitutions:<br>
                    {{name}} - replaces with individual owner name on email send<br>
                    {{plotlist}} - list plot numbers in comma-separated format<br>
                    {{plotowners}}  - list plots as "pid - owner" format on separate lines<br>
                    {{month}} - name of reaping month<br>
                    {{outby}} - date to vacate plot (3rd tuesday)<br>
                    {{returnon}} - first date to return to plot (4th tuesday)<br>
                    {{returnby}} - last date to return to plot (last day of month)<br>'
            )
        )
    );

    return $form;
}


// Themeing ////////////////////////////////////////////////////////////////////

function theme_storage_add_form ($name) {
    return theme('form', crm_get_form('storage_add', $name));
}

function theme_storage_edit_form ($name) {
    return theme('form', crm_get_form('storage_edit', $name));
}

function theme_user_plot_assign_form ($cid) {
    return theme('form', crm_get_form('user_plot_assign', $cid));
}

function theme_user_plot_vacate_form ($opts) {
    return theme('form', crm_get_form('user_plot_vacate', $opts));
}

function theme_storage_reap_form ($opts) {
    return theme('form', crm_get_form('storage_reap'), $opts);
}

function theme_storage_reap_email_form ($opts) {
    return theme('form', crm_get_form('storage_reap_email'), $opts);
}

function theme_storage_reap_filter_form ($opts) {
    return theme('form', crm_get_form('storage_reap_filter'), $opts);
}

function theme_storage_reap_month_filter_form ($opts) {
    return theme('form', crm_get_form('storage_reap_month_filter'), $opts);
}

function theme_storage_reap_config_months_form ($opts) {
    return theme('form', crm_get_form('storage_reap_config'), $opts);
}

function theme_storage_reap_config_email_form ($opts) {
    return theme('form', crm_get_form('storage_reap_config'), $opts);
}

// Pages ///////////////////////////////////////////////////////////////////////

/**
 * @return An array of pages provided by this module.
 */
function storage_page_list () {
    $pages = array();
    if (user_access('storage_view')) {
        $pages[] = 'storage';
        $pages[] = 'storage_edit';
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
function storage_page (&$page_data, $page_name, $options) {
    switch ($page_name) {
        // Plot management
        case 'storage':
            page_set_title($page_data, 'Storage Plots');

            if (user_access('storage_view')) {
                $storage = theme('table', 'storage', array('show_export'=>true));
            }
            if (user_access('storage_edit')) {
                $storage .= theme('storage_add_form', '');
            }
            page_add_content_top($page_data, $storage, 'View' );

            // Reap tab
            if (user_access('storage_edit')) {
                // set current month
                if (!array_key_exists('reap_month_filter_option', $_SESSION)) {
                    $_SESSION['reap_month_filter_option'] = date('n');
                }
                $thisWeek = array_key_exists('reap_filter', $_SESSION) ? $_SESSION['reap_filter'] : array('week'=>'weekOne');

                $reap_content = theme('form', crm_get_form('storage_reap_month_filter', array('tab'=>'reap')));
                $reap_content .= theme('table', 'storage_reap', array('show_export'=>true));
                $reap_content .= theme('form', crm_get_form('storage_reap_filter', array('tab'=>'reap')));
                $reap_content .= theme('form', crm_get_form('storage_reap_email'));
                page_add_content_top($page_data, $reap_content, 'Reap');
            }

            // Config tab
            if (user_access('storage_edit')) {
                $config_content = theme('form', crm_get_form('storage_reap_config_months'));
                $config_content .= theme('form', crm_get_form('storage_reap_filter', array('tab'=>'config')));
                $config_content .= theme('form', crm_get_form('storage_reap_config_email'));
                page_add_content_top($page_data, $config_content, 'Config');
            }

            if (user_access('storage_view')) {
                $storage_log = theme('table', 'storage_log', array('count'=>15, 'order'=>'reverse'));
            }
            page_add_content_top($page_data, $storage_log, 'Log' );

            break;

        case 'storage_edit':
            // Capture secret to edit
            $pid = $options['pid'];
            if (empty($pid)) {
                return;
            }
            page_set_title($page_data, 'Edit a Storage Plot');
            if (user_access('storage_edit')) {
                page_add_content_top($page_data, theme('storage_edit_form', $pid));
            }
            break;

        // User Plots
        case 'contact':

            // Capture contact cid
            $cid = $options['cid'];
            if (empty($cid)) {
                return;
            }

            // Add storage tab
            if (user_access('storage_view') || user_access('storage_edit') || user_access('storage_delete') || $cid == user_id()) {
                $plots = theme('table', 'storage_plot', array('cid' => $cid, 'show_export'=>false));
                page_add_content_bottom($page_data, $plots, 'Storage');
            }

            break;

         case 'plot_assign':

            // Capture contact cid
            $cid = $options['cid'];
            if (empty($cid)) {
                return;
            }
            page_set_title($page_data, 'Storage Plot Assignment');
            if (user_access('storage_view') || $cid == user_id()) {
                $plot_content = theme('user_plot_assign_form', array('cid' => $cid));
                page_add_content_top($page_data, $plot_content);
            }
            break;

         case 'plot_vacate':
            // Capture contact cid
            $pid = $options['pid'];
            if (empty($pid)) {
                return;
            }
            $cid = $options['cid'];
            if (empty($cid)) {
                return;
            }
            page_set_title($page_data, 'Vacate Storage Plot');
            if (user_access('storage_view') || $cid == user_id()) {
                $plot_content = theme('user_plot_vacate_form', array('pid' => $pid, 'cid'=>$cid));
                page_add_content_top($page_data, $plot_content);
            }
            break;

    }
}

// Request Handlers ////////////////////////////////////////////////////////////
// Put request handlers here

function command_storage_add() {
    // Verify permissions
    if (!user_access('storage_edit')) {
        error_register('Permission denied: storage_edit');
        return crm_url('storage');
    }
    storage_add($_POST);
    return crm_url('storage');
}

function command_storage_edit() {
    // Verify permissions
    if (!user_access('storage_edit')) {
        error_register('Permission denied: storage_edit');
        return crm_url('storage');
    }
    storage_edit($_POST);
    return crm_url('storage');
}

function command_storage_delete() {
    global $esc_post;
    // Verify permissions
    if (!user_access('storage_delete')) {
        error_register('Permission denied: storage_delete');
        return crm_url('storage');
    }
    storage_delete($_POST);
    return crm_url('storage');
}

function command_user_plot_assign() {
    // Verify permissions
    // if (!user_access('storage_edit') || $_POST['cid'] == user_id()) {
    //     error_register('Permission denied: storage_edit');
    //     return crm_url('contact&cid=' . $_POST['cid'] . '&tab=storage');
    // }
    storage_edit($_POST);
    return crm_url('contact&cid=' . $_POST['cid'] . '&tab=storage');
}

function command_user_plot_vacate() {
    // Verify permissions
    // if (!user_access('storage_edit') || $_POST['cid'] == user_id()) {
    //     error_register('Permission denied: storage_edit');
    //     return crm_url('contact&cid=' . $_POST['cid'] . '&tab=storage');
    // }
    user_plot_vacate($_POST);
    return crm_url('contact&cid=' . $_POST['cid'] . '&tab=storage');
}

function command_storage_reap() {
    global $esc_post;
    // Verify permissions
    if (!user_access('storage_edit')) {
        error_register('Permission denied: storage_edt');
        return crm_url('storage&tab=reap');
    }
    storage_reap($_POST);
    return crm_url('storage&tab=reap');
}

function command_storage_reap_config() {
    global $esc_post;
    // Verify permissions
    if (!user_access('storage_edit')) {
        error_register('Permission denied: storage_edt');
        return crm_url('storage&tab=config');
    }
    storage_reap_config($_POST);
    return crm_url('storage&tab=config');
}

function command_reap_filter_reap () {
    // Set filter in session
    $_SESSION['reap_filter_option'] = $_GET['filter'];
    // Set filter
    if ($_GET['filter'] == 'weekOne') {
        $_SESSION['reap_filter'] = array('week'=>'one');
    }
    if ($_GET['filter'] == 'weekTwo') {
        $_SESSION['reap_filter'] = array('week'=>'two');
    }
    if ($_GET['filter'] == 'weekThree') {
        $_SESSION['reap_filter'] = array('week'=>'three');
    }
    if ($_GET['filter'] == 'weekFour') {
        $_SESSION['reap_filter'] = array('week'=>'four');
    }
    return crm_url('storage&tab=reap');
}

function command_reap_filter_config () {
    // Set filter in session
    $_SESSION['reap_filter_option'] = $_GET['filter'];
    // Set filter
    if ($_GET['filter'] == 'weekOne') {
        $_SESSION['reap_filter'] = array('week'=>'one');
    }
    if ($_GET['filter'] == 'weekTwo') {
        $_SESSION['reap_filter'] = array('week'=>'two');
    }
    if ($_GET['filter'] == 'weekThree') {
        $_SESSION['reap_filter'] = array('week'=>'three');
    }
    if ($_GET['filter'] == 'weekFour') {
        $_SESSION['reap_filter'] = array('week'=>'four');
    }
    return crm_url('storage&tab=config');
}

function command_reap_month_filter () {
    // Set filter in session
    $_SESSION['reap_month_filter_option'] = $_GET['monthfilter'];
    return crm_url('storage&tab=reap');
}
