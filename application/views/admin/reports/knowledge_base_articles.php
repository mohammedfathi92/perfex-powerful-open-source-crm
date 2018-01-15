<?php init_head(); ?>
<?php $groups = get_all_knowledge_base_articles_grouped(false); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <?php echo _l('reports_choose_kb_group'); ?>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3">
                                <select class="selectpicker" name="report-group-change" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php foreach($groups as $group){ ?>
                                    <option value="<?php echo $group['groupid']; ?>"><?php echo $group['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <?php foreach($groups as $group){ ?>
                    <div class="col-md-12 group-report hide" id="group_<?php echo $group['groupid']; ?>">
                        <div class="panel_s">
                            <div class="panel-heading">
                                <?php echo $group['name']; ?>
                            </div>
                            <div class="panel-body">
                                <?php foreach($group['articles'] as $article) {
                                    $total_answers = total_rows('tblknowledgebasearticleanswers',array('articleid'=>$article['articleid']));
                                    $total_yes_answers = total_rows('tblknowledgebasearticleanswers',array('articleid'=>$article['articleid'],'answer'=>1));
                                    $total_no_answers = total_rows('tblknowledgebasearticleanswers',array('articleid'=>$article['articleid'],'answer'=>0));
                                    $percent_yes = 0;
                                    $percent_no = 0;
                                    if($total_yes_answers > 0){
                                    	$percent_yes = number_format(($total_yes_answers * 100) / $total_answers,2);
                                    }
                                    if($total_no_answers > 0){
                                    	$percent_no = number_format(($total_no_answers * 100) / $total_answers,2);
                                    }
                                    ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <span class="bold">
                                                        <?php if($article['staff_article'] == 1){ ?>
                                                        <span class="label label-default mright5 inline-block mbot10"><?php echo _l('internal_article'); ?></span>
                                                        <?php } ?>
                                                        <?php echo $article['subject']; ?></span>
                                                        (<?php echo _l('kb_report_total_answers'); ?>: <?php echo $total_answers; ?>)
                                                    </div>
                                                    <?php if($total_yes_answers > 0){ ?>
                                                    <div class="col-md-4 text-right">
                                                        <?php echo _l('report_kb_yes'); ?>: <?php echo $total_yes_answers; ?>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <?php if($total_no_answers > 0 || $total_yes_answers > 0){ ?>
                                            <div class="col-md-12 progress-bars-report-articles">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_yes; ?>">
                                                        0%
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12 text-right">

                                                        <?php echo _l('report_kb_no'); ?>: <?php echo $total_no_answers; ?>
                                                    </div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_no; ?>">
                                                        0%
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="col-md-12">
                                                <p class="no-margin text-info"><?php echo _l('report_kb_no_votes'); ?></p>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <hr />
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php init_tail(); ?>
        <script>
            $(function(){
                var groupid = $('select[name="report-group-change"]').val();
                $('#group_'+groupid).removeClass('hide');
       // Used for knowledge base reports
       $('select[name="report-group-change"]').on('change', function() {
       	var groupid = $(this).val();
       	$('.progress .progress-bar').each(function() {
       		$(this).css('width', 0 + '%');
       		$(this).text(0 + '%');
       	});

       	setTimeout(function() {
       		$('.group-report').addClass('hide');
       		$('#group_' + groupid).removeClass('hide');
       	}, 200);

       	init_progress_bars();
       });
   })
</script>
</body>
</html>
