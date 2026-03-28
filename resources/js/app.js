import './bootstrap';

import Alpine from 'alpinejs';
import { purchaseDateRange } from './purchase-date-range';
import { bootAddressAutocomplete } from './address-autocomplete';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('purchaseDateRange', purchaseDateRange);
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    bootAddressAutocomplete();
});
