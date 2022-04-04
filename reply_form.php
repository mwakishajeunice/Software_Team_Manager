<?php 
include 'db_connect.php';
if(isset($_GET['sid'])){
	$qry = $conn->query("SELECT support.*, concat(firstname, ' ' , lastname) as name, type, email FROM support LEFT JOIN users ON support.user_id = users.id WHERE support.id =" .$_GET['sid'])->fetch_array();
	foreach($qry as $k => $v){
		$$k = $v;
	}
}
?>
<form method="POST" id='reply_form'>
    <input type="hidden" id="support_id" name="support_id" value="<?php echo $id ?>">
    <input type="hidden" id="user_email" name="user_email" value="<?php echo $email ?>">
    <input type="hidden" id="user_name" name="user_name" value="<?php echo $name ?>">
    <div class="form-row">
        <div class="col-md-12">
            <div class="form-group">
            <label for="reply">Your Reply</label>
            <textarea type="text" id="reply" name="reply" rows="5" class="form-control summernote" required></textarea>
            </div>
        </div>
    </div>
</form>
<script>
	$(document).ready(function(){
	$('.summernote').summernote({
        height: 200,
        toolbar: [
            [ 'style', [ 'style' ] ],
            [ 'font', [ 'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear'] ],
            [ 'fontname', [ 'fontname' ] ],
            [ 'fontsize', [ 'fontsize' ] ],
            [ 'color', [ 'color' ] ],
            [ 'para', [ 'ol', 'ul', 'paragraph', 'height' ] ],
            [ 'table', [ 'table' ] ],
            [ 'view', [ 'undo', 'redo', 'fullscreen', 'codeview', 'help' ] ]
        ]
    });
     
    $('#reply_form').submit(function(e){
    	e.preventDefault()
    	start_load()
    	$.ajax({
    		url:'ajax.php?action=support_reply',
			data: $('#reply_form').serialize(),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
                console.log({resp});
				if(resp == 1){
					alert_toast('Reply send successfully',"success");
					setTimeout(function(){
						location.reload()
					},1500)
				}
			},
            error: function(err){
                console.log({err})
            }
    	});
    })
</script>