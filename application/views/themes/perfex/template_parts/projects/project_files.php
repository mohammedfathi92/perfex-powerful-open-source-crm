<?php if($project->settings->upload_files == 1){ ?>
<?php echo form_open_multipart(site_url('clients/project/'.$project->id),array('class'=>'dropzone mbot15','id'=>'project-files-upload')); ?>
<input type="file" name="file" multiple class="hide"/>
<?php echo form_close(); ?>
<div class="text-left mbot20">
  <div id="dropbox-chooser-project-files"></div>
</div>
<?php } ?>
  <table class="table dt-table" data-order-col="4" data-order-type="desc">
    <thead>
      <tr>
        <th><?php echo _l('project_file_filename'); ?></th>
        <th><?php echo _l('project_file__filetype'); ?></th>
        <th><?php echo _l('project_discussion_last_activity'); ?></th>
        <th><?php echo _l('project_discussion_total_comments'); ?></th>
        <th><?php echo _l('project_file_dateadded'); ?></th>
        <?php if(get_option('allow_contact_to_delete_files') == 1){ ?>
        <th><?php echo _l('options'); ?></th>
        <?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($files as $file){
        $path = get_upload_path_by_type('project') . $project->id . '/'. $file['file_name'];
        ?>
        <tr>
         <td data-order="<?php echo $file['file_name']; ?>">
          <a href="#" onclick="view_project_file(<?php echo $file['id']; ?>,<?php echo $file['project_id']; ?>); return false;">
           <?php if(is_image(PROJECT_ATTACHMENTS_FOLDER .$project->id.'/'.$file['file_name']) || (!empty($file['external']) && !empty($file['thumbnail_link']))){
            echo '<div class="text-left"><i class="fa fa-spinner fa-spin mtop30"></i></div>';
            echo '<img class="project-file-image img-table-loading" src="#" data-orig="'.project_file_url($file,true).'" width="100">';
            echo '</div>';
          }
          echo $file['subject']; ?></a>
        </td>
        <td data-order="<?php echo $file['filetype']; ?>"><?php echo $file['filetype']; ?></td>
        <td data-order="<?php echo $file['last_activity']; ?>">
          <?php
          if(!is_null($file['last_activity'])){
            echo time_ago($file['last_activity']);
          } else {
            echo _l('project_discussion_no_activity');
          }
          ?>
        </td>
        <?php $total_file_comments = total_rows('tblprojectdiscussioncomments',array('discussion_id'=>$file['id'],'discussion_type'=>'file')); ?>
        <td data-order="<?php echo $total_file_comments; ?>">
          <?php echo $total_file_comments; ?>
        </td>
        <td data-order="<?php echo $file['dateadded']; ?>">
         <?php echo _dt($file['dateadded']); ?>
       </td>
       <?php if(get_option('allow_contact_to_delete_files') == 1) { ?>
       <td>
        <?php if($file['contact_id'] == get_contact_user_id()){ ?>
        <a href="<?php echo site_url('clients/delete_file/'.$file['id'].'/project'); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
        <?php } ?>
      </td>
      <?php } ?>
    </tr>
    <?php } ?>
  </tbody>
</table>
<div id="project_file_data"></div>
