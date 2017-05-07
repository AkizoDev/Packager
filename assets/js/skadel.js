$('#discordURL').on('keyup', function () {
    var value = $(this).val();
    if(value.includes("canary.discordapp.com")){
        $('#discordCanary').prop('checked', true);
    }else{
        $('#discordCanary').prop('checked', false);
    }
});

$('#generateURL').click(function () {
    var genURL = null;
    var hookURL = $('#discordURL').val();
    var canary = $('#discordCanary').is(':checked');
    var provider = $('input[name=provider]:checked').val();
    if (hookURL && hookURL.includes("discordapp.com")) {
        if (provider && typeof provider !== "undefined") {
            genURL = (canary) ? hookURL.replace("canary.discordapp.com", "skadel.net") + '/canary/' + provider : hookURL.replace("discordapp.com", "skadel.net") + '/' + provider;
            window.copyToClipboard(genURL);
            $('#message').removeClass().addClass('alert alert-success').text('URL generated and copied to your clipboard').hide().fadeIn(500);
        } else {
            $('#message').removeClass().addClass('alert alert-danger').text('Please select a provider').hide().fadeIn(500);
        }
    } else {
        $('#message').removeClass().addClass('alert alert-danger').text('Unable to create URL. Make sure your Discord URL is valid').hide().fadeIn(500);
    }
});

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});