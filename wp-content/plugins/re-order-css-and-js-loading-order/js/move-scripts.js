jQuery(document).ready(function($){

$('script').each(function(){
    if ($(this).prop('src').length == 0) {
        $(this).appendTo('body');
    }
});

});
