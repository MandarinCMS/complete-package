<style media="screen">
.gardenlogin-modal.active {
  display: block;
}
.gardenlogin-modal {
    position: fixed;
    overflow: auto;
    height: 100%;
    width: 100%;
    top: 0;
    z-index: 100000;
    display: none;
    background: rgba(0,0,0,0.6);
}
.gardenlogin-modal.active .gardenlogin-modal-dialog {
    top: 10%;
}
.gardenlogin-modal .gardenlogin-modal-dialog {
    background: transparent;
    position: absolute;
    left: 50%;
    margin-left: -298px;
    padding-bottom: 30px;
    top: -100%;
    z-index: 100001;
    width: 596px;
}
.gardenlogin-modal .gardenlogin-modal-header {
    border-bottom: #eeeeee solid 1px;
    background: #fbfbfb;
    padding: 15px 20px;
    position: relative;
    margin-bottom: -10px;
}
.gardenlogin-modal .gardenlogin-modal-body {
    border-bottom: 0;
}
.gardenlogin-modal .gardenlogin-modal-body, .gardenlogin-modal .gardenlogin-modal-footer {
    border: 0;
    background: #fefefe;
    padding: 20px;
}
.gardenlogin-modal .gardenlogin-modal-body>div {
    margin-top: 10px;
}
.gardenlogin-modal .gardenlogin-modal-body>div h2 {
    font-weight: bold;
    font-size: 20px;
    margin-top: 0;
}
.gardenlogin-modal .gardenlogin-modal-body p {
    font-size: 14px;
}
.gardenlogin-modal .gardenlogin-modal-footer {
    border-top: #eeeeee solid 1px;
    text-align: right;
}
.gardenlogin-modal .gardenlogin-modal-footer>.button:first-child {
    margin: 0;
}
.gardenlogin-modal .gardenlogin-modal-footer>.button {
    margin: 0 7px;
}
.gardenlogin-modal .gardenlogin-modal-body>div h2 {
    font-weight: bold;
    font-size: 20px;
    margin-top: 0;
}
.gardenlogin-modal .gardenlogin-modal-body h2 {
    font-size: 20px;
     line-height: 1.5em;
}
.gardenlogin-modal .gardenlogin-modal-header h4 {
    margin: 0;
    padding: 0;
    text-transform: uppercase;
    font-size: 1.2em;
    font-weight: bold;
    color: #cacaca;
    text-shadow: 1px 1px 1px #fff;
    letter-spacing: 0.6px;
    -webkit-font-smoothing: antialiased;
}

.gardenlogin-optout-spinner{
    display: none;
}
</style>


<div class="gardenlogin-modal gardenlogin-modal-opt-out">
  <div class="gardenlogin-modal-dialog">
    <div class="gardenlogin-modal-header">
      <h4><?php _e( 'Opt Out', 'gardenlogin' ); ?></h4>
    </div>
    <div class="gardenlogin-modal-body">
      <div class="gardenlogin-modal-panel active">
        <h2><?php _e( 'We appreciate your help in making the module better by letting us track some usage data.', 'gardenlogin' ); ?></h2>
        <div class="notice notice-error inline opt-out-error-message" style="display: none;">
          <p></p>
        </div>
        <p><?php echo sprintf( __( 'Usage tracking is done in the name of making %1$s GardenLogin %2$s better. Making a better user experience, prioritizing new features, and more good things. We\'d really appreciate if you\'ll reconsider letting us continue with the tracking.', 'gardenlogin' ), '<strong>', '</strong>') ?></p>
        <p><?php echo sprintf( __( 'By clicking "Opt Out", we will no longer be sending any data to %1$s GardenLogin%2$s.', 'gardenlogin' ), '<a href="https://jiiworks.net" target="_blank">', '</a>' ); ?></p>
      </div>
    </div>
    <div class="gardenlogin-modal-footer">
      <form class="" action="<?php echo admin_url( 'modules.php' ) ?>" method="post">
        <span class="gardenlogin-optout-spinner"><img src="<?php echo admin_url( '/images/spinner.gif' ); ?>" alt=""></span>
        <button type='submit' name='gardenlogin-submit-optout' id='gardenlogin_optout_button'  class="button button-secondary button-opt-out" tabindex="1"><?php _e( 'Opt Out', 'gardenlogin' ) ?></button>
        <button class="button button-primary button-close" tabindex="2"><?php _e( 'On second thought - I want to continue helping', 'gardenlogin' ); ?></button>
      </form>
    </div>
  </div>
</div>



<script type="text/javascript">

(function( $ ) {

  $(function() {
    var moduleSlug = 'gardenlogin';
    // Code to fire when the DOM is ready.

    $(document).on('click', 'tr[data-slug="' + moduleSlug + '"] .opt-out', function(e){
        e.preventDefault();
        $('.gardenlogin-modal-opt-out').addClass('active');
    });

    $(document).on('click', '.button-close', function(event) {
      event.preventDefault();
      $('.gardenlogin-modal-opt-out').removeClass('active');
    });

    $(document).on('click','#gardenlogin_optout_button', function(event) {
      event.preventDefault();
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'gardenlogin_optout_yes'
        },
        beforeSend: function(){
          $(".gardenlogin-optout-spinner").show();
          $(".gardenlogin-popup-allow-deactivate").attr("disabled", "disabled");
        }
      })
      .done(function() {
        $(".gardenlogin-optout-spinner").hide();
        $('.gardenlogin-modal-opt-out').removeClass('active');
        location.reload();
      });

    });

  });

})( jQuery ); // This invokes the function above and allows us to use '$' in place of 'jQuery' in our code.
</script>
