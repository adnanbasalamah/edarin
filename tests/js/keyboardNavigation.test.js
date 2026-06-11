import { describe, it, expect, beforeEach } from 'vitest';
import { app } from '../../public/js/app.js';

const mockStores = [
    { id: 1, name: 'Toko Berkah Makmur', owner: 'Ahmad Rizki', phone: '081234567890' },
    { id: 2, name: 'Warung Sejahtera', owner: 'Budi Santoso', phone: '082345678901' },
    { id: 3, name: 'Minimarket Jaya', owner: 'Citra Dewi', phone: '083456789012' },
];

describe('Keyboard Navigation', () => {
    let data;

    beforeEach(() => {
        data = app();
        data.stores = [...mockStores];
        data.saleStoreSearch = '';
        data.saleForm = { store_id: '', items: {}, sale_date: '' };
        data.highlightedStoreIndex = -1;
    });

    it('highlightedStoreIndex initializes to -1', () => {
        const fresh = app();
        expect(fresh.highlightedStoreIndex).toBe(-1);
    });

    describe('navigateStoresDown', () => {
        it('sets highlightedStoreIndex to 0 when starting from -1', () => {
            data.saleStoreSearch = '';
            data.navigateStoresDown();
            expect(data.highlightedStoreIndex).toBe(0);
        });

        it('increments highlightedStoreIndex', () => {
            data.highlightedStoreIndex = 0;
            data.navigateStoresDown();
            expect(data.highlightedStoreIndex).toBe(1);
        });

        it('wraps around to 0 when at the end', () => {
            data.highlightedStoreIndex = 2;
            data.navigateStoresDown();
            expect(data.highlightedStoreIndex).toBe(0);
        });
    });

    describe('navigateStoresUp', () => {
        it('sets highlightedStoreIndex to last index when starting from -1', () => {
            data.saleStoreSearch = '';
            data.navigateStoresUp();
            expect(data.highlightedStoreIndex).toBe(2);
        });

        it('decrements highlightedStoreIndex', () => {
            data.highlightedStoreIndex = 2;
            data.navigateStoresUp();
            expect(data.highlightedStoreIndex).toBe(1);
        });

        it('wraps around to last index when at 0', () => {
            data.highlightedStoreIndex = 0;
            data.navigateStoresUp();
            expect(data.highlightedStoreIndex).toBe(2);
        });
    });

    describe('selectHighlightedStore', () => {
        it('selects the highlighted store from filtered results', () => {
            data.highlightedStoreIndex = 1;
            data.selectHighlightedStore();
            expect(data.saleForm.store_id).toBe(2);
            expect(data.saleStoreSearch).toBe('');
        });

        it('does nothing when highlightedStoreIndex is -1', () => {
            data.highlightedStoreIndex = -1;
            data.selectHighlightedStore();
            expect(data.saleForm.store_id).toBe('');
        });
    });

    describe('closeStoreDropdown', () => {
        it('resets highlightedStoreIndex when closing dropdown', () => {
            data.highlightedStoreIndex = 3;
            data.closeStoreDropdown();
            expect(data.highlightedStoreIndex).toBe(-1);
        });
    });
});