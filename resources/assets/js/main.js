function showDiv(target, sourcebox) {
    if (document.getElementById(sourcebox).checked) {
        $(target).removeClass('hidden');
    } else {
        $(target).addClass('hidden');
    }
}

function showUsState() {
    if ($("#location_country option:selected").html() === 'United States') {
        $('#select_state').removeClass('hidden');
    } else {
        $('#select_state').addClass('hidden');
    }
}
