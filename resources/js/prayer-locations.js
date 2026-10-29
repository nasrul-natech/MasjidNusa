import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.css';

const form = document.getElementById('prayer-location-form');

if (form) {
    const provincesUrl = form.dataset.provincesUrl;
    const citiesUrl = form.dataset.citiesUrl;
    const provinceHidden = document.getElementById('province-hidden');
    const cityHidden = document.getElementById('city-hidden');
    const latHidden = document.getElementById('lat-hidden');
    const lngHidden = document.getElementById('lng-hidden');
    const latDisplay = document.getElementById('lat-display');
    const lngDisplay = document.getElementById('lng-display');
    const submitBtn = document.getElementById('submit-btn');
    const status = document.getElementById('location-status');

    const selectOptions = {
        maxOptions: null,
        allowEmptyOption: true,
        create: false,
        dropdownParent: 'body',
    };

    const provinceSelect = new TomSelect('#province-select', {
        ...selectOptions,
        placeholder: 'Cari provinsi',
    });
    const citySelect = new TomSelect('#city-select', {
        ...selectOptions,
        placeholder: 'Pilih provinsi dulu',
    });

    provinceSelect.disable();
    citySelect.disable();

    function setStatus(message) {
        if (status) status.textContent = message || '';
    }

    function clearCity() {
        cityHidden.value = '';
        latHidden.value = '';
        lngHidden.value = '';
        latDisplay.value = '';
        lngDisplay.value = '';
        submitBtn.disabled = true;
    }

    function applyCity(raw) {
        if (!raw) {
            clearCity();
            return;
        }

        try {
            const city = JSON.parse(raw);
            cityHidden.value = city.name;
            latHidden.value = city.lat;
            lngHidden.value = city.lng;
            latDisplay.value = city.lat;
            lngDisplay.value = city.lng;
            submitBtn.disabled = false;
            setStatus('');
        } catch (e) {
            clearCity();
        }
    }

    fetch(provincesUrl)
        .then((response) => response.json())
        .then((json) => {
            (json.data || []).forEach((province) => {
                provinceSelect.addOption({ value: province, text: province });
            });
            provinceSelect.refreshOptions(false);
            provinceSelect.enable();
            setStatus(`${(json.data || []).length} provinsi`);
        })
        .catch(() => {
            setStatus('Provinsi gagal dimuat. Muat ulang halaman.');
        });

    provinceSelect.on('change', (province) => {
        provinceHidden.value = province || '';
        clearCity();
        citySelect.clear(true);
        citySelect.clearOptions();
        citySelect.disable();

        if (!province) {
            setStatus('');
            return;
        }

        setStatus('Memuat kabupaten/kota...');

        fetch(`${citiesUrl}?province=${encodeURIComponent(province)}`)
            .then((response) => response.json())
            .then((json) => {
                const cities = json.data || [];
                cities.forEach((city) => {
                    citySelect.addOption({
                        value: JSON.stringify(city),
                        text: city.name,
                    });
                });
                citySelect.refreshOptions(false);
                citySelect.enable();
                setStatus(`${cities.length} kabupaten/kota. Ketik untuk mencari.`);
            })
            .catch(() => {
                setStatus('Kabupaten/kota gagal dimuat.');
            });
    });

    citySelect.on('change', (value) => applyCity(value));
}
