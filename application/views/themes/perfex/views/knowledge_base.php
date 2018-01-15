<div class="panel_s">
	<div class="panel-body">
		<?php if(count($groups) == 0){ ?>
		<p class="no-margin"><?php echo _l('clients_knowledge_base_articles_not_found'); ?></p>
		<?php } ?>
		<?php if(!$this->input->get('groupid') && !$this->input->get('kb_q')){ ?>
		<?php foreach($groups as $group){ ?>
		<div class="col-md-12">
			<div class="article_group_wrapper">
				<h4 class="bold"><i class="fa fa-folder-o"></i> <a href="<?php echo site_url('knowledge-base'); ?>?groupid=<?php echo $group['groupid']; ?>"><?php echo $group['name']; ?></a>
					<small><?php echo count($group['articles']); ?></small>
				</h4>
				<p><?php echo $group['description']; ?></p>
			</div>
		</div>
		<?php } ?>
		<?php do_action('after_kb_groups_customers_area'); ?>
		<?php } else { ?>
		<div class="col-md-12">
			<?php foreach($groups as $group){ ?>
			<h4 class="bold mbot30"><i class="fa fa-folder-o"></i> <?php echo $group['name']; ?></h4>
			<ul class="list-unstyled articles_list">
				<?php foreach($group['articles'] as $article) { ?>
				<li>
					<a href="<?php echo site_url('knowledge-base/'.$article['slug']); ?>" class="article-heading"><?php echo $article['subject']; ?></a>
					<div class="text-muted mtop10"><?php echo strip_tags(mb_substr($article['description'],0,250)); ?>...</div>
				</li>
				<hr />
				<?php } ?>
			</ul>
			<?php } ?>
		</div>
		<?php do_action('after_kb_group_customers_area'); ?>
		<?php } ?>
	</div>
</div>
