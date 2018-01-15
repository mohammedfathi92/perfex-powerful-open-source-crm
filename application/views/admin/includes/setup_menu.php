<div id="setup-menu-wrapper" class="animated <?php if($this->session->has_userdata('setup-menu-open') && $this->session->userdata('setup-menu-open') == true){echo 'display-block';} ?>">
    <ul class="nav metis-menu" id="setup-menu">
        <li>
            <a class="close-customizer"><i class="fa fa-close"></i></a>
            <span class="text-left bold customizer-heading"><?php echo _l('setting_bar_heading'); ?></span>
        </li>
        <?php
        $menu_active       = get_option('setup_menu_active');
        $menu_active       = json_decode($menu_active);
        $total_setup_items = count($menu_active->setup_menu_active);
        $m                 = 0;
        foreach ($menu_active->setup_menu_active as $item) {
            if (isset($item->permission) && !empty($item->permission)) {
                if (!has_permission($item->permission, '', 'view')) {
                    $total_setup_items--;
                    continue;
                }
            }
            $submenu          = false;
            $remove_main_menu = false;
            $url              = '';
            if (isset($item->children)) {
                $submenu                 = true;
                $total_sub_items_removed = 0;
                foreach ($item->children as $_sub_menu_check) {
                    if (isset($_sub_menu_check->permission) && !empty($_sub_menu_check->permission)) {
                        if (!has_permission($_sub_menu_check->permission, '', 'view')) {
                            $total_sub_items_removed++;
                        }
                    }
                }

                if ($total_sub_items_removed == count($item->children)) {
                    $submenu          = false;
                    $remove_main_menu = true;
                    $total_setup_items--;
                }
            } else {
                    // child items removed
                if ($item->url == '#') {
                    continue;
                }
                $url = $item->url;
            }
            if ($remove_main_menu == true) {
                continue;
            }
            $url = $item->url;
            if (!_startsWith($url, 'http://') && $url != '#') {
                $url = admin_url($url);
            }
            ?>
            <li>
                <a href="<?php echo $url; ?>"><?php if(!empty($item->icon)){ ?><i class="<?php echo $item->icon; ?> menu-icon"></i><?php } ?><?php echo _l($item->name); ?>
                    <?php if($submenu == true){ ?>
                    <span class="fa arrow"></span>
                    <?php } ?>
                </a>
                <?php if(isset($item->children)){ ?>
                <ul class="nav nav-second-level collapse" aria-expanded="false">
                    <?php foreach($item->children as $submenu){
                        if(isset($submenu->permission) && !empty($submenu->permission)){
                           if(!has_permission($submenu->permission,'','view')){
                               continue;
                           }
                       }
                       $url = $submenu->url;
                       if(!_startsWith($url,'http://')){
                           $url = admin_url($url);
                       }
                       ?>
                       <li>
                        <a href="<?php echo $url; ?>">
                            <?php if(!empty($submenu->icon)){ ?>
                            <i class="<?php echo $submenu->icon; ?> menu-icon"></i>
                            <?php } ?>
                            <?php echo _l($submenu->name); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </li>
            <?php
            $m++;
            do_action('after_render_single_setup_menu',$m);
        }
        ?>
        <?php if(get_option('show_help_on_setup_menu') == 1 && is_admin()){ $total_setup_items++; ?>
        <li>
            <a href="<?php echo do_action('help_menu_item_link','https://help.perfexcrm.com'); ?>" target="_blank"><?php echo do_action('help_menu_item_text',_l('setup_help')); ?></a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php $this->perfex_base->set_setup_menu_visibility($total_setup_items); ?>
