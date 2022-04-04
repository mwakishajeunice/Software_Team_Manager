<?php include 'db_connect.php' ?>
 <div class="col-md-12" >
    <div class="card card-outline card-success" >
      <div class="card-header">
        <b>Support Requests</b>
      </div>
      <div class="card-body p-0" style="">
        <div class="row">
          <div class="col-md-4 d-none d-md-block px-2 position-relative" style="position: relative">
            <?php
                $qry = $conn->query("SELECT support.*, concat(firstname, ' ' , lastname) as name, email FROM support LEFT JOIN users ON support.user_id = users.id ORDER BY status ASC, date_created DESC");
                
                $requests = array();
                if($qry->num_rows > 0){
                  while($row = $qry->fetch_assoc()){
                    $item = array(
                      "id" => $row['id'],
                      "message" => str_replace("&#x2019;", "'", $row['message']),
                      "date_created" => $row['date_created'],
                      "status" => $row['status'],
                      "user_id" => $row['user_id'],
                      "user_name" => $row['name'],
                      "user_email" => $row['email'],
                      "reply" => $row['reply']
                    );
                    array_push($requests, $item);
                  }
                }
            ?>
            <ul class="list-group list-group-flash text-left" style="position: absolute; left:0, top:0; overflow-y:scroll; height:100%">
              <?php foreach($requests as $request) : ?>
              <li class="list-group-item text-left " data-sid="<?php echo $request['id'] ?>">
              <div class='row text-left'>
                  <div class="col-10 text-left d-flex flex-column text-left support-<?php echo $request['status'] ?>">
                    <h4 class="card-title text-primary mb-1 text-left" style=""><?php echo $request['user_name']?></h4>
                    <h6 class="card-subtitle show-support text-left"> <?php echo $request['user_email']?></h6>
                  </div>
                  <div class="col-2 d-flex flex-column justify-content-between align-items-center">
                    <button class="btn btn-outline-primary btn-sm show-support" data-sid="<?php echo $request['id'] ?>">View
                  </button>
                  </div>
              </div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="col-md-8">
            <div class="card">
              <h4 class="card-header" id="support_subject">Subject Of message</h4>
              <div class="card-body">
                <h5 class="cardt-title" id="support_user_name">Sender Name</h5>
                <h6 class="card-subtitle text-muted" id="support_user_role">Sender Role</h6>
                <div class="card-text my-1" id="support_message">
                  Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatem reiciendis non consectetur adipisci labore dolores illum iste soluta excepturi modi voluptas atque, tenetur aut ullam aspernatur quo ea sunt recusandae!
                </div>
                <form method="POST" id='reply_form'>
                  <input type="hidden" id="support_id" name="support_id" >
                  <input type="hidden" id="support_email" name="user_email" >
                  <input type="hidden" id="support_name" name="user_name" >
                  <div class="form-row">
                      <div class="col-md-12">
                          <div class="form-group">
                          <label for="reply">Your Reply</label>
                          <textarea type="text" id="reply" name="reply" rows="4" class="form-control" required></textarea>
                          </div>
                      </div>
                      <button class="btn btn-sm btn-primary" type="submit">Send Reply</button>
                  </div>
              </form>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
<script>
  $('.show-support').click( (evt) =>{
    let id = evt.target.getAttribute('data-sid');
    showRequest(id);
  });

  showRequest(<?php echo $requests[0]['id'] ?? 0 ?>);

  function showRequest(id){
    let data = new FormData();
    data.append("id", id);

    $.ajax({
    	url:`ajax.php?action=get_single_support_request`,
			data: data,
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      type: 'POST',
      success:function(resp){
        resp = JSON.parse(resp);
        $('#support_subject').text(resp.subject || 'Support Request');
        $('#support_id').val(resp.id);
        $('#support_name').val(resp.user_name);
        $('#support_email').val(resp.user_email);
        $('#support_user_name').text(resp.user_name);
        $('#support_user_role').text(resp.user_role);
        $('#support_message').html(resp.message);
        $('#reply').text(resp.reply);
        $('#btn-show-reply-form').attr('data-support-subject', resp.subject || 'Support Request');
        $('#btn-show-reply-form').attr('data-support-id',resp.id);
        
      },
      error: function(err){
        console.log(err);
      }
    });
  }

  $('#reply_form').submit(function(e){
    e.preventDefault()
    start_load();

    let data = new FormData();
    data.append("support_id", $('#support_id').val());
    data.append("support_name", $('#support_name').val());
    data.append("support_email", $('#support_email').val());
    data.append("reply", $('#reply').val());

    $.ajax({
      url:'ajax.php?action=support_reply',
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      type: 'POST',
      success:function(resp){
        console.log({resp});
        if(resp == 1){
          alert_toast('Reply send successfully',"success");
          end_load();
          setTimeout(() => {
            window.reload()
          }, 3000);
        }
      },
      error: function(err){
          console.log({err})
          end_load();
      }
    });
  });
</script>