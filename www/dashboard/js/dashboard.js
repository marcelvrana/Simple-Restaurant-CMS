
$(document).on('change', '.ajax', function(){
    $.ajax({
        url: $(this).data('url'),
        method: "POST",
        success: function(){
    }
});
});