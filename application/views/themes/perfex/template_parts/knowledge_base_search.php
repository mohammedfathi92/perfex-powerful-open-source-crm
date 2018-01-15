<?php if(count($groups) > 0 || $this->input->get('kb_q')){ ?>
<div class="jumbotron kb-search-jumbotron">
    <div class="kb-search">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="text-center">
                        <h2 class="mbot30 bold"><?php echo _l('kb_search_articles'); ?></h2>
                        <?php echo form_open(site_url('clients/knowledge_base'),array('method'=>'GET')); ?>
                        <div class="form-group has-feedback has-feedback-left">
                          <div class="input-group">
                            <input type="search" name="kb_q" placeholder="<?php echo _l('have_a_question'); ?>" class="form-control" value="<?php echo $this->input->get('kb_q'); ?>">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-success p8"><?php echo _l('kb_search'); ?></button>
                            </span>
                            <i class="glyphicon glyphicon-search form-control-feedback"></i>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php } ?>
