(function ($) {
  "use strict";

    $(document).on('mousemove', '.zoom-image-inner', function (e) {
        const $container = $(this);
        const $img = $container.find('img');
        const offsetX = e.pageX - $container.offset().left;
        const offsetY = e.pageY - $container.offset().top;
        const xPercent = (offsetX / $container.width()) * 100;
        const yPercent = (offsetY / $container.height()) * 100;
        $img.css({
            'transform-origin': `${xPercent}% ${yPercent}%`,
            'transform': 'scale(2)'
        });
    });

    $(document).on('mouseleave', '.zoom-image-inner', function () {
        $(this).find('img').css({
            'transform': 'scale(1)',
            'transform-origin': 'center center'
        });
    });


$(function () {
    const $trigger = $('.zoom-image');
    const $result = $('#aq-zoom-result');
    $trigger.on('mousemove', function (e) {
        $result.show();
        const $img = $(this).find('img');  
        const imgSrc = $img.attr('src');
        $result.css('background-image', `url(%24%7bimgSrc%7d.html)`);
        const posX = e.pageX - $(this).offset().left;
        const posY = e.pageY - $(this).offset().top;
        const xPercent = (posX / $(this).width()) * 100;
        const yPercent = (posY / $(this).height()) * 100;
        $result.css({
            'background-size': ($(this).width() * 2.5) + 'px', 
            'background-position': xPercent + '% ' + yPercent + '%'
        });
    });
    $trigger.on('mouseleave', function () {
        $result.hide();
    });
});




})(jQuery);
