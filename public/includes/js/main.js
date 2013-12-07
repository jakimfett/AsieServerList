$(document).ready(function() {
    $('.server-row').click(function(){
        $('#'+$(this).attr('id')+'-mods').toggleClass('hidden');
    });
});