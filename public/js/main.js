$(document).ready(function() {
    $('.server-row').click(function(){
        if ($(this).attr('id').substr($(this).length - 5) === 'mods'){
            $('#'+$(this).attr('id')).toggleClass('hidden');
        } else {
            $('#'+$(this).attr('id')+'-mods').toggleClass('hidden');
        }
    });
});