<?php init_head(); ?>
<?php function render_theme_styling_picker($id, $value, $target,$css,$additional = ''){
    echo '<div class="input-group mbot15 colorpicker-component" data-target="'.$target.'" data-css="'.$css.'" data-additional="'.$additional.'">
    <input type="text" value="'.$value.'" data-id="'.$id.'" class="form-control" />
    <span class="input-group-addon"><i></i></span>
</div>';
}
$tags = get_styling_areas('tags');
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                       <a href="#" onclick="save_theme_style(); return false;" class="btn btn-info">Save</a>
                   </div>
               </div>
           </div>
           <div class="col-md-3">
               <div class="panel_s">
                   <div class="panel-body picker">
                       <ul class="nav nav-tabs navbar-pills nav-stacked" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_admin_styling" aria-controls="tab_admin_styling" role="tab" data-toggle="tab">
                                Admin
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_customers_styling" aria-controls="tab_customers_styling" role="tab" data-toggle="tab">
                                Customers
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_buttons_styling" aria-controls="tab_buttons_styling" role="tab" data-toggle="tab">
                                Buttons
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_tabs_styling" aria-controls="tab_tabs_styling" role="tab" data-toggle="tab">
                                Tabs
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_modals_styling" aria-controls="tab_modals_styling" role="tab" data-toggle="tab">
                                Modals
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_general_styling" aria-controls="tab_general_styling" role="tab" data-toggle="tab">
                                General
                            </a>
                        </li>
                        <?php if(count($tags) > 0){ ?>
                        <li role="presentation">
                            <a href="#tab_styling_tags" aria-controls="tab_styling_tags" role="tab" data-toggle="tab">
                                Tags
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
         <div class="panel_s">
            <div class="panel-body pickers">

                <div class="tab-content">
                  <div role="tabpanel" class="tab-pane ptop10 active" id="tab_admin_styling">
                     <div class="row">
                         <div class="col-md-12">
                            <?php
                            foreach(get_styling_areas('admin') as $area){ ?>
                            <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                            <?php render_theme_styling_picker($area['id'], get_custom_style_values('admin',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
                            <hr />
                            <?php  } ?>
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane ptop10" id="tab_customers_styling">
                   <div class="row">
                     <div class="col-md-12">
                        <?php foreach(get_styling_areas('customers') as $area){ ?>
                        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                        <?php render_theme_styling_picker($area['id'], get_custom_style_values('customers',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
                        <hr />
                        <?php  } ?>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane ptop10" id="tab_buttons_styling">
               <div class="row">
                 <div class="col-md-12">
                    <?php foreach(get_styling_areas('buttons') as $area){ ?>
                    <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                    <?php render_theme_styling_picker($area['id'], get_custom_style_values('buttons',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
                    <?php if(isset($area['example'])){echo $area['example'];} ?>
                    <div class="clearfix"></div>
                    <hr />
                    <?php  } ?>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane ptop10" id="tab_tabs_styling">
           <div class="row">
             <div class="col-md-12">
                <?php foreach(get_styling_areas('tabs') as $area){ ?>
                <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
                <?php render_theme_styling_picker($area['id'], get_custom_style_values('tabs',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
                <hr />
                <?php  } ?>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane ptop10" id="tab_modals_styling">
       <div class="row">
         <div class="col-md-12">
            <?php foreach(get_styling_areas('modals') as $area){ ?>
            <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
            <?php render_theme_styling_picker($area['id'], get_custom_style_values('modals',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
            <hr />
            <?php  } ?>
            <div class="modal-content theme_style_modal_example">
              <div class="modal">
                <div class="modal-header">
                    <h4 class="modal-title">Example Modal Heading</h4>
                </div>
                <div class="modal-body">
                    Modal Body
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div role="tabpanel" class="tab-pane ptop10" id="tab_general_styling">
   <div class="row">
     <div class="col-md-12">
        <?php foreach(get_styling_areas('general') as $area){ ?>
        <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
        <?php render_theme_styling_picker($area['id'], get_custom_style_values('general',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
        <?php if(isset($area['example'])){echo $area['example'];} ?>
        <hr />
        <?php  } ?>
    </div>
</div>
</div>
<?php if(count($tags) > 0){ ?>
<div role="tabpanel" class="tab-pane ptop10" id="tab_styling_tags">
    <div class="row">
        <div class="col-md-12">
            <?php foreach($tags as $area){ ?>
            <label class="bold mbot10 inline-block"><?php echo $area['name']; ?></label>
            <?php render_theme_styling_picker($area['id'], get_custom_style_values('tags',$area['id']),$area['target'],$area['css'],$area['additional_selectors']); ?>
            <?php if(isset($area['example'])){echo $area['example'];} ?>
            <hr />
            <?php  } ?>
        </div>
    </div>
</div>
<?php  } ?>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<script>
    var pickers = $('.colorpicker-component');
    $(function() {
        $.each(pickers, function() {
            $(this).colorpicker({
                format: "hex"
            });
            $(this).colorpicker().on('changeColor', function(e) {
                var color = e.color.toHex();
                var _class = 'custom_style_' + $(this).find('input').data('id');
                var val = $(this).find('input').val();
                if (val == '') {
                    $('.' + _class).remove();
                    return false;
                }
                var append_data = '';
                var additional = $(this).data('additional');
                additional = additional.split('+');
                if (additional.length > 0 && additional[0] != '') {
                    $.each(additional, function(i, add) {
                        add = add.split('|');
                        append_data += add[0] + '{' + add[1] + ':' + color + ';}';
                    });
                }
                append_data += $(this).data('target') + '{' + $(this).data('css') + ':' + color + ';}';
                if ($('head').find('.' + _class).length > 0) {
                    $('head').find('.' + _class).html(append_data);
                } else {
                    $("<style />", {
                        class: _class,
                        type: 'text/css',
                        html: append_data
                    }).appendTo("head");
                }
            });
        });
    });

    function save_theme_style() {
        var data = [];
        $.each(pickers, function() {
            var color = $(this).find('input').val();
            if (color != '') {
                var _data = {};
                _data.id = $(this).find('input').data('id');
                _data.color = color;
                data.push(_data);
            }
        });
        $.post(admin_url + 'utilities/save_theme_style', {
            data: JSON.stringify(data)
        }).done(function() {
            window.location.reload();
        });
    }
</script>
</body>
</html>
