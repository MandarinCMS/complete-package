<style>
    .gardenlogin-hidden{

      overflow: hidden;
    }
    .gardenlogin-popup-overlay .gardenlogin-internal-message{
      margin: 3px 0 3px 22px;
      display: none;
    }
    .gardenlogin-reason-input{
      margin: 3px 0 3px 22px;
      display: none;
    }
    .gardenlogin-reason-input input[type="text"]{

      width: 100%;
      display: block;
    }
  .gardenlogin-popup-overlay{

    background: rgba(0,0,0, .8);
    position: fixed;
    top:0;
    left: 0;
    height: 100%;
    width: 100%;
    z-index: 1000;
    overflow: auto;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease-in-out:
  }
  .gardenlogin-popup-overlay.gardenlogin-active{
    opacity: 1;
    visibility: visible;
  }
  .gardenlogin-serveypanel{
    width: 600px;
    background: #fff;
    margin: 65px auto 0;
  }
  .gardenlogin-popup-header{
    background: #ffffff;
    padding: 20px;
    border-bottom: 1px solid #ccc;
  }
  .gardenlogin-popup-header h2{
    margin: 0;
  }
  .gardenlogin-popup-body{
      padding: 10px 20px;
  }
  .gardenlogin-popup-footer{
    background: #f9f3f3;
    padding: 10px 20px;
    border-top: 1px solid #ccc;
  }
  .gardenlogin-popup-footer:after{

    content:"";
    display: table;
    clear: both;
  }
  .action-btns{
    float: right;
  }
  .gardenlogin-anonymous{

    display: none;
  }
  .attention, .error-message {
    color: red;
    font-weight: 600;
    display: none;
  }
  .gardenlogin-spinner{
    display: none;
  }
  .gardenlogin-spinner img{
    margin-top: 3px;
  }
  .gardenlogin-pro-message{
    padding-left: 24px;
    color: red;
    font-weight: 600;
    display: none;
  }

