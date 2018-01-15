<div id="gantt"></div>
<script>
  jQuery(function() {
    var gantt_data = <?php echo json_encode($gantt_data); ?>;
    gantt = $("#gantt").gantt({
      source: gantt_data,
      months:JSON.parse(months_json),
      itemsPerPage: 25,
      navigate: 'scroll',
      onRender:function(){
        var rm = $('#gantt .leftPanel .name .fn-label:empty').parents('.name').css('background','initial');
        $('#gantt .leftPanel .spacer').html('<span class="gantt_project_name"><i class="fa fa-cubes"></i> '+$('.project-name').text()+'</span>');
      }
    });
  });
</script>
