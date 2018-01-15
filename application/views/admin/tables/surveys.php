<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'surveyid',
    'subject',
    '(SELECT count(questionid) FROM tblformquestions WHERE tblformquestions.rel_id = tblsurveys.surveyid AND rel_type="survey")',
    '(SELECT count(resultsetid) FROM tblsurveyresultsets WHERE tblsurveyresultsets.surveyid = tblsurveys.surveyid)',
    'datecreated',
    'active'
);
$sIndexColumn = "surveyid";
$sTable       = 'tblsurveys';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array(
    'hash'
));
$output       = $result['output'];
$rResult      = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('surveys/survey/' . $aRow['surveyid']) . '">' . $_data . '</a>';
        } else if ($aColumns[$i] == 'datecreated') {
            $_data = _dt($_data);
        } else if ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }

            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'surveys/change_survey_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['surveyid'].'" data-id="'.$aRow['surveyid'].'" ' . $checked . '>
                <label class="onoffswitch-label" for="c_'.$aRow['surveyid'].'"></label>
            </div>';

            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
        }
        $row[] = $_data;
    }
    $options = '';
    if (total_rows('tblsurveyresultsets', 'surveyid=' . $aRow['surveyid']) > 0) {
        $options .= icon_btn('surveys/results/' . $aRow['surveyid'], 'area-chart', 'btn-success', array(
            'data-toggle' => 'tooltip',
            'title' => _l('survey_list_view_results_tooltip')
        ));
    }
    $options .= icon_btn(site_url('clients/survey/' . $aRow['surveyid'] . '/' . $aRow['hash']), 'eye', 'btn-default', array(
        'data-toggle' => 'tooltip',
        'title' => _l('survey_list_view_tooltip'),
        'target' => '_blank'
    ));

     $options .= icon_btn('surveys/survey/' . $aRow['surveyid'], 'pencil-square-o');
     if(has_permission('surveys','','delete')){
     $options .= icon_btn('surveys/delete/' . $aRow['surveyid'], 'remove', 'btn-danger _delete');
 }

     $row[]              = $options;
    $output['aaData'][] = $row;
}
