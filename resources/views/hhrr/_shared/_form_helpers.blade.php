{{-- IMask masks + Nominatim address autocomplete.
     Include with @include('hhrr._shared._form_helpers') at the bottom of any form.

     Add CSS classes to inputs:
       phone-mask  → (000) 000-0000
       zip-mask    → 00000[-0000]
       state-mask  → 2-letter uppercase
       ssn-mask    → 000-00-0000
       npi-mask    → 10 digits

     For address autocomplete, set on the address input:
       class="addr-input" data-suggestions-id="addr_dropdown"
       data-city-id="..."  data-state-id="..."  data-zip-id="..."
     and add <div id="addr_dropdown" class="addr-suggestions"></div> next to it.
--}}

<style>
    .addr-suggestions { position: absolute; background: white; border: 1px solid #cbd5e1; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 50; max-height: 240px; overflow-y: auto; min-width: 280px; }
    .addr-item { padding: 0.5rem 0.75rem; font-size: 0.8rem; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
    .addr-item:hover { background: #eef2ff; color: #4338ca; }
</style>

<script src="https://cdn.jsdelivr.net/npm/imask@7/dist/imask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const init = (sel, opts) => document.querySelectorAll(sel).forEach(el => IMask(el, opts));
    init('.phone-mask', { mask: '(000) 000-0000' });
    init('.zip-mask',   { mask: '00000[-0000]' });
    init('.state-mask', { mask: 'aa', prepare: s => s.toUpperCase() });
    init('.ssn-mask',   { mask: '000-00-0000' });
    init('.npi-mask',   { mask: '0000000000' });

    const STATE_ABBR = {
        'alabama':'AL','alaska':'AK','arizona':'AZ','arkansas':'AR','california':'CA','colorado':'CO',
        'connecticut':'CT','delaware':'DE','florida':'FL','georgia':'GA','hawaii':'HI','idaho':'ID',
        'illinois':'IL','indiana':'IN','iowa':'IA','kansas':'KS','kentucky':'KY','louisiana':'LA',
        'maine':'ME','maryland':'MD','massachusetts':'MA','michigan':'MI','minnesota':'MN','mississippi':'MS',
        'missouri':'MO','montana':'MT','nebraska':'NE','nevada':'NV','new hampshire':'NH','new jersey':'NJ',
        'new mexico':'NM','new york':'NY','north carolina':'NC','north dakota':'ND','ohio':'OH','oklahoma':'OK',
        'oregon':'OR','pennsylvania':'PA','rhode island':'RI','south carolina':'SC','south dakota':'SD',
        'tennessee':'TN','texas':'TX','utah':'UT','vermont':'VT','virginia':'VA','washington':'WA',
        'west virginia':'WV','wisconsin':'WI','wyoming':'WY','district of columbia':'DC','puerto rico':'PR'
    };

    document.querySelectorAll('.addr-input').forEach(input => {
        const dropId  = input.dataset.suggestionsId;
        const drop    = document.getElementById(dropId);
        const cityEl  = document.getElementById(input.dataset.cityId);
        const stateEl = document.getElementById(input.dataset.stateId);
        const zipEl   = document.getElementById(input.dataset.zipId);
        if (!drop) return;

        let timer = null;
        input.setAttribute('autocomplete', 'off');

        input.addEventListener('input', () => {
            const q = input.value.trim();
            clearTimeout(timer);
            if (q.length < 4) { drop.style.display = 'none'; return; }
            timer = setTimeout(async () => {
                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=us&limit=5&q=${encodeURIComponent(q)}`;
                    const res = await fetch(url, { headers: { 'Accept-Language': 'en' }});
                    const items = await res.json();
                    drop.innerHTML = '';
                    items.forEach(it => {
                        const row = document.createElement('div');
                        row.className = 'addr-item';
                        row.textContent = it.display_name;
                        row.addEventListener('click', () => {
                            const a = it.address || {};
                            const street = [a.house_number, a.road].filter(Boolean).join(' ');
                            input.value = street || a.road || it.display_name.split(',')[0];
                            if (cityEl)  cityEl.value  = a.city || a.town || a.village || a.hamlet || a.suburb || '';
                            if (stateEl) stateEl.value = STATE_ABBR[(a.state || '').toLowerCase()] || (a.state || '').slice(0,2).toUpperCase();
                            if (zipEl)   zipEl.value   = a.postcode || '';
                            drop.style.display = 'none';
                        });
                        drop.appendChild(row);
                    });
                    drop.style.display = items.length ? 'block' : 'none';
                } catch (e) {
                    drop.style.display = 'none';
                }
            }, 450);
        });

        document.addEventListener('click', (e) => {
            if (e.target !== input) drop.style.display = 'none';
        });
    });
});
</script>
