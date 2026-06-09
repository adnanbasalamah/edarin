const API_BASE = window.location.origin + '/api';

const DB_NAME = 'edarin_offline';
const DB_VERSION = 1;

function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pending_sales')) {
                const store = db.createObjectStore('pending_sales', { keyPath: 'client_id' });
                store.createIndex('status', 'status', { unique: false });
                store.createIndex('created_at', 'created_at', { unique: false });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function app() {
    return {
        loading: true,
        isLoggedIn: false,
        isOnline: navigator.onLine,
        currentRoute: 'login',
        mobileMenuOpen: false,
        user: null,
        token: localStorage.getItem('edarin_token') || '',
        pageTitle: '',
        loginError: '',
        loginLoading: false,
        form: { username: '', password: '' },
        stats: { products: 0, stores: 0, distributors: 0, todaySales: 0, totalSalesIdr: 0, returnRate: 0, returnBadge: '' },
        statsLoading: true,
        products: [],
        stores: [],
        distributors: [],
        showProductForm: false,
        showStoreForm: false,
        showDistributorForm: false,
        productForm: {},
        storeForm: {},
        distributorForm: {},
        createdPassword: '',
        locationError: '',
        storeSearch: '',
        saleForm: { store_id: '', items: {}, sale_date: '' },
        saleFormError: '',
        saleFormSuccess: '',
        saleLoading: false,
        offlineSales: [],
        recentSales: [],
        filterDateFrom: '',
        filterDateTo: '',
        topProducts: [],
        topStores: [],
        returnStores: [],
        monthlyTrend: [],
        reportData: [],
        reportDateFrom: '',
        reportDateTo: '',
        reportLoading: false,
        reportPeriod: 'today',
        reportStats: { total_revenue: 0, count: 0, avg_per_tx: 0, percent_change: 0 },
        chartData: [],
        reportTransactions: [],
        auditLogs: [],
        syncQueue: [],
        isSyncing: false,
        pendingCount: 0,
        lastSyncResult: '',

        get navItems() {
            if (!this.user) return [];
            if (this.user.role === 'admin') {
                return [
                    { id: 'dashboard', path: 'dashboard', icon: '📊', label: 'Dashboard' },
                    { id: 'products', path: 'products', icon: '📦', label: 'Product' },
                    { id: 'stores', path: 'stores', icon: '🏪', label: 'Store' },
                    { id: 'distributors', path: 'distributors', icon: '👥', label: 'Sales Person' },
                    { id: 'sales', path: 'sales', icon: '📝', label: 'Input Penjualan' },
                    { id: 'reports', path: 'reports', icon: '📈', label: 'Report' },
                    { id: 'audit', path: 'audit', icon: '📋', label: 'Audit Log' },
                ];
            }
            return [
                { id: 'sales', path: 'sales', icon: '📝', label: 'Input Penjualan' },
                { id: 'stores', path: 'stores', icon: '🏪', label: 'Store' },
                { id: 'reports', path: 'reports', icon: '📈', label: 'Riwayat' },
            ];
        },

        get mobileNavItems() {
            return this.navItems.slice(0, 4);
        },

        get filteredStores() {
            if (!this.storeSearch) return this.stores;
            const q = this.storeSearch.toLowerCase();
            return this.stores.filter(s =>
                (s.name || '').toLowerCase().includes(q) ||
                (s.owner || '').toLowerCase().includes(q) ||
                (s.phone || '').toLowerCase().includes(q)
            );
        },

        get maxMonthlyTotal() {
            if (!this.monthlyTrend.length) return 1;
            return Math.max(...this.monthlyTrend.map(m => m.total), 1);
        },

        async init() {
            if (this.token) {
                const payload = this.parseJwt(this.token);
                if (payload && payload.exp * 1000 > Date.now()) {
                    this.user = { sub: payload.sub, username: payload.username, role: payload.role };
                    this.isLoggedIn = true;
                } else {
                    localStorage.removeItem('edarin_token');
                    this.token = '';
                }
            }

            this.loadQueue();

            window.addEventListener('online', async () => {
                this.isOnline = true;
                if (this.isLoggedIn) await this.processQueue();
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            this.handleRoute();
            window.addEventListener('hashchange', () => this.handleRoute());
            this.loading = false;
        },

        handleRoute() {
            const hash = window.location.hash.slice(1) || 'login';
            this.currentRoute = hash;

            if (hash === 'login') {
                this.pageTitle = 'Masuk';
                if (this.isLoggedIn) {
                    const target = this.user?.role === 'admin' ? 'dashboard' : 'sales';
                    window.location.hash = target;
                    this.currentRoute = target;
                }
                return;
            }

            if (!this.isLoggedIn) {
                window.location.hash = 'login';
                this.currentRoute = 'login';
                return;
            }

            const titles = {
                dashboard: 'Dashboard',
                products: 'Product',
                stores: 'Store',
                distributors: 'Distributor',
                sales: 'Input Penjualan',
                reports: 'Report',
            };
            this.pageTitle = titles[hash] || 'Edarin';

            if (hash === 'dashboard') {
                this.filterDateFrom = new Date().toISOString().slice(0, 10);
                this.filterDateTo = new Date().toISOString().slice(0, 10);
                this.loadStats();
            }
            if (hash === 'products') this.loadProducts();
            if (hash === 'stores') this.loadStores();
            if (hash === 'distributors') this.loadDistributors();
            if (hash === 'sales') this.loadSalesFormData();
            if (hash === 'reports') {
                this.reportPeriod = 'today';
                this.loadReports();
            }
            if (hash === 'audit') this.loadAudit();
        },

        navigate(path) {
            window.location.hash = path;
        },

        async api(path, options = {}) {
            const headers = { 'Content-Type': 'application/json' };
            if (this.token) headers['Authorization'] = 'Bearer ' + this.token;

            try {
                const res = await fetch(API_BASE + path, { ...options, headers });
                const data = await res.json();

                if (res.status === 401 && this.isLoggedIn) {
                    this.logout();
                    return null;
                }

                return { status: res.status, data };
            } catch (err) {
                this.queueRequest(path, options);
                return null;
            }
        },

        loadQueue() {
            try {
                this.syncQueue = JSON.parse(localStorage.getItem('edarin_queue') || '[]');
                this.pendingCount = this.syncQueue.length;
            } catch { this.syncQueue = []; this.pendingCount = 0; }
        },

        saveQueue() {
            localStorage.setItem('edarin_queue', JSON.stringify(this.syncQueue));
            this.pendingCount = this.syncQueue.length;
        },

        queueRequest(path, options) {
            this.syncQueue.push({ path, options, timestamp: Date.now() });
            this.saveQueue();
            if (this.isOnline) this.processQueue();
        },

        async processQueue() {
            if (this.isSyncing || this.syncQueue.length === 0) return;
            this.isSyncing = true;

            const remaining = [];
            for (const item of this.syncQueue) {
                try {
                    const headers = { 'Content-Type': 'application/json' };
                    if (this.token) headers['Authorization'] = 'Bearer ' + this.token;

                    const res = await fetch(API_BASE + item.path, { ...item.options, headers });
                    if (!res.ok && res.status !== 409) {
                        remaining.push(item);
                    }
                } catch {
                    remaining.push(item);
                }
            }

            this.syncQueue = remaining;
            this.saveQueue();
            this.isSyncing = false;

            if (this.syncQueue.length === 0 && this.pendingCount > 0) {
                this.lastSyncResult = 'Semua data berhasil disinkronkan.';
                setTimeout(() => { this.lastSyncResult = ''; }, 4000);
            } else if (this.syncQueue.length > 0) {
                this.lastSyncResult = this.syncQueue.length + ' data gagal disinkronkan.';
            }
        },

        async saveOfflineSale(saleData) {
            try {
                const db = await openDB();
                const tx = db.transaction('pending_sales', 'readwrite');
                const store = tx.objectStore('pending_sales');
                const clientId = 'sale_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
                store.put({
                    client_id: clientId,
                    ...saleData,
                    status: 'pending',
                    created_at: new Date().toISOString(),
                });
                await new Promise((resolve, reject) => {
                    tx.oncomplete = resolve;
                    tx.onerror = () => reject(tx.error);
                });
                return clientId;
            } catch (e) {
                console.error('IndexedDB error:', e);
                return null;
            }
        },

        async getOfflineSales(status = 'pending') {
            try {
                const db = await openDB();
                const tx = db.transaction('pending_sales', 'readonly');
                const store = tx.objectStore('pending_sales');
                const index = store.index('status');
                const items = await new Promise((resolve, reject) => {
                    const req = index.getAll(status);
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => reject(req.error);
                });
                return items;
            } catch { return []; }
        },

        async syncOfflineSales() {
            const sales = await this.getOfflineSales('pending');
            if (sales.length === 0) return;

            this.isSyncing = true;
            const failed = [];

            for (const sale of sales) {
                try {
                    const headers = { 'Content-Type': 'application/json' };
                    if (this.token) headers['Authorization'] = 'Bearer ' + this.token;

                    const res = await fetch(API_BASE + '/sales', {
                        method: 'POST',
                        headers,
                        body: JSON.stringify(sale),
                    });

                    if (res.ok || res.status === 409) {
                        const db = await openDB();
                        const tx = db.transaction('pending_sales', 'readwrite');
                        tx.objectStore('pending_sales').delete(sale.client_id);
                        await new Promise((resolve, reject) => {
                            tx.oncomplete = resolve;
                            tx.onerror = () => reject(tx.error);
                        });
                    } else {
                        failed.push(sale);
                    }
                } catch {
                    failed.push(sale);
                }
            }

            this.isSyncing = false;

            if (failed.length === 0) {
                this.lastSyncResult = 'Semua data berhasil disinkronkan.';
                setTimeout(() => { this.lastSyncResult = ''; }, 4000);
            }
        },

        async loadSalesFormData() {
            this.saleForm = { store_id: '', items: {}, sale_date: new Date().toISOString().slice(0, 10) };
            this.saleFormError = '';
            this.saleFormSuccess = '';
            await Promise.all([this.loadProducts(), this.loadStores()]);
            this.offlineSales = await this.getOfflineSales();
        },

        async loadReports() {
            this.reportLoading = true;
            const result = await this.api('/reports/stats?period=' + this.reportPeriod);
            if (result) {
                this.reportStats = result.data;
                this.chartData = result.data.chart_data || [];
                this.reportTransactions = result.data.transactions || [];
            }
            this.reportLoading = false;
        },

        get maxChartTotal() {
            if (!this.chartData.length) return 1;
            return Math.max(...this.chartData.map(c => c.total), 1);
        },

        async downloadReport() {
            let url = '/reports/download';
            const params = [];
            if (this.reportDateFrom) params.push('date_from=' + this.reportDateFrom);
            if (this.reportDateTo) params.push('date_to=' + this.reportDateTo);
            if (params.length) url += '?' + params.join('&');

            try {
                const res = await fetch(API_BASE + url, {
                    headers: { 'Authorization': 'Bearer ' + this.token },
                });
                const blob = await res.blob();
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'laporan_penjualan.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
            } catch {}
        },

        async loadAudit() {
            const result = await this.api('/audit');
            if (result) this.auditLogs = result.data || [];
        },

        async filterSales() {
            let url = '/sales';
            const params = [];
            if (this.filterDateFrom) params.push('date_from=' + this.filterDateFrom);
            if (this.filterDateTo) params.push('date_to=' + this.filterDateTo);
            if (params.length) url += '?' + params.join('&');
            const result = await this.api(url);
            if (result) this.recentSales = result.data || [];
        },

        async submitSale() {
            this.saleLoading = true;
            this.saleFormError = '';
            this.saleFormSuccess = '';

            if (!this.saleForm.store_id) {
                this.saleFormError = 'Silakan pilih toko.';
                this.saleLoading = false;
                return;
            }

            const entries = Object.entries(this.saleForm.items)
                .filter(([_, v]) => parseInt(v.qty || 0) > 0 || parseInt(v.return_qty || 0) > 0);

            if (entries.length === 0) {
                this.saleFormError = 'Silakan isi minimal satu product.';
                this.saleLoading = false;
                return;
            }

            let allSuccess = true;
            for (const [productId, item] of entries) {
                const saleData = {
                    client_id: 'sale_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
                    store_id: this.saleForm.store_id,
                    product_id: parseInt(productId),
                    quantity: parseInt(item.qty || 0),
                    return_qty: parseInt(item.return_qty || 0),
                    sale_date: this.saleForm.sale_date || new Date().toISOString().slice(0, 10),
                };

                if (this.isOnline) {
                    const result = await this.api('/sales', {
                        method: 'POST',
                        body: JSON.stringify(saleData),
                    });
                    if (!result || (result.status !== 201 && result.status !== 409)) {
                        allSuccess = false;
                        await this.saveOfflineSale({ ...saleData, sync_status: 'failed' });
                    }
                } else {
                    await this.saveOfflineSale({ ...saleData, sync_status: 'pending' });
                    this.queueRequest('/sales', {
                        method: 'POST',
                        body: JSON.stringify(saleData),
                    });
                }
            }

            this.saleLoading = false;
            this.saleForm.items = {};

            if (allSuccess) {
                if (this.isOnline) {
                    this.saleFormSuccess = 'Penjualan berhasil disimpan.';
                } else {
                    this.saleFormSuccess = 'Data penjualan akan dikirim otomatis saat koneksi tersedia.';
                }
            } else {
                this.saleFormError = 'Beberapa data gagal disimpan.';
            }

            setTimeout(() => { this.saleFormSuccess = ''; }, 5000);
            this.offlineSales = await this.getOfflineSales();
        },

        parseJwt(token) {
            try {
                return JSON.parse(atob(token.split('.')[1]));
            } catch {
                return null;
            }
        },

        async doLogin() {
            this.loginLoading = true;
            this.loginError = '';
            const result = await this.api('/auth/login', {
                method: 'POST',
                body: JSON.stringify(this.form),
            });

            if (!result) {
                this.loginError = 'Koneksi terputus. Silakan coba lagi.';
                this.loginLoading = false;
                return;
            }

            if (result.status === 200) {
                this.token = result.data.token;
                this.user = result.data.user;
                this.isLoggedIn = true;
                localStorage.setItem('edarin_token', this.token);
                const target = this.user.role === 'admin' ? 'dashboard' : 'sales';
                window.location.hash = target;
                this.processQueue();
                this.syncOfflineSales();
            } else {
                this.loginError = 'Username atau kata sandi salah.';
            }
            this.loginLoading = false;
        },

        logout() {
            this.token = '';
            this.user = null;
            this.isLoggedIn = false;
            localStorage.removeItem('edarin_token');
            window.location.hash = 'login';
        },

        async loadStats() {
            this.statsLoading = true;
            const [p, s, d, summary, recentSales, dashboard] = await Promise.all([
                this.api('/products'),
                this.api('/stores'),
                this.api('/distributors'),
                this.api('/sales/summary'),
                this.api('/sales?date_from=' + new Date().toISOString().slice(0, 10)),
                this.api('/reports/dashboard'),
            ]);
            this.stats.products = p?.data?.length ?? 0;
            this.stats.stores = s?.data?.length ?? 0;
            this.stats.distributors = d?.data?.length ?? 0;
            this.stats.totalSalesIdr = summary?.data?.total_sales_idr ?? 0;
            this.stats.todaySales = summary?.data?.today_sales ?? 0;
            this.stats.returnRate = summary?.data?.return_rate ?? 0;
            this.stats.returnBadge = null;
            if (summary?.data?.return_rate > 5) {
                this.stats.returnBadge = '⚠️ ' + summary.data.return_rate + '%';
            }
            this.recentSales = recentSales?.data ?? [];
            this.topProducts = dashboard?.data?.top_products ?? [];
            this.topStores = dashboard?.data?.top_stores ?? [];
            this.returnStores = dashboard?.data?.return_stores ?? [];
            this.monthlyTrend = dashboard?.data?.monthly_trend ?? [];
            this.statsLoading = false;
        },

        async loadProducts() {
            const result = await this.api('/products');
            if (result) this.products = result.data || [];
        },

        editProduct(product) {
            this.productForm = { ...product };
            this.showProductForm = true;
        },

        async createProduct() {
            const result = await this.api('/products', {
                method: 'POST',
                body: JSON.stringify(this.productForm),
            });
            if (result && result.status === 201) {
                this.showProductForm = false;
                this.productForm = {};
                await this.loadProducts();
            }
        },

        async updateProduct() {
            const { id, ...data } = this.productForm;
            const result = await this.api('/products/' + id, {
                method: 'PUT',
                body: JSON.stringify(data),
            });
            if (result && result.status === 200) {
                this.showProductForm = false;
                this.productForm = {};
                await this.loadProducts();
            }
        },

        async deleteProduct(id) {
            if (!confirm('Hapus product ini?')) return;
            await this.api('/products/' + id, { method: 'DELETE' });
            await this.loadProducts();
        },

        async loadStores() {
            const result = await this.api('/stores');
            if (result) this.stores = result.data || [];
        },

        editStore(store) {
            this.storeForm = { ...store };
            this.showStoreForm = true;
        },

        async createStore() {
            const result = await this.api('/stores', {
                method: 'POST',
                body: JSON.stringify(this.storeForm),
            });
            if (result && result.status === 201) {
                this.showStoreForm = false;
                this.storeForm = {};
                await this.loadStores();
            } else if (result) {
                this.locationError = result.data?.error || 'Gagal menyimpan store.';
            }
        },

        async updateStore() {
            const { id, ...data } = this.storeForm;
            const result = await this.api('/stores/' + id, {
                method: 'PUT',
                body: JSON.stringify(data),
            });
            if (result && result.status === 200) {
                this.showStoreForm = false;
                this.storeForm = {};
                await this.loadStores();
            } else if (result) {
                this.locationError = result.data?.error || 'Gagal menyimpan store.';
            }
        },

        getLocation() {
            this.locationError = '';
            if (!navigator.geolocation) {
                this.locationError = 'Geolokasi tidak didukung oleh browser ini.';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.storeForm.latitude = pos.coords.latitude.toFixed(6);
                    this.storeForm.longitude = pos.coords.longitude.toFixed(6);
                },
                (err) => {
                    switch (err.code) {
                        case err.PERMISSION_DENIED:
                            this.locationError = 'Izin lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser.';
                            break;
                        case err.POSITION_UNAVAILABLE:
                            this.locationError = 'Lokasi tidak tersedia. Silakan coba lagi.';
                            break;
                        case err.TIMEOUT:
                            this.locationError = 'Waktu permintaan lokasi habis. Silakan coba lagi.';
                            break;
                        default:
                            this.locationError = 'Gagal mendapatkan lokasi.';
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        },

        async deleteStore(id) {
            if (!confirm('Hapus store ini?')) return;
            await this.api('/stores/' + id, { method: 'DELETE' });
            await this.loadStores();
        },

        async loadDistributors() {
            const result = await this.api('/distributors');
            if (result) this.distributors = result.data || [];
        },

        editDistributor(distributor) {
            this.distributorForm = { ...distributor, password: '' };
            this.createdPassword = '';
            this.showDistributorForm = true;
        },

        async createDistributor() {
            this.createdPassword = '';
            const result = await this.api('/distributors', {
                method: 'POST',
                body: JSON.stringify(this.distributorForm),
            });
            if (result && result.status === 201) {
                this.createdPassword = result.data.password;
                this.distributorForm = {};
                await this.loadDistributors();
            }
        },

        async updateDistributor() {
            const { id, username, email, password, status, ...rest } = this.distributorForm;
            const data = {};
            if (username) data.username = username;
            if (email) data.email = email;
            if (password) data.password = password;
            const result = await this.api('/distributors/' + id, {
                method: 'PUT',
                body: JSON.stringify(data),
            });
            if (result && result.status === 200) {
                this.showDistributorForm = false;
                this.distributorForm = {};
                await this.loadDistributors();
            }
        },

        async deactivateDistributor(id) {
            if (!confirm('Nonaktifkan distributor ini?')) return;
            await this.api('/distributors/' + id, { method: 'DELETE' });
            await this.loadDistributors();
        },
    };
}
