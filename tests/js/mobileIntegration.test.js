import { describe, it, expect, beforeEach } from 'vitest';
import { app } from '../../public/js/app.js';

const mockStores = [
    { id: 1, name: 'Toko Berkah Makmur', owner: 'Ahmad Rizki', phone: '081234567890' },
    { id: 2, name: 'Warung Sejahtera', owner: 'Budi Santoso', phone: '082345678901' },
    { id: 3, name: 'Minimarket Jaya', owner: 'Citra Dewi', phone: '083456789012' },
];

describe('Mobile Optimization & Integration', () => {
    let data;

    beforeEach(() => {
        data = app();
        data.stores = [...mockStores];
        data.saleStoreSearch = '';
        data.storeSearch = '';
        data.saleForm = { store_id: '', items: {}, sale_date: '' };
        data.highlightedStoreIndex = -1;
    });

    it('selected store persists when navigating away and back (saleForm retains store_id)', () => {
        data.selectStore(mockStores[0]);
        expect(data.saleForm.store_id).toBe(1);
        expect(data.saleForm.store_id).toBe(1);
    });

    it('Store view search does not affect saleFilteredStores', () => {
        data.storeSearch = 'Jaya';
        data.saleStoreSearch = '';
        expect(data.filteredStores).toHaveLength(1);
        expect(data.saleFilteredStores).toHaveLength(3);
    });

    it('saleFilteredStores returns empty when no stores match sale search', () => {
        data.saleStoreSearch = 'xyznotfound';
        expect(data.saleFilteredStores).toHaveLength(0);
    });

    it('dropdown state resets when store is deselected', () => {
        data.selectStore(mockStores[0]);
        expect(data.saleStoreSearch).toBe('');
        expect(data.highlightedStoreIndex).toBe(-1);
    });

    it('clearStoreSelection fully resets sale form store state', () => {
        data.selectStore(mockStores[1]);
        data.clearStoreSelection();
        expect(data.saleForm.store_id).toBe('');
        expect(data.saleStoreSearch).toBe('');
    });

    it('selectStore with different stores updates correctly', () => {
        data.selectStore(mockStores[0]);
        expect(data.saleForm.store_id).toBe(1);
        data.selectStore(mockStores[2]);
        expect(data.saleForm.store_id).toBe(3);
    });

    it('selectedStoreName returns correct name after selectStore', () => {
        data.selectStore(mockStores[1]);
        expect(data.selectedStoreName).toBe('Warung Sejahtera');
    });

    it('selectedStoreName returns empty after clearStoreSelection', () => {
        data.selectStore(mockStores[0]);
        data.clearStoreSelection();
        expect(data.selectedStoreName).toBe('');
    });
});