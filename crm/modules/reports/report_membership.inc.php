<?php

/*
    Copyright 2009-2017 Edward L. Platt <ed@elplatt.com>
    
    This file is part of the Seltzer CRM Project
    report_membership.inc.php - Membership graph report
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
$report_membership_theme = 'report_membership';
$report_membership_theme_opts = '';
$report_membership_name = "Membership Status";
$report_membership_desc = "Graphical history of memberships";
/**
 * @return the earliest date of any membership.
 */
function report_membership_earliest_date () {
    global $db_connect;
    $sql = "SELECT `start` FROM `membership` ORDER BY `start` ASC LIMIT 1";
    $res = mysqli_query($db_connect, $sql);
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        return '';
    }
    return $row['start'];
}

/**
 * @return json structure containing membership statistics.
 */
function member_statistics () {
    global $db_connect;
    // Get plans and earliest date
    $plans = crm_map(member_plan_data(), 'pid');
    $results = array();
    foreach ($plans as $pid => $plan) {
        $results[$pid] = array();
    }
    $earliest = report_membership_earliest_date();
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
    global $db_connect;
    // Create temporary table with dates
    $sql = "DROP TEMPORARY TABLE IF EXISTS `temp_months`";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) { crm_error(mysqli_error($db_connect)); }
    $sql = "CREATE TEMPORARY TABLE `temp_months` (`month` date NOT NULL);";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) { crm_error(mysqli_error($db_connect)); }
    $sql = "INSERT INTO `temp_months` (`month`) VALUES " . implode(',', $dates) . ";";
    $res = mysqli_query($db_connect, $sql);
    if (!$res) { crm_error(mysqli_error($db_connect)); }
    // Query number of active memberships for each month
    $sql = "
        SELECT
            `plan`.`pid`
            , `plan`.`name`
            , `temp_months`.`month`
            , UNIX_TIMESTAMP(`temp_months`.`month`) *1000 AS `month_timestamp`
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
    if (!$res) { crm_error(mysqli_error($db_connect)); }
    // Build results
    while ($row = mysqli_fetch_assoc($res)) {
        $results[$row['pid']][] = array(
            // 'x' => (int)$row['month_timestamp']
            // , 'label' => $row['month']
            // , 'y' => (int)$row['member_count']
            (int)$row['month_timestamp']
            //, 'label' => $row['month']
            , (int)$row['member_count']
        );
    }
    // Convert from associative to indexed
    $indexed = array();
    foreach ($results as $pid => $v) {
        $indexed[] = array(
            'key' => $plans[$pid]['name']
            , 'values' => $v
        );
    }
    // reorder so that plans are in the order we want
    // this is some fuck-ugly code, but it works
    $indexed = array($indexed[7], $indexed[9], $indexed[6], $indexed[5], $indexed[8], $indexed[4], $indexed[3], $indexed[2], $indexed[1], $indexed[0]);
    return json_encode($indexed);
}

// Forms ///////////////////////////////////////////////////////////////////////
// Put form generators here


// Themeing ////////////////////////////////////////////////////////////////////
/**
 * @return The themed html for a membership report.
 */
function theme_report_membership () {
    $json = member_statistics();
    // var_dump_pre($json);
    $output = <<<EOF
    <svg id="chart1" width="960" height="500">
    </svg>
    <script type="text/javascript">

    var data = $json;
    var colors = d3.scale.category20();
    var chart;
    nv.addGraph(function() {
        chart = nv.models.stackedAreaChart()
            .useInteractiveGuideline(true)
            .x(function(d) { return d[0] })
            .y(function(d) { return d[1] })
            .controlLabels({stacked: "Stacked"})
            .rightAlignYAxis(true)
            .duration(300);
        chart.xAxis.tickFormat(function(d) { return d3.time.format('%x')(new Date(d)) });
        chart.yAxis.tickFormat(d3.format(',.0f'));
        
        d3.select('#chart1')
            .datum(data)
            .transition().duration(1000)
            .call(chart)
            .each('start', function() {
                setTimeout(function() {
                    d3.selectAll('#chart1 *').each(function() {
                        if(this.__transition__)
                            this.__transition__.duration = 1;
                    })
                }, 0)
            });
    });
    </script>
EOF;
    return $output;
}


// Pages ///////////////////////////////////////////////////////////////////////
// No pages
