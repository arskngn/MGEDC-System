/**
 * Address autocomplete: Google Places (if API key) or Photon (OSM) fallback.
 * Philippines-first bias; worldwide search still works.
 */

const PH_CENTER = { lat: 14.5995, lon: 120.9842 };
const DEBOUNCE_MS = 380;

function debounce(fn, ms) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), ms);
    };
}

function formatPhotonFeature(f) {
    const p = f.properties || {};
    const parts = [
        [p.housenumber, p.street].filter(Boolean).join(' ').trim(),
        p.name,
        p.district,
        p.city || p.town || p.village,
        p.state,
        p.postcode,
        p.country,
    ].filter((x) => x && String(x).trim() !== '');

    const uniq = [...new Set(parts)];
    return uniq.join(', ') || p.name || 'Address';
}

function loadGoogleMapsPlaces(apiKey) {
    return new Promise((resolve, reject) => {
        if (window.google?.maps?.places) {
            resolve();
            return;
        }
        const id = 'google-maps-places-sdk';
        if (document.getElementById(id)) {
            const check = () =>
                window.google?.maps?.places ? resolve() : setTimeout(check, 50);
            check();
            return;
        }
        const s = document.createElement('script');
        s.id = id;
        s.async = true;
        s.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places&v=weekly`;
        s.onload = () => resolve();
        s.onerror = () => reject(new Error('Google Maps failed to load'));
        document.head.appendChild(s);
    });
}

/**
 * @param {HTMLInputElement} input
 * @param {{ googleApiKey?: string }} options
 */
export function initAddressAutocomplete(input, options = {}) {
    const googleApiKey = options.googleApiKey || input.dataset.googleKey || '';
    const wrapper = input.closest('[data-address-autocomplete-wrapper]');
    const list =
        wrapper?.querySelector('[data-address-suggestions]') || createList(input, wrapper);

    if (googleApiKey) {
        initGoogle(input, googleApiKey, list);
    } else {
        initPhoton(input, list);
    }
}

function createList(input, wrapper) {
    const ul = document.createElement('ul');
    ul.setAttribute('data-address-suggestions', '');
    ul.className =
        'absolute z-[300] left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg text-sm hidden';
    ul.setAttribute('role', 'listbox');
    (wrapper || input.parentElement).appendChild(ul);
    return ul;
}

function initGoogle(input, apiKey, list) {
    let autocomplete;
    let ready = false;

    const setup = async () => {
        if (ready) return;
        try {
            await loadGoogleMapsPlaces(apiKey);
            const bounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(5.0, 115.0),
                new google.maps.LatLng(22.0, 128.0)
            );
            autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ['formatted_address', 'geometry', 'name'],
                types: ['geocode'],
                bounds,
                strictBounds: false,
            });
            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (place.formatted_address) {
                    input.value = place.formatted_address;
                } else if (place.name) {
                    input.value = place.name;
                }
                list.classList.add('hidden');
                list.innerHTML = '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
            ready = true;
            list.classList.add('hidden');
        } catch {
            initPhoton(input, list);
        }
    };

    input.addEventListener(
        'focus',
        () => {
            setup();
        },
        { once: true }
    );
}

async function fetchPhotonSuggestions(query) {
    const q = query.trim();
    if (q.length < 3) return [];

    const url = new URL('https://photon.komoot.io/api/');
    url.searchParams.set('q', q);
    url.searchParams.set('limit', '12');
    url.searchParams.set('lang', 'en');
    url.searchParams.set('lat', String(PH_CENTER.lat));
    url.searchParams.set('lon', String(PH_CENTER.lon));

    const res = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
    });
    if (!res.ok) return [];
    const data = await res.json();
    return Array.isArray(data.features) ? data.features : [];
}

function initPhoton(input, list) {
    let active = -1;
    let items = [];

    const close = () => {
        list.classList.add('hidden');
        list.innerHTML = '';
        active = -1;
        items = [];
    };

    const render = (features) => {
        list.innerHTML = '';
        items = features;
        active = -1;
        if (!features.length) {
            close();
            return;
        }
        features.forEach((f, i) => {
            const label = formatPhotonFeature(f);
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.className =
                'cursor-pointer px-3 py-2 hover:bg-[#5542ff]/10 border-b border-gray-100 last:border-0 text-gray-800';
            li.textContent = label;
            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
                input.value = label;
                close();
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
            list.appendChild(li);
        });
        list.classList.remove('hidden');
    };

    const runSearch = debounce(async () => {
        const q = input.value;
        if (q.trim().length < 3) {
            close();
            return;
        }
        try {
            const features = await fetchPhotonSuggestions(q);
            render(features);
        } catch {
            close();
        }
    }, DEBOUNCE_MS);

    input.addEventListener('input', runSearch);
    input.addEventListener('keydown', (e) => {
        if (!list.classList.contains('hidden') && items.length) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                active = Math.min(active + 1, items.length - 1);
                highlight(list, active);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                active = Math.max(active - 1, 0);
                highlight(list, active);
            } else if (e.key === 'Enter' && active >= 0) {
                e.preventDefault();
                const label = formatPhotonFeature(items[active]);
                input.value = label;
                close();
                input.dispatchEvent(new Event('change', { bubbles: true }));
            } else if (e.key === 'Escape') {
                close();
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            close();
        }
    });
}

function highlight(list, index) {
    const lis = list.querySelectorAll('li');
    lis.forEach((li, i) => {
        li.classList.toggle('bg-[#5542ff]/15', i === index);
    });
}

/**
 * Scan DOM for inputs with [data-address-autocomplete]
 */
export function bootAddressAutocomplete() {
    document.querySelectorAll('input[data-address-autocomplete]').forEach((el) => {
        if (el.dataset.addressAutocompleteInit === '1') return;
        el.dataset.addressAutocompleteInit = '1';
        const key = el.dataset.googleKey || '';
        initAddressAutocomplete(el, { googleApiKey: key || undefined });
    });
}
