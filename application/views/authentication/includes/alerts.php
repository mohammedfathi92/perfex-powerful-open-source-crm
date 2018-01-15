    <div class="row">
     <?php
     $alertclass = "";
     if($this->session->flashdata('message-success')){
      $alertclass = "success";
    } else if ($this->session->flashdata('message-warning')){
      $alertclass = "warning";
    } else if ($this->session->flashdata('message-info')){
      $alertclass = "info";
    } else if ($this->session->flashdata('message-danger')){
      $alertclass = "danger";
    }
    if($this->session->flashdata('message-'.$alertclass)){ ?>
    <div class="col-lg-12" id="alerts">
      <div class="text-center alert alert-<?php echo $alertclass; ?>">
        <?php
        echo $this->session->flashdata('message-'.$alertclass);
        ?>
      </div>
    </div>
    <?php } ?>
  </div>
