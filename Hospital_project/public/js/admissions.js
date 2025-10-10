document.addEventListener('DOMContentLoaded', function () {
    var roomSelect = document.getElementById('room_id');
    var bedSelect = document.getElementById('bed_id');
    if (!roomSelect || !bedSelect) return;

    function setBedOptions(beds) {
        bedSelect.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'No specific bed';
        bedSelect.appendChild(opt);
        beds.forEach(function (b) {
            var o = document.createElement('option');
            o.value = b.bed_id;
            o.textContent = b.room_type + ' - Bed ' + b.bed_no + ' (' + b.bed_type + ') - $' + Number(b.daily_cost).toFixed(2) + '/day';
            bedSelect.appendChild(o);
        });
    }

    roomSelect.addEventListener('change', function () {
        var roomId = roomSelect.value;
        if (!roomId) { setBedOptions([]); return; }
        fetch('../api/beds.php?room_id=' + encodeURIComponent(roomId))
            .then(function (r) { return r.json(); })
            .then(function (data) { setBedOptions(Array.isArray(data) ? data : []); })
            .catch(function () { setBedOptions([]); });
    });
});


