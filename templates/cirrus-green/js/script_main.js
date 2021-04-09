jQuery(document).ready(function ($) {
    
    var topo = $(window).scrollTop();
    if(topo >= 90){
        $('#header_wrap').css('background-color', '#FFF');
        $('#search_wrap').css('border-bottom', '1px solid #341e11');
        $('#topmenu a, #topmenu li.active a, #topmenu li.active a:hover').css('color', '#341e11');
        if (!$('#topmenu a').hasClass('border-brown')) {
            $('#topmenu a').addClass('border-brown');
        }
        if (!$('ul.contact-social li a').hasClass('color-brown')) {
            $('ul.contact-social li a').addClass('color-brown');
        }
    }
    
    $(window).on('resize', function () {

        $(window).scroll(function () {
            var scrollTop = $(window).scrollTop();
            
            if (scrollTop >= 90) {
                $('#header_wrap').css('background-color', '#FFF');
                $('#search_wrap').css('border-bottom', '1px solid #341e11');
                $('#topmenu a, #topmenu li.active a, #topmenu li.active a:hover').css('color', '#341e11');
                if (!$('#topmenu a').hasClass('border-brown')) {
                    $('#topmenu a').addClass('border-brown');
                }
                if (!$('ul.contact-social li a').hasClass('color-brown')) {
                    $('ul.contact-social li a').addClass('color-brown');
                }
            } else {
                $('#header_wrap').css('background-color', 'transparent');
                $('#search_wrap').css('border-bottom', '1px solid #FFF');
                $('#topmenu a, #topmenu li.active a, #topmenu li.active a:hover').css('color', '#FFF');
                if ($('#topmenu a').hasClass('border-brown')) {
                    $('#topmenu a').removeClass('border-brown');
                }
                if ($('ul.contact-social li a').hasClass('color-brown')) {
                    $('ul.contact-social li a').removeClass('color-brown');
                }
            }
        });


    }).trigger('resize');

    $('.menuresp').hide();

    $('#gotomenu').click(function () {
        $('.menuresp').css('visibility', 'visible');
        $('.menuresp').slideToggle();
    });
});