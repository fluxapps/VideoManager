$(window).load(function () {
    $('.card-image img').each(function () {
        var imgClass = (this.width / this.height > 1) ? 'wide' : 'tall',
            half = this.height / 2;
        $(this).addClass(imgClass);
        $(this).css('margin-top', '-' + half + 'px');
    })
});