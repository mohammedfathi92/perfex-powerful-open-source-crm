<?php
$project_tabs = get_project_tabs_admin($project->id);
?>
<ul class="nav nav-tabs no-margin project-tabs" role="tablist">
    <?php foreach($project_tabs as $tab){

        $dropdown = isset($tab['dropdown']) ? true : false;
        if($dropdown){

            $total_hidden = 0;
            foreach($tab['dropdown'] as $d){
                if((isset($d['visible']) && $d['visible'] == false) || (isset($project->settings->available_features[$d['name']]) && $project->settings->available_features[$d['name']] == 0)) {
                    $total_hidden++;
                }
            }
            if($total_hidden == count($tab['dropdown'])) {
                continue;
            }
        }
        if((isset($tab['visible']) && $tab['visible'] == true) || !isset($tab['visible'])){
            if(isset($project->settings->available_features[$tab['name']]) && $project->settings->available_features[$tab['name']] == 0){
                continue;
            }
        ?>
        <li class="<?php if($tab['name'] == 'project_overview'){echo 'active ';} ?>project_tab_<?php echo $tab['name']; ?>">
            <a data-group="<?php echo $tab['name']; ?>" href="<?php echo $tab['url']; ?>" role="tab"<?php if($dropdown){ ?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" class="dropdown-toggle" id="dropdown_<?php echo $tab['name']; ?>"<?php } ?>><i class="<?php echo $tab['icon']; ?>" aria-hidden="true"></i> <?php echo $tab['lang']; ?>
               <?php if($dropdown){ ?> <span class="caret"></span> <?php } ?>
           </a>
           <?php if($dropdown){ ?>
           <ul class="dropdown-menu" aria-labelledby="dropdown_<?php echo $tab['name']; ?>">
            <?php

            usort($tab['dropdown'], function($a, $b) {
                return $a['order'] - $b['order'];
            });

            foreach($tab['dropdown'] as $d){
                if((isset($d['visible']) && $d['visible'] == true) || !isset($d['visible'])){
                    echo '<li class="'.(isset($project->settings->available_features[$d['name']]) && $project->settings->available_features[$d['name']] == 0 ? 'hide': '').'"><a href="'.$d['url'].'" data-group="'.$d['name'].'">'.$d['lang'].'</a></li>';
                }
            }
            ?>
        </ul>
        <?php } ?>
    </li>
    <?php } ?>
    <?php } ?>
</ul>