</style>
<div class="gardenlogin-popup-overlay">
  <div class="gardenlogin-serveypanel">
    <form action="#" method="post" id="gardenlogin-deactivate-form">
    <div class="gardenlogin-popup-header">
      <h2><?php _e( 'Quick feedback about GardenLogin', 'gardenlogin' ); ?></h2>
    </div>
    <div class="gardenlogin-popup-body">
      <h3><?php _e( 'If you have a moment, please let us know why you are deactivating:', 'gardenlogin' ); ?></h3>
      <ul id="gardenlogin-reason-list">
        <li class="gardenlogin-reason gardenlogin-reason-pro" data-input-type="" data-input-placeholder="">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="pro">
            </span>
            <span><?php _e( " I upgraded to GardenLogin Pro", 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-pro-message"><?php _e( 'No need to deactivate this GardenLogin Core version. Pro version works as an add-on with Core version.', 'gardenlogin' ); ?></div>
        </li>
        <li class="gardenlogin-reason" data-input-type="" data-input-placeholder="">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="1">
            </span>
            <span><?php _e( 'I only needed the module for a short period', 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
        </li>
        <li class="gardenlogin-reason has-input" data-input-type="textfield">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="2">
            </span>
            <span><?php _e( 'I found a better module', 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
          <div class="gardenlogin-reason-input"><span class="message error-message "><?php _e( 'Kindly tell us the Module name.', 'gardenlogin' ); ?></span><input type="text" name="better_module" placeholder="What's the module's name?"></div>
        </li>
        <li class="gardenlogin-reason" data-input-type="" data-input-placeholder="">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="3">
            </span>
            <span><?php _e( 'The module broke my site', 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
        </li>
        <li class="gardenlogin-reason" data-input-type="" data-input-placeholder="">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="4">
            </span>
            <span><?php _e( 'The module suddenly stopped working', 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
        </li>
        <li class="gardenlogin-reason" data-input-type="" data-input-placeholder="">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="5">
            </span>
            <span><?php _e( 'I no longer need the module', 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
        </li>
        <li class="gardenlogin-reason" data-input-type="" data-input-placeholder="">
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="6">
            </span>
            <span><?php _e( "It's a temporary deactivation. I'm just debugging an issue.", 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
        </li>
        <li class="gardenlogin-reason has-input" data-input-type="textfield" >
          <label>
            <span>
              <input type="radio" name="gardenlogin-selected-reason" value="7">
            </span>
            <span><?php _e( 'Other', 'gardenlogin' ); ?></span>
          </label>
          <div class="gardenlogin-internal-message"></div>
          <div class="gardenlogin-reason-input"><span class="message error-message "><?php _e( 'Kindly tell us the reason so we can improve.', 'gardenlogin' ); ?></span><input type="text" name="other_reason" placeholder="Kindly tell us the reason so we can improve."></div>
        </li>
      </ul>
    </div>
    <div class="gardenlogin-popup-footer">
      <label class="gardenlogin-anonymous"><input type="checkbox" /><?php _e( 'Anonymous feedback', 'gardenlogin' ); ?></label>
        <input type="button" class="button button-secondary button-skip gardenlogin-popup-skip-feedback" value="<?php _e( 'Skip & Deactivate', 'gardenlogin'); ?>" >
      <div class="action-btns">
        <span class="gardenlogin-spinner"><img src="<?php echo admin_url( '/images/spinner.gif' ); ?>" alt=""></span>
        <input type="submit" class="button button-secondary button-deactivate gardenlogin-popup-allow-deactivate" value="<?php _e( 'Submit & Deactivate', 'gardenlogin'); ?>" disabled="disabled">
        <a href="#" class="button button-primary gardenlogin-popup-button-close"><?php _e( 'Cancel', 'gardenlogin' ); ?></a>

      </div>
    </div>
  </form>
    </div>
  </div>


  <script>
    (function( $ ) {

      $(function() {

        var moduleSlug = 'gardenlogin';
        // Code to fire when the DOM is ready.

        $(document).on('click', 'tr[data-slug="' + moduleSlug + '"] .deactivate', function(e){
          e.preventDefault();
          $('.gardenlogin-popup-overlay').addClass('gardenlogin-active');
          $('body').addClass('gardenlogin-hidden');
        });
        $(document).on('click', '.gardenlogin-popup-button-close', function () {
          close_popup();
        });
        $(document).on('click', ".gardenlogin-serveypanel,tr[data-slug='" + moduleSlug + "'] .deactivate",function(e){
            e.stopPropagation();
        });

        $(document).click(function(){
          close_popup();
        });
        $('.gardenlogin-reason label').on('click', function(){
          if($(this).find('input[type="radio"]').is(':checked')){
            //$('.gardenlogin-anonymous').show();
            $(this).next().next('.gardenlogin-reason-input').show().end().end().parent().siblings().find('.gardenlogin-reason-input').hide();
          }
        });
        $('input[type="radio"][name="gardenlogin-selected-reason"]').on('click', function(event) {
          $(".gardenlogin-popup-allow-deactivate").removeAttr('disabled');
          $(".gardenlogin-popup-skip-feedback").removeAttr('disabled');
          $('.message.error-message').hide();
          $('.gardenlogin-pro-message').hide();
        });

        $('.gardenlogin-reason-pro label').on('click', function(){
          if($(this).find('input[type="radio"]').is(':checked')){
            $(this).next('.gardenlogin-pro-message').show().end().end().parent().siblings().find('.gardenlogin-reason-input').hide();
            $(this).next('.gardenlogin-pro-message').show()
            $('.gardenlogin-popup-allow-deactivate').attr('disabled', 'disabled');
            $('.gardenlogin-popup-skip-feedback').attr('disabled', 'disabled');
          }
        });
        $(document).on('submit', '#gardenlogin-deactivate-form', function(event) {
          event.preventDefault();

          var _reason =  $('input[type="radio"][name="gardenlogin-selected-reason"]:checked').val();
          var _reason_details = '';

          if ( _reason == 2 ) {
            _reason_details = $("input[type='text'][name='better_module']").val();
          } else if ( _reason == 7 ) {
            _reason_details = $("input[type='text'][name='other_reason']").val();
          }

          if ( ( _reason == 7 || _reason == 2 ) && _reason_details == '' ) {
            $('.message.error-message').show();
            return ;
          }
          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action        : 'gardenlogin_deactivate',
              reason        : _reason,
              reason_detail : _reason_details,
            },
            beforeSend: function(){
              $(".gardenlogin-spinner").show();
              $(".gardenlogin-popup-allow-deactivate").attr("disabled", "disabled");
            }
          })
          .done(function() {
            $(".gardenlogin-spinner").hide();
            $(".gardenlogin-popup-allow-deactivate").removeAttr("disabled");
            window.location.href =  $("tr[data-slug='"+ moduleSlug +"'] .deactivate a").attr('href');
          });

        });

        $('.gardenlogin-popup-skip-feedback').on('click', function(e){
          // e.preventDefault();
          window.location.href =  $("tr[data-slug='"+ moduleSlug +"'] .deactivate a").attr('href');
        })

        function close_popup() {
          $('.gardenlogin-popup-overlay').removeClass('gardenlogin-active');
          $('#gardenlogin-deactivate-form').trigger("reset");
          $(".gardenlogin-popup-allow-deactivate").attr('disabled', 'disabled');
          $(".gardenlogin-reason-input").hide();
          $('body').removeClass('gardenlogin-hidden');
          $('.message.error-message').hide();
          $('.gardenlogin-pro-message').hide();
        }
        });

        })( jQuery ); // This invokes the function above and allows us to use '$' in place of 'jQuery' in our code.
  </script>
