<?php init_head(); ?>
<div id="wrapper">
 <div class="content">
  <div class="row">
   <div class="col-md-12">
    <div class="panel_s">

     <div class="panel-body">
      <?php do_action('before_render_business_news'); ?>
      <h4 class="no-margin">
       <?php echo $title; ?>
     </h4>
     <hr class="hr-panel-heading" />

     <?php
     $count = count($rss_sites);
     if(!is_connected()){
       echo 'No internet connection';
     }else if($count == 0){
       echo 'No RSS Feed sites found';
     }
     ?>
     <div>
      <ul class="nav nav-tabs" role="tablist">
       <?php
       for($i = 0; $i < $count; $i++){ ?>
       <li role="presentation" class="<?php if($i == 0){echo 'active';}; ?>">
        <a href="#<?php echo slug_it($rss_sites[$i]['name']); ?>" aria-controls="<?php echo slug_it($rss_sites[$i]['name']); ?>" role="tab" data-toggle="tab">
         <?php echo $rss_sites[$i]['name']; ?>
       </a>
     </li>
     <?php } ?>
   </ul>
   <div class="tab-content">
     <?php for($x = 0; $x < $count; $x++){ ?>
     <div role="tabpanel" class="tab-pane ptop10 <?php if($x == 0){echo 'active';}; ?>" id="<?php echo slug_it($rss_sites[$x]['name']); ?>">
      <?php
      $ch = curl_init($rss_sites[$x]['feed_url']);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_AUTOREFERER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      $content = curl_exec($ch);
      curl_close($ch);
      if( mb_detect_encoding($content,"auto", true) !== "UTF-8" ) {
        $content = mb_convert_encoding($content, "UTF-8");
      } else {
        $content = utf8_encode($content);
      }
      try {
        $xml = new SimpleXmlElement($content);
        if(isset($xml->channel->item)){
          foreach($xml->channel->item as $entry) { ?>
          <div class="row">
            <div class="col-md-12">
             <h4 class="bold">
              <a href="<?php echo $entry->link; ?>" title="<?php echo $entry->title; ?>" target="_blank">
               <?php echo utf8_decode($entry->title); ?>
             </a>
             <br />
             <small class="mtop10 inline-block"><?php echo _dt(date('Y-m-d H:i:s',strtotime($entry->pubDate))); ?></small>
           </h4>
         </div>
         <div class="clearfix"></div>
         <div class="col-md-12">
           <div class="feed_description">
            <?php
            echo utf8_decode($entry->description);
            ?>
          </div>
        </div>
      </div>
      <hr />
      <?php } ?>
      <?php } ?>
      <?php }
      catch (Exception  $e) { }
      ?>
    </div>
    <?php } ?>
  </div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
</body>
</html>
