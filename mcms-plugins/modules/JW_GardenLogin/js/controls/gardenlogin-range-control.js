/**
 * Script run inside a Customizer control sidebar
 */
(function($) {
    mcms.customize.bind('ready', function() {
        rangeSlider();
    });

    var rangeSlider = function() {
        var slider  = $('.gardenlogin-range-slider'),
            range   = $('.gardenlogin-range-slider_range'),
            value   = $('.gardenlogin-range-slider_val'),
            reset   = $('.gardenlogin-range-reset');

        slider.each(function() {

          value.each(function() {

            var eachVal = $(this).prev().attr('value');

            $(this).val( eachVal );
          });

          value.on( 'keyup', function() {

            var keyupVal = $(this).val();

            $(this).prev().attr( 'value', keyupVal );
            $(this).prev().trigger('input');
          });

          range.on( 'input', function() {

            $(this).next(value).val( this.value );
          });

          reset.on( 'click', function () {

            var rangeVal = $(this).parent().next().data('default-value');
            $(this).parent().next().val( rangeVal );
            $(this).parent().next().trigger('input');
      		});
        });

    };

})(jQuery);
