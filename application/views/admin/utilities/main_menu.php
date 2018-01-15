<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
           <div class="_buttons">
            <a href="#" onclick="save_menu();return false;" class="btn btn-info"><?php echo _l('utilities_menu_save'); ?></a>
            <a href="<?php echo admin_url('utilities/reset_aside_menu'); ?>" class="btn btn-default"><?php echo _l('reset'); ?></a>
          </div>
          <div class="clearfix"></div>
          <hr class="hr-panel-heading no-mbot" />
          <?php
          $menu_active = get_option('aside_menu_active');
          $menu_active = json_decode($menu_active);
          $menu_inactive = get_option('aside_menu_inactive');
          $menu_inactive = json_decode($menu_inactive);
          ?>
          <div class="clearfix"></div>
          <div class="row">
            <div class="col-md-6 border-right">
              <h4 class="bold mtop15"><?php echo _l('active_menu_items'); ?></h4>
              <hr class="hr-panel-heading" />
              <div class="dd active">
                <?php
                $i = 1;
                echo '<ol class="dd-list">';
                if(count($menu_active->aside_menu_active) == 0){ ?>
                <li class="dd-item dd3-empty"></li>
                <?php }
                foreach($menu_active->aside_menu_active as $item){
                  ?>
                  <li class="dd-item dd3-item main" data-id="<?php echo $item->id; ?>" data-permission="<?php echo $item->permission; ?>">
                   <div class="dd-handle dd3-handle"></div>
                   <div class="dd3-content"><?php echo _l($item->name); ?>
                    <?php if($item->permission == 'is_admin'){ ?>
                    - <small class="bold">- <?php echo _l('only_admins'); ?></small>
                    <?php } ?>
                    <a href="#" class="text-muted toggle-menu-options main-item-options pull-right"><i class="fa fa-cog"></i></a>
                  </div>
                  <div class="menu-options main-item-options" style="display:none;" data-menu-options="<?php echo $item->id; ?>">
                   <label class="control-label"><?php echo _l('utilities_menu_name'); ?></label>
                   <div class="input-group mbot15">
                     <input type="text" value="<?php echo $item->name; ?>" class="form-control main-item-name" name="name-menu-item-<?php echo $item->id; ?>">
                     <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_translate_name_help'); ?>"></i></span>
                   </div>
                   <label class="control-label"><?php echo _l('utilities_menu_url'); ?></label>
                   <div class="input-group mbot15">
                     <?php
                     $url = '#';
                     if(isset($item->url) && !empty($item->url)){
                      $url = $item->url;
                    }

                    ?>
                    <input type="text" disabled value="<?php echo $url; ?>" class="form-control main-item-url" name="url-menu-item-<?php echo $item->id; ?>">
                    <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_url_help',admin_url()); ?>"></i></span>
                  </div>
                  <label class="control-label"><?php echo _l('utilities_menu_icon'); ?></label>
                  <div class="input-group">
                   <input type="text" value="<?php echo $item->icon; ?>" class="form-control main-item-icon icon-picker">
                   <span class="input-group-addon">
                     <i class="<?php echo $item->icon; ?>"></i>
                   </span>
                 </div>
               </div>
               <?php if(isset($item->children)){ ?>
               <ol class="dd-list">
                <?php $x = 1; foreach($item->children as $submenu){ ?>
                <li class="dd-item dd3-item sub-items" data-id="<?php echo $submenu->id; ?>" data-permission="<?php echo $submenu->permission; ?>">
                  <div class="dd-handle dd3-handle"></div>
                  <div class="dd3-content"><?php echo _l($submenu->name); ?>
                   <?php if($submenu->permission == 'is_admin'){ ?>
                   <small class="bold">- <?php echo _l('only_admins'); ?></small>
                   <?php } ?>
                   <a href="#" class="text-muted toggle-menu-options sub-item-options pull-right"><i class="fa fa-cog"></i></a>
                 </div>
                 <div class="menu-options sub-item-options" style="display:none;" data-menu-options="<?php echo $submenu->id; ?>">
                   <label class="control-label"><?php echo _l('utilities_menu_name'); ?></label>
                   <div class="input-group">
                     <input type="text" value="<?php echo $submenu->name; ?>" class="form-control sub-item-name" name="name-menu-item-<?php echo $submenu->id; ?>">
                     <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_translate_name_help'); ?>"></i></span>
                   </div>
                   <label class="control-label"><?php echo _l('utilities_menu_url'); ?></label>
                   <div class="input-group">

                     <?php
                     $url = '#';
                     if(isset($submenu->url) && !empty($submenu->url)){
                      $url = $submenu->url;
                    }

                    ?>
                    <input type="text" disabled value="<?php echo $url; ?>" class="form-control sub-item-url" name="url-menu-item-<?php echo $submenu->id; ?>">
                    <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_url_help',admin_url()); ?>"></i></span>
                  </div>
                  <label class="control-label"><?php echo _l('utilities_menu_icon'); ?></label>
                  <div class="input-group">
                   <input type="text" value="<?php echo $submenu->icon; ?>" class="form-control main-item-icon icon-picker">
                   <span class="input-group-addon">
                     <i class="<?php echo $submenu->icon; ?>"></i>
                   </span>
                 </div>
               </div>
             </li>
             <?php $x++; } ?>
           </ol>
           <?php } ?>
         </li>
         <?php $i++; } ?>
       </ol>
     </div>
   </div>
   <div class="col-md-6">
     <h4 class="bold mtop15"><?php echo _l('inactive_menu_items'); ?></h4>
     <hr class="hr-panel-heading" />
     <div class="dd inactive">
      <?php
      $i = 1;
      echo '<ol class="dd-list">'; ?>
      <?php if(count($menu_inactive->aside_menu_inactive) == 0){ ?>
      <li class="dd-item dd3-empty"></li>
      <?php } ?>
      <?php
      foreach($menu_inactive->aside_menu_inactive as $item){
        ?>
        <li class="dd-item dd3-item main" data-id="<?php echo $item->id; ?>" data-permission="<?php echo $item->permission; ?>">
         <div class="dd-handle dd3-handle"></div>
         <div class="dd3-content"><?php echo _l($item->name); ?>
          <a href="#" class="text-muted toggle-menu-options main-item-options pull-right"><i class="fa fa-cog"></i></a>
        </div>
        <div class="menu-options main-item-options" style="display:none;" data-menu-options="<?php echo $item->id; ?>">
         <label class="control-label"><?php echo _l('utilities_menu_name'); ?></label>
         <div class="input-group">
           <input type="text" value="<?php echo $item->name; ?>" class="form-control main-item-name" name="name-menu-item-<?php echo $item->id; ?>">
           <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_translate_name_help'); ?>"></i></span>
         </div>
         <label class="control-label"><?php echo _l('utilities_menu_url'); ?></label>
         <div class="input-group">

           <?php
           $url = '#';
           if(isset($item->url) && !empty($item->url)){
            $url = $item->url;
          }

          ?>
          <input type="text" disabled value="<?php echo $url; ?>" class="form-control main-item-url" name="url-menu-item-<?php echo $item->id; ?>">
          <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_url_help',admin_url()); ?>"></i></span>
        </div>
        <label class="control-label"><?php echo _l('utilities_menu_icon'); ?></label>
        <div class="input-group">
         <input type="text" value="<?php echo $item->icon; ?>" class="form-control main-item-icon icon-picker">
         <span class="input-group-addon">
           <i class="<?php echo $item->icon; ?>"></i>
         </span>
       </div>
     </div>
     <?php if(isset($item->children)){ ?>
     <ol class="dd-list">
      <?php $x = 1; foreach($item->children as $submenu){ ?>
      <li class="dd-item dd3-item sub-items" data-id="<?php echo $submenu->id; ?>" data-permission="<?php echo $submenu->permission; ?>">
        <div class="dd-handle dd3-handle"></div>
        <div class="dd3-content"><?php echo _l($submenu->name); ?>
         <a href="#" class="text-muted toggle-menu-options sub-item-options pull-right"><i class="fa fa-cog"></i></a>
       </div>
       <div class="menu-options sub-item-options" style="display:none;" data-menu-options="<?php echo $submenu->id; ?>">
        <label class="control-label"><?php echo _l('utilities_menu_name'); ?></label>
        <div class="input-group">
         <input type="text" value="<?php echo $submenu->name; ?>" class="form-control sub-item-name" name="name-menu-item-<?php echo $submenu->id; ?>">
         <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_translate_name_help'); ?>"></i></span>
       </div>
       <label class="control-label"><?php echo _l('utilities_menu_url'); ?></label>
       <div class="input-group">
         <?php
         $url = '#';
         if(isset($submenu->url) && !empty($submenu->url)){
          $url = $submenu->url;
        }
        ?>
        <input type="text" disabled value="<?php echo $url; ?>" class="form-control sub-item-url" name="url-menu-item-<?php echo $submenu->id; ?>">
        <span class="input-group-addon"><i class="fa fa-question" data-toggle="tooltip" data-placement="left" data-title="<?php echo _l('utilities_menu_url_help',admin_url()); ?>"></i></span>
      </div>
      <label class="control-label"><?php echo _l('utilities_menu_icon'); ?></label>
      <div class="input-group">
       <input type="text" value="<?php echo $submenu->icon; ?>" class="form-control main-item-icon icon-picker">
       <span class="input-group-addon">
         <i class="<?php echo $submenu->icon; ?>"></i>
       </span>
     </div>
   </div>
 </li>
 <?php $x++; } ?>
</ol>
<?php } ?>
</li>
<?php $i++; } ?>
</ol>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url(); ?>assets/plugins/jquery-nestable/jquery.nestable.js"></script>
<link href="<?php echo base_url(); ?>assets/plugins/font-awesome-icon-picker/css/fontawesome-iconpicker.min.css" rel="stylesheet">
<script src="<?php echo base_url(); ?>assets/plugins/font-awesome-icon-picker/js/fontawesome-iconpicker.js"></script>
<script>
  var iconPickerInitialized = false;
  $(function(){
    _formatMenuIconInput();
    $('.dd').nestable({
      maxDepth: 2
    });
    $('.toggle-menu-options').on('click', function(e) {
      e.preventDefault();
      if(iconPickerInitialized == false){
        $('.icon-picker').iconpicker()
        .on({'iconpickerSetSourceValue': function(e){
          _formatMenuIconInput(e);
        }})
        iconPickerInitialized = true;
      }
      menu_id = $(this).parents('li').data('id');
      if ($(this).hasClass('main-item-options')) {
        $(this).parents('li').find('.main-item-options[data-menu-options="' + menu_id + '"]').slideToggle();
      } else {
        $(this).parents('li').find('.sub-item-options[data-menu-options="' + menu_id + '"]').slideToggle();
      }
    });


  });


  function save_menu() {
    var items = $('.dd.active').find('li.main');
    $.each(items, function() {
      var main_menu = $(this);
      var name = $(this).find('.main-item-options input.main-item-name').val();
      var url = $(this).find('.main-item-options input.main-item-url').val();
      var icon = $(this).find('.main-item-icon').val();
      main_menu.data('name', name);
      main_menu.data('url', url);
      main_menu.data('permission', $(this).data('permission'));
      main_menu.data('icon', icon);

    });

    var sub_items = $('.dd.active li.sub-items');
    $.each(sub_items, function() {
      var sub_item = $(this);
      var name = $(this).find('.sub-item-options input.sub-item-name').val();
      var url = $(this).find('.sub-item-options input.sub-item-url').val();
      var icon = $(this).find('.main-item-icon').val();
      sub_item.data('name', name);
      sub_item.data('url', url);
      sub_item.data('permission', $(this).data('permission'));
      sub_item.data('icon', icon);
    });

    var aside_menu_active = $('.dd.active').nestable('serialize');
    /* Inactive */
    var items_inactive = $('.dd.inactive').find('li.main');
    $.each(items_inactive, function() {
      var main_menu = $(this);
      var name = $(this).find('.main-item-options input.main-item-name').val();
      var url = $(this).find('.main-item-options input.main-item-url').val();
      var icon = $(this).find('.main-item-icon').val();
      main_menu.data('name', name);
      main_menu.data('url', url);
      main_menu.data('permission', $(this).data('permission'));
      main_menu.data('icon', icon);

    });

    var sub_items = $('.dd.inactive li.sub-items');
    $.each(sub_items, function() {
      var sub_item = $(this);
      var name = $(this).find('.sub-item-options input.sub-item-name').val();
      var url = $(this).find('.sub-item-options input.sub-item-url').val();
      var icon = $(this).find('.main-item-icon').val();
      sub_item.data('name', name);
      sub_item.data('url', url);
      sub_item.data('permission', $(this).data('permission'));
      sub_item.data('icon', icon);
    });

    var aside_menu_inactive = $('.dd.inactive').nestable('serialize');
    var data = {};
    data.active = aside_menu_active;
    data.inactive = aside_menu_inactive;
    $.post(admin_url + 'utilities/update_aside_menu', data).done(function() {
      window.location.reload();
    })
  }

</script>
</body>
</html>
