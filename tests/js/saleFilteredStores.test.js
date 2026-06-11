import { describe, it, expect, beforeEach } from 'vitest';
import { app } from '../../public/js/app.js';

const mockStores = [
    { id: 1, name: 'Toko Berkah Makmur', owner: 'Ahmad Rizki', phone: '081234567890' },
    { id: 2, name: 'Warung Sejahtera', owner: 'Budi Santoso', phone: '082345678901' },
    { id: 3, name: 'Minimarket Jaya', owner: 'Citra Dewi', phone: '083456789012' },
    { id: 4, name: 'Toko Harapan Baru', owner: 'Ahmad Hidayat', phone: '084567890123' },
    { id: 5, name: 'Kios Melati', owner: 'Rizki Berlian', phone: '085678901234' },
];

describe('saleFilteredStores', () => {
    let data;

    beforeEach(() => {
        data = app();
        data.stores = [...mockStores];
        data.saleStoreSearch = '';
        data.storeSearch = '';
    });

    it('returns all stores when saleStoreSearch is empty', () => {
        data.saleStoreSearch = '';
        expect(data.saleFilteredStores).toHaveLength(5);
    });

    it('filters stores by name (case-insensitive partial match)', () => {
        data.saleStoreSearch = 'berkah';
        const result = data.saleFilteredStores;
        expect(result).toHaveLength(1);
        expect(result[0].name).toBe('Toko Berkah Makmur');

        data.saleStoreSearch = 'BERKAH';
        expect(data.saleFilteredStores).toHaveLength(1);
        expect(data.saleFilteredStores[0].name).toBe('Toko Berkah Makmur');
    });

    it('filters stores by owner name (case-insensitive partial match)', () => {
        data.saleStoreSearch = 'ahmad';
        const result = data.saleFilteredStores;
        expect(result).toHaveLength(2);
        expect(result.map(s => s.name)).toContain('Toko Berkah Makmur');
        expect(result.map(s => s.name)).toContain('Toko Harapan Baru');

        data.saleStoreSearch = 'AHMAD';
        expect(data.saleFilteredStores).toHaveLength(2);
    });

    it('does not filter by phone number', () => {
        data.saleStoreSearch = '08123';
        const result = data.saleFilteredStores;
        expect(result).toHaveLength(0);
    });

    it('is independent from filteredStores (Store view)', () => {
        data.saleStoreSearch = '';
        data.storeSearch = 'Jaya';

        const saleFiltered = data.saleFilteredStores;
        expect(saleFiltered).toHaveLength(5);

        const storeFiltered = data.filteredStores;
        expect(storeFiltered).toHaveLength(1);
        expect(storeFiltered[0].name).toBe('Minimarket Jaya');
    });

    it('returns stores matching either name or owner', () => {
        data.saleStoreSearch = 'ber';
        const result = data.saleFilteredStores;
        expect(result).toHaveLength(2);
        expect(result.map(s => s.name)).toContain('Toko Berkah Makmur');
        expect(result.map(s => s.name)).toContain('Kios Melati');
    });
});