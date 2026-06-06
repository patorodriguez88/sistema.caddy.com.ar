function _googleAcDebounce(inputEl, opciones) {
    var cfg = Object.assign(
        { fields: ['address_components'], componentRestrictions: { country: 'AR' },
          types: ['geocode','establishment'], debounce: 400, minLength: 3, onSelect: null },
        opciones || {}
    );
    var svc = new google.maps.places.AutocompleteService();
    var placeSvc = new google.maps.places.PlacesService(document.createElement('div'));
    var token = new google.maps.places.AutocompleteSessionToken();
    var timer = null;
    var wrapper = inputEl.parentElement;
    if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';
    var ul = document.createElement('ul');
    ul.style.cssText = 'position:absolute;z-index:99999;width:100%;top:100%;left:0;display:none;max-height:220px;' +
        'overflow-y:auto;border-radius:0 0 4px 4px;list-style:none;padding:0;margin:0;' +
        'background:#fff;border:1px solid rgba(0,0,0,.15);box-shadow:0 .25rem .5rem rgba(0,0,0,.1);';
    wrapper.appendChild(ul);
    function close() { ul.style.display = 'none'; }
    function selectPlace(placeId, description) {
        inputEl.value = description; close();
        placeSvc.getDetails({ placeId: placeId, fields: cfg.fields, sessionToken: token }, function(place, status) {
            token = new google.maps.places.AutocompleteSessionToken();
            if (status === google.maps.places.PlacesServiceStatus.OK && cfg.onSelect) cfg.onSelect(place);
        });
    }
    inputEl.addEventListener('input', function() {
        clearTimeout(timer);
        var val = this.value.trim();
        if (val.length < cfg.minLength) { close(); return; }
        var snap = val;
        timer = setTimeout(function() {
            svc.getPlacePredictions(
                { input: snap, sessionToken: token, componentRestrictions: cfg.componentRestrictions, types: cfg.types },
                function(predictions, status) {
                    ul.innerHTML = '';
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) { close(); return; }
                    predictions.forEach(function(p) {
                        var li = document.createElement('li');
                        li.style.cssText = 'padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;';
                        li.textContent = p.description;
                        li.addEventListener('mouseover', function() { this.style.background = '#f5f5f5'; });
                        li.addEventListener('mouseout', function() { this.style.background = ''; });
                        li.addEventListener('mousedown', function(e) { e.preventDefault(); selectPlace(p.place_id, p.description); });
                        ul.appendChild(li);
                    });
                    ul.style.display = 'block';
                }
            );
        }, cfg.debounce);
    });
    inputEl.addEventListener('blur', function() { setTimeout(close, 200); });
}

function initMap() {
    var inputstart = document.getElementById('start');
    if (!inputstart) return;
    _googleAcDebounce(inputstart, {
        onSelect: function(place) {
            if (!place || !place.address_components) return;
            var provincia = '';
            place.address_components.forEach(function(c) {
                var t = c.types[0];
                if (t === 'administrative_area_level_1') {
                    provincia = c.long_name;
                    if (provincia !== 'Córdoba') {
                        alertify.error('La Provincia de origen debe ser Córdoba, no ' + provincia);
                        inputstart.value = ''; inputstart.focus(); return;
                    }
                } else if (t === 'locality') { document.getElementById('Ciudad_t').value = c.long_name; }
                else if (t === 'postal_code') { document.getElementById('Codigo_Postal_t').value = c.short_name; }
                else if (t === 'street_number') { document.getElementById('Numero_t').value = c.long_name; }
                else if (t === 'route') { document.getElementById('Calle_t').value = c.long_name; }
            });
        }
    });
}

function verDireccion() {
    var inputstartm = document.getElementById('Direccion_t');
    if (!inputstartm) return;
    _googleAcDebounce(inputstartm, {
        onSelect: function(place) {
            if (!place || !place.address_components) return;
            var provincia = '';
            place.address_components.forEach(function(c) {
                var t = c.types[0];
                if (t === 'administrative_area_level_1') {
                    provincia = c.long_name;
                    if (provincia !== 'Córdoba') {
                        alertify.error('La Provincia de origen debe ser Córdoba, no ' + provincia);
                        inputstartm.value = ''; inputstartm.focus(); return;
                    }
                } else if (t === 'locality') { document.getElementById('Ciudad_m').value = c.long_name; }
                else if (t === 'administrative_area_level_3') { document.getElementById('Barrio_m').value = c.long_name; }
                else if (t === 'postal_code') { document.getElementById('CodigoPostal_m').value = c.short_name; }
                else if (t === 'street_number') { document.getElementById('Numero_m').value = c.long_name; }
                else if (t === 'route') { document.getElementById('Calle_m').value = c.long_name; }
            });
        }
    });
}
