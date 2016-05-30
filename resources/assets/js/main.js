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

function showLocation() {
    if ($("#tournament_type_id option:selected").html() === 'online event') {
        $('#select_location').addClass('hidden');
    } else {
        $('#select_location').removeClass('hidden');
    }

}

function calculateAddress(country, state, city, store, address) {
    var q = country;
    if (state !== '') {
        q = q + ', ' + state;
    }
    if (city !== '') {
        q = q + ', ' + city;
    }
    if (address !== '') {
        q = q + ', ' + address;
    } else {
        if (store !== '') {
            q = q + ' ' + store;
        }
    }
    return q;
}

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();
