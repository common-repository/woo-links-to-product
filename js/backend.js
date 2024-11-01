jQuery(document).ready(function($) {

    /*** replica of fields, on the fly ***/
    $('button.new_wclinks2p').on("click", function(event){
        event.preventDefault();
        //adjust idx_max
        if (!$(".wclinks2p_clones").html()) $("#idx_max").val("00");
        var cur_idx = $("#idx_max").val();
        //check that precedent links are present 
        var products = $("#wclinks2p_link").length;
        var empty = wclinks2p_empties(cur_idx, products);
        $("#wclinks2p_alert").html(wclinks2p_vars.option_empty);
        if (products)
            $("#wclinks2p_alert").html(wclinks2p_vars.product_empty);
        if (empty) return false;
        $("#wclinks2p_alert").empty();
        //clone fields
        var replica = $('.wclinks2p_replica').clone()
            .removeClass('wclinks2p_replica')
            .addClass('wclinks2p_clone');
        //remove tip
        replica.find('.woocommerce-help-tip').remove();
        //empty values in the clone
        replica.find('select option').removeAttr('selected');
        replica.find('input').each(
                function(){$(this).attr('value','');});
        replica.find('textarea').html('');
        //update idx_max
        suffix = parseInt(cur_idx)+1;
        if (suffix < 10) suffix = '0' + suffix;
        $("#idx_max").val(suffix);
        //put suffix on fields
        var html = replica.html()
            //products
            .replace(/wclinks2p_retail/gi, 'wclinks2p_retail_' + suffix)
            .replace(/wclinks2p_link/gi, 'wclinks2p_link_' + suffix)
            .replace(/wclinks2p_price/gi,'wclinks2p_price_' + suffix)
            .replace(/wclinks2p_note/gi, 'wclinks2p_note_' + suffix)
            //options page
            .replace(/retailer_n/gi, 'retailer_n_' + suffix)
            .replace(/retailer_txt/gi, 'retailer_txt_' + suffix);
        //append to DOM
        replica
            .html(html)
            .appendTo('.wclinks2p_clones')
            .append('<div class="wclinks2p_killer">&times</div>');
    });

    /*** delete replicants ***/
    $('.wclinks2p_clones').on("click",".wclinks2p_killer",function() {
        event.preventDefault();
        //update max values
        var html = $(this).prev().parent().html();
        var cur_max_id = parseInt($("#idx_max").val());
        var del_suffix = parseInt(wclinks2p_getidx(html));
        if (del_suffix == cur_max_id){
            prev_html = $(this).prev().parent().prev().html();
            new_suffix = parseInt(wclinks2p_getidx(prev_html));
            if (!new_suffix) new_suffix = 1;
            if (new_suffix < 10) new_suffix = '0' + new_suffix;
            $("#idx_max").val(new_suffix);
        }
        //ajax params
        var ajaxnonce = JSON.parse(wclinks2p_vars.ajaxnonce);
        var ajaxurl = JSON.parse(wclinks2p_vars.ajaxurl);
        if ($("#wclinks2p_retail").length){
            var postid = $("#the_postid").val();
            var todo = "delmetas";
        }else{
            var postid = 0;
            var todo ="deloption";
        }
        //ajax requests
        $.ajax({
            url: ajaxurl,
            type:'POST',
            data: {
                action: 'wclinks2p_reqs',
                security: ajaxnonce,
                todo: todo,
                del_suffix: del_suffix,
                postid: postid,
            },
            success:function(results){
                //adjust idx_max
                if (!$(".wclinks2p_clones").html()) 
                    $("#idx_max").val("00");
            }
        });
        //delete
        $(this).prev().parent()
            .fadeOut('normal', function( ){
                $(this).hide('fast',
                    function(){$(this).remove();} 
                );
            } 
        ); 
    });

    /*** go to manage retailers/anchors ***/
    $('button.retail_wclinks2p').on("click", function(event){
        event.preventDefault();
        window.location.href = wclinks2p_vars.admin_uri;
    });
    
    /*** functions ***/
    //get suffix of the clone to be deleted (clicked on 'x')
    function wclinks2p_getidx(html){
        suffix = false;
        if (!html) return false;
        var idx = html.indexOf('wclinks2p_retail');
        if (idx == -1) { //optios page
            idx = html.indexOf('retailer_n');
            suffix = html.charAt(idx+11)+html.charAt(idx+12);
        } else { //products
            suffix = html.charAt(idx+15)+html.charAt(idx+16);
        }
        return suffix;
    }

    //verify fields are not empty
    function wclinks2p_empties(idx, products){
        var empty = false;
        switch(products) {
            case 0: //OPTIONS
                if (idx=="00") {
                    if ($.trim($("#retailer_n").val())=='')
                        $("#retailer_n").val("Amazon");
                    if ($.trim($("#retailer_txt").val())=='')
                        empty = true;
                }else{
                    for (var i=1;i<=idx*1;i++){
                        var ii = i;
                        if (i<10) ii = "0"+i;
                        if ($.trim($("#retailer_n_"+ii).val())==''
                        || $.trim($("#retailer_txt_"+ii).val())=='')
                            empty = true;
                        if (empty) break;        
                    }
                }
                break;
            default: //PRODUCTS
                if (idx=="00") {
                    if ($.trim($("#wclinks2p_link").val())=='')
                        empty = true;
                }else{
                    for (var i=1;i<=idx*1;i++){
                        var ii = i;
                        if (i<10) ii = "0"+i;
                        if ($.trim($("#wclinks2p_link_"+ii).val())=='')
                            empty = true;
                        if (empty) break;        
                    }
                }
                break;
            }
        return empty;
    }

});