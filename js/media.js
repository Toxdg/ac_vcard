jQuery(function($) {
  if ($(".ac-vcard-form").length) {
    //alert();
    var frame;
    var id; // tablica
    var attachment;
    $("#set-ico-button").click(function() {
      id = $("#ico_id").val();
      if (!frame) {
        frame = wp.media({
          frame: "select",
          title: "fotka",
          multiple: false
        });
        frame.on("select", onImageSelect);
        frame.on("open", onImageChecked);
      }
      frame.open();
      return false;
    });

    function onImageChecked() {
      var selection = frame.state().get("selection");
      var library = frame.state().get("library");
      var attachment = wp.media.attachment(id);

      attachment.fetch({
        success: function() {
          library.add(attachment);
          selection.reset([attachment]);
        }
      });
    }
    function onImageSelect() {
      var selection = frame.state().get("selection");

      if (selection.length) {
        selection.each(function(item) {});
        console.log(selection.first().get('id'));
        console.log(selection.first().get('sizes').full.url);
        //$("#icon_input").val(selection.first().get("sizes").full.url);
		$('#person_imagebox').html('<img src="'+selection.first().get("sizes").full.url+'">');
        $("#icon_input").val(selection.first().get("id"));
      }
    }

    $("#reset_ico").click(function() {
		$('#person_imagebox').html('');
		$("#icon_input").val("");
    });
  }
});