var API_URL = 'https://api.soccerwars.xyz';
var TOKEN = '#superadmin2015';

$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Token', TOKEN);
    }
});


// Navigation

$("#nav-matches").click(function(){
    $("section").hide(function(){
        $("#section-matches").show();
    });
});


// Forms

$("#create-match").on('submit', function(e){
    e.preventDefault();

    $.ajax(API_URL + '/matches', {
        type: 'POST',
        data: JSON.stringify({
            team_1: $('[name=team_1]').val(),
            team_2: $('[name=team_2]').val(),
            start_time: moment($('[name=start_time]').val()).format('YYYY-MM-DD HH:mm:ss'),
            end_time: moment($('[name=end_time]').val()).format('YYYY-MM-DD HH:mm:ss')
        })
    })
        .done(function(data) {
            showMessage("Match <b>#" + data.id + "</b> created");
        })
        .always(function(data) {
            console.log(data);
        });
});

function showMessage(text) {
    $('#message').html(text).show().delay(5000).fadeOut();
}