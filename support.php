<?php include'db_connect.php' ?>
<section class="mb-4">
    <p class="text-center w-responsive mx-auto mb-5">Do you have any questions? Please do not hesitate to contact us directly. Our team will come back to you within
        a matter of hours to help you.</p>

    <div class="row">
        <div class="col-md-9 mb-md-0 mb-5">
            <form method="POST" id='support-form'>
            <input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION['login_id'] ?>">
                <div class="form-row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="subject" class="">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="message">Your message</label>
                            <textarea type="text" id="message" name="message" rows="5" class="form-control md-textarea" required></textarea>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm my-1" type="submit">Send</button>
            </form>
        </div>
        <div class="col-md-3 text-center">
            <ul class="list-unstyled mb-0">
                <li><i class="fas fa-map-marker-alt fa-2x"></i>
                    <p>536-20115, Egerton , Njoro</p>
                </li>

                <li><i class="fas fa-phone mt-4 fa-2x"></i>
                    <p>+ 254 716 751 979</p>
                </li>

                <li><i class="fas fa-envelope mt-4 fa-2x"></i>
                    <p>jeunice.shakimwa@gmail.com</p>
                </li>
            </ul>
        </div>
    </div>

</section>
<script>
        
	$(document).ready(function(){
           
        $('#support-form').on('submit', (evt) => {
            evt.preventDefault();
            start_load()
            $.ajax({
                url:'ajax.php?action=support',
                method:'POST',
                data: $('#support-form').serialize(),
                success:function(resp){
                    console.log({resp});
                    if(resp ==1){
                        alert_toast("Message sent successfully",'success');
                        end_load();
                    }
                    else if(resp==2){
                        alert_toast("Message saved but could not send email to admin",'info');
                        end_load();
                    }
                    else{
                        alert_toast("Message sent failed. Try again later",'error');
                        end_load();
                    }
                },
                error: function(err){
                    console.log({resp});
                }
            });
        });
		
	});
</script>