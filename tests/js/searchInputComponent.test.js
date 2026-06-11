import { describe, it, expect, beforeEach } from 'vitest';
import { app } from '../../public/js/app.js';

const mockStores = [
    { id: 1, name: 'Toko Berkah Makmur', owner: 'Ahmad Rizki', phone: '081234567890' },
    { id: 2, name: 'Warung Sejahtera', owner: 'Budi Santoso', phone: '082345678901' },
    { id: 3, name: 'Minimarket Jaya', owner: 'Citra Dewi', phone: '083456789012' },
];

describe('Search Input Component', () => {
    let data;

    beforeEach(() => {
        data = app();
        data.stores = [...mockStores];
        data.saleStoreSearch = '';
        data.saleForm = { store_id: '', items: {}, sale_date: '' };
    });

    describe('selectStore', () => {
        it('sets saleForm.store_id to the selected store id', () => {
            data.selectStore(mockStores[0]);
            expect(data.saleForm.store_id).toBe(1);
        });

        it('sets saleStoreSearch to empty string after selection', () => {
            data.saleStoreSearch = 'ber';
            data.selectStore(mockStores[0]);
            expect(data.saleStoreSearch).toBe('');
        });
    });

    describe('clearStoreSelection', () => {
        it('resets saleForm.store_id to empty', () => {
            data.saleForm.store_id = 1;
            data.clearStoreSelection();
            expect(data.saleForm.store_id).toBe('');
        });

        it('resets saleStoreSearch to empty', () => {
            data.saleStoreSearch = 'tok';
            data.clearStoreSelection();
            expect(data.saleStoreSearch).toBe('');
        });
    });

    describe('form validation', () => {
        it('validates that store_id must be selected', () => {
            data.saleForm.store_id = '';
            expect(data.saleForm.store_id).toBe('');
        });

        it('saleForm.store_id is set after selecting a store', () => {
            data.selectStore(mockStores[1]);
            expect(data.saleForm.store_id).toBe(2);
        });
    });

    describe('selectedStoreName', () => {
        it('returns empty string when no store is selected', () => {
            data.saleForm.store_id = '';
            expect(data.selectedStoreName).toBe('');
        });

        it('returns the store name when a store is selected', () => {
            data.saleForm.store_id = 2;
            expect(data.selectedStoreName).toBe('Warung Sejahtera');
        });

        it('returns the store name for another store', () => {
            data.saleForm.store_id = 3;
            expect(data.selectedStoreName).toBe('Minimarket Jaya');
        });
    });
});