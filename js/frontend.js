jQuery(document).ready(function($) {

    //inline content should have our class and the close btn
    $('.link2p-btn').each(function(){
        var url = decodeURIComponent($(this).val());
        var begin = url.charAt(0);
        if ((begin == '.' || begin == '#') && url.length > 1)
            if ($(url).length)
                $(url)
                .addClass('link2p-popup')
                .append('<span class="link2p-btn-close">&times;</span>');
    })
    //the background of modal
    if (!$('.link2p-modal').length)
        $('body').append('<div class="link2p-modal"></div>');

    //close modal
    $('.link2p-btn-close').on('click',function() {
        $(this).parent().hide();
        $('.link2p-modal').fadeOut(350);
    });
    $('.link2p-modal').click(function() {
        $('.link2p-popup').hide();
        $('.link2p-modal').fadeOut(350);
    });

    //click on btn
    $('.link2p-btn').click(function(event) {
        event.preventDefault();
        var url = decodeURIComponent($(this).val());
        var begin = url.charAt(0);
        if ((begin=='.' || begin=='#') && $(url).length)
            //inline content
            modal_me(url);
        else if (url == '#')
            //note
            pop_the_note($(this).data('l2pnote')); 
        else if (inner_hostname(url))
            //inner page
            grab_content(url);
        else
            //outside page
            go_to_url(url);   
    });

    //inline content
    function modal_me(url){
        //do nothing if already open
        if ($(url+':visible').length) return;
        //close the open modal, if any
        $(".link2p-popup:visible").hide();
        //modal transparency
        $('.link2p-modal').fadeIn(350)
        //popup
        $(url).show().css('top', $(url).height());
        $('html, body').animate({
            scrollTop: $(url).offset().top
            }, 500); 
    };

    //find if in the same domain
    function inner_hostname(url) {
        var host = url.replace(/^(.*\/\/[^\/?#]*).*$/,"$1");
            protocol = window.location.protocol, 
            hostname = window.location.hostname;
        if (host == hostname || 
            host == 'www.'+hostname ||
            host == protocol+'//'+hostname ||
            host == protocol+'//www.'+hostname) return true;
        return false
    }

    //grab content
    function grab_content (url){
        if (!$('.link2p-content').length)
            $('body').append('<div class="link2p-popup link2p-content"></div>');
        //getting content from external id or class
        var idclass = "|";
        if (url.includes("+#")) idclass = "#";
        if (url.includes("+.")) idclass = ".";
        urldiv = url.split("+"+idclass);
        if (urldiv.length == 2) 
            url = urldiv[0]+" "+idclass+urldiv[1];
        //load content
        $('.link2p-content')
        .load(url , function(_result, _status, xhr){
            if(_status == "success") //can append here
                modal_me('.link2p-content');
            if(_status == "error") //treat it as a link
                window.location.href = urldiv[0];
        });
    }

    //the note
    function pop_the_note (note){
        if (!$('.link2p-content').length)
            $('body').append('<div class="link2p-popup link2p-content"></div>');
        //load content
        $('.link2p-content').html(note);
        modal_me('.link2p-content');
    }

    //external page
    function go_to_url(url){
        if (url.indexOf("http") != 0) url = "http://"+url;
        window.open(url);
    }
});