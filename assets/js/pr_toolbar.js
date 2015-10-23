jQuery('#pr-toolbar-flush-themes-cache').click(function(){
    jQuery.post(ajaxurl, {
        'action':'pr_flush_themes_cache'
    }, function(response) {
        if (response.success) {
            document.location.href = pr.flush_redirect_url;
        } else {
            alert(pr.flush_failed);
        }
    });
});