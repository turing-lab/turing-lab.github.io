$(function() {
    $('a.scroll-effect').bind('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top
        }, 1500, 'easeInOutExpo');
        close();
        event.preventDefault();
    });
});

function close() {
    if($("#toggle").hasClass("w3-show")) {
        $("#toggle").removeClass("w3-show");
    }
}

function toggle() {
    if($("#toggle").hasClass("w3-show")) {
        $("#toggle").removeClass("w3-show");
    } else {
        $("#toggle").addClass("w3-show");
    }
}

var xd;

function dontScroll(id) {
    xd = document.body.scrollTop;
    document.getElementById(id).style.display='block';
    $("#page-top").addClass("modal-open");
}

function scroll(id) {
    document.getElementById(id).style.display='none';
    $("#page-top").removeClass("modal-open");
    $(document).scrollTop(xd);
}
