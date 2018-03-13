<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$aColumns     = array(
    'subject',
    'CONCAT(firstname," ", lastname)',
    'achievement',
    'start_date',
    'end_date',
    'goal_type'
);

$sIndexColumn = "id";
$sTable       = 'tblgoals';

$join = array('LEFT JOIN tblstaff ON tblstaff.staffid = tblgoals.staff_id');

$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, array(), array('id'));

$output       = $result['output'];
$rResult      = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('goals/goal/' . $aRow['id']) . '">' . $_data . '</a>';
        } else if ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'end_date') {
            $_data = _d($_data);
        } else if ($aColumns[$i] == 'goal_type') {
            $_data = format_goal_type($_data);
        }
        $row[] = $_data;
    }
    ob_start();
    $achievement          = $this->ci->goals_model->calculate_goal_achievement($aRow['id']);
    $percent              = $achievement['percent'];
    $progress_bar_percent = $achievement['progress_bar_percent'];
    ?>
    <input type="hidden" value="<?php
    echo $progress_bar_percent;
    ?>" name="percent">
    <div class="goal-progress" data-reverse="true">
       <strong class="goal-percent"><?php
        echo $percent;
        ?>%</strong>
    </div>
    <?php
    $progress = ob_get_contents();
    ob_end_clean();
    $row[]              = $progress;
    $options = '';
    $options            .= icon_btn('goals/goal/' . $aRow['id'], 'pencil-square-o');
    if(has_permission('goals','','delete')){
        $options .= icon_btn('goals/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
     $row[]              = $options;
    $output['aaData'][] = $row;
}
