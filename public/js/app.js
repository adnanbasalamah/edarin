const API_BASE = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '') + '/api.php';

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
        showDistributorForm: false,
        productForm: {},
        storeForm: {},
        storeImageFile: null,
        storeImagePreview: null,
        distributorForm: {},
        createdPassword: '',
        locationError: '',
        storeSearch: '',
        saleStoreSearch: '',
        highlightedStoreIndex: -1,
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
        notas: [],
        notaDetail: null,
        storeDetail: null,
        storeDetailLoading: false,
        storeDetailError: '',
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

        get saleFilteredStores() {
            if (!this.saleStoreSearch) return this.stores;
            const q = this.saleStoreSearch.toLowerCase();
            return this.stores.filter(s =>
                (s.name || '').toLowerCase().includes(q) ||
                (s.owner || '').toLowerCase().includes(q)
            );
        },

        get selectedStoreName() {
            if (!this.saleForm.store_id) return '';
            const store = this.stores.find(s => s.id == this.saleForm.store_id);
            return store ? store.name : '';
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
            const [base, ...rest] = hash.split('/');
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
                'store-form': 'Store',
                distributors: 'Distributor',
                sales: 'Input Penjualan',
                reports: 'Report',
                'nota-detail': 'Detail Nota',
            };
            this.pageTitle = titles[base] || 'Edarin';

            if (base === 'dashboard') {
                this.filterDateFrom = new Date().toISOString().slice(0, 10);
                this.filterDateTo = new Date().toISOString().slice(0, 10);
                this.loadStats();
            }
            if (base === 'products') this.loadProducts();
            if (base === 'stores') this.loadStores();
            if (base === 'distributors') this.loadDistributors();
            if (base === 'sales') this.loadSalesFormData();
            if (base === 'reports') {
                this.reportPeriod = 'today';
                this.loadReports();
                this.loadNotas();
            }
            if (base === 'audit') this.loadAudit();
            if (base === 'nota-detail') {
                this.pageTitle = 'Detail Nota';
                if (rest.length && rest[0] && !this.notaDetail) {
                    const notaId = rest[0];
                    this.viewNota(notaId);
                }
            }
            if (base === 'store-detail') {
                this.pageTitle = 'Detail Toko';
                if (rest.length && rest[0] && (!this.storeDetail || String(this.storeDetail.id) !== rest[0])) {
                    this.viewStore(rest[0]);
                }
            }
            if (base === 'store-form') {
                if (rest.length && rest[0]) {
                    this.pageTitle = 'Edit Store';
                    this.loadStoreForEdit(rest[0]);
                } else {
                    this.pageTitle = 'Tambah Store';
                    this.storeForm = {};
                    this.storeImageFile = null;
                    this.storeImagePreview = null;
                    this.locationError = '';
                }
            }
        },

        navigate(path) {
            window.location.hash = path;
        },

        async api(path, options = {}) {
            const headers = { 'Content-Type': 'application/json' };
            if (this.token) headers['Authorization'] = 'Bearer ' + this.token;

            const qIndex = path.indexOf('?');
            const route = qIndex >= 0 ? path.substring(0, qIndex) : path;
            const qs = qIndex >= 0 ? path.substring(qIndex + 1) : '';
            let url = API_BASE + '?path=' + route;
            if (qs) url += '&' + qs;

            try {
                const res = await fetch(url, { ...options, headers });
                const data = await res.json();

                if (res.status === 401 && this.isLoggedIn) {
                    this.logout();
                    return null;
                }

                return { status: res.status, data };
            } catch (err) {
                console.error('API fetch failed:', err, 'URL:', url);
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

                    const qi = item.path.indexOf('?');
                    const p = qi >= 0 ? item.path.substring(0, qi) : item.path;
                    const q = qi >= 0 ? item.path.substring(qi + 1) : '';
                    let iurl = API_BASE + '?path=' + p;
                    if (q) iurl += '&' + q;
                    const res = await fetch(iurl, { ...item.options, headers });
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

                    const res = await fetch(API_BASE + '?path=/sales', {
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
            const prevStoreId = this.saleForm.store_id;
            this.saleForm = { store_id: prevStoreId || '', items: {}, sale_date: new Date().toISOString().slice(0, 10) };
            this.saleFormError = '';
            this.saleFormSuccess = '';
            this.saleStoreSearch = prevStoreId ? '' : this.saleStoreSearch;
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
                const qi = url.indexOf('?');
                const purl = qi >= 0 ? url.substring(0, qi) : url;
                const pqs = qi >= 0 ? url.substring(qi + 1) : '';
                let durl = API_BASE + '?path=' + purl;
                if (pqs) durl += '&' + pqs;
                const res = await fetch(durl, {
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

        selectStore(store) {
            this.saleForm.store_id = store.id;
            this.saleStoreSearch = '';
        },

        clearStoreSelection() {
            this.saleForm.store_id = '';
            this.saleStoreSearch = '';
        },

        navigateStoresDown() {
            const list = this.saleFilteredStores;
            if (!list.length) return;
            this.highlightedStoreIndex = (this.highlightedStoreIndex + 1) % list.length;
        },

        navigateStoresUp() {
            const list = this.saleFilteredStores;
            if (!list.length) return;
            this.highlightedStoreIndex = this.highlightedStoreIndex <= 0
                ? list.length - 1
                : this.highlightedStoreIndex - 1;
        },

        selectHighlightedStore() {
            if (this.highlightedStoreIndex < 0) return;
            const list = this.saleFilteredStores;
            if (this.highlightedStoreIndex >= list.length) return;
            this.selectStore(list[this.highlightedStoreIndex]);
        },

        closeStoreDropdown() {
            this.highlightedStoreIndex = -1;
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

        async loadNotas() {
            const result = await this.api('/notas');
            if (result) {
                this.notas = result.data || [];
            } else {
                this.notas = [];
            }
        },

        async viewNota(notaId) {
            const result = await this.api('/notas/' + notaId);
            if (result && result.status === 200) {
                this.notaDetail = result.data;
                window.location.hash = 'nota-detail/' + notaId;
            }
        },

        goBackToNotas() {
            this.notaDetail = null;
            window.location.hash = 'reports';
        },

        async viewStore(storeId) {
            this.storeDetailLoading = true;
            this.storeDetailError = '';
            this.storeDetail = null;
            const result = await this.api('/stores/' + storeId);
            if (result && result.status === 200) {
                this.storeDetail = result.data;
                window.location.hash = 'store-detail/' + storeId;
                this.$nextTick(() => {
                    this.initStoreMap();
                });
            } else {
                this.storeDetailError = 'Gagal memuat data toko. Silakan coba lagi.';
            }
            this.storeDetailLoading = false;
        },

        goBackToStores() {
            if (this._storeMapRef) {
                this._storeMapRef.remove();
                this._storeMapRef = null;
            }
            this.storeDetail = null;
            this.storeDetailError = '';
            window.location.hash = 'stores';
        },

        initStoreMap() {
            const el = document.getElementById('store-map');
            if (!el || !this.storeDetail || !this.storeDetail.latitude || !this.storeDetail.longitude) return;
            if (el._leaflet_id) {
                const oldMap = this._storeMapRef;
                if (oldMap) {
                    oldMap.remove();
                    this._storeMapRef = null;
                }
                el._leaflet_id = null;
            }
            const map = L.map('store-map').setView([parseFloat(this.storeDetail.latitude), parseFloat(this.storeDetail.longitude)], 15);
            this._storeMapRef = map;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            L.marker([parseFloat(this.storeDetail.latitude), parseFloat(this.storeDetail.longitude)]).addTo(map);
            setTimeout(() => map.invalidateSize(), 200);
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
            this.navigate('store-form/' + store.id);
        },

        async loadStoreForEdit(storeId) {
            const result = await this.api('/stores/' + storeId);
            if (result && result.status === 200) {
                const store = result.data;
                this.storeForm = { ...store };
                this.storeImageFile = null;
                this.storeImagePreview = store.image ? (API_BASE + '?path=/stores/image/' + store.image.split('/').pop()) : null;
                this.locationError = '';
            }
        },

        cancelStoreForm() {
            const id = this.storeForm.id;
            this.storeForm = {};
            this.storeImageFile = null;
            this.storeImagePreview = null;
            this.locationError = '';
            this.navigate(id ? 'store-detail/' + id : 'stores');
        },

        handleStoreImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.storeImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.storeImagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        async createStore() {
            const data = new FormData();
            data.append('name', this.storeForm.name || '');
            data.append('owner', this.storeForm.owner || '');
            data.append('address', this.storeForm.address || '');
            data.append('phone', this.storeForm.phone || '');
            if (this.storeForm.latitude) data.append('latitude', this.storeForm.latitude);
            if (this.storeForm.longitude) data.append('longitude', this.storeForm.longitude);
            if (this.storeImageFile) {
                data.append('image', this.storeImageFile);
            }

            const headers = { 'Authorization': 'Bearer ' + this.token };
            let url = API_BASE + '?path=/stores';

            try {
                const res = await fetch(url, { method: 'POST', headers, body: data });
                const json = await res.json();
                if (res.status === 201) {
                    this.storeForm = {};
                    this.storeImageFile = null;
                    this.storeImagePreview = null;
                    this.navigate('stores');
                }
            } catch (err) {
                console.error('Create store failed:', err);
            }
        },

        async updateStore() {
            const { id, created_at, updated_at, image, ...rest } = this.storeForm;
            const data = new FormData();
            data.append('_method', 'PUT');
            const allowedFields = ['name', 'owner', 'address', 'phone', 'latitude', 'longitude'];
            for (const key of allowedFields) {
                const value = rest[key];
                if (value !== null && value !== undefined && value !== '') {
                    data.append(key, value);
                }
            }
            if (this.storeImageFile) {
                data.append('image', this.storeImageFile);
            }

            const headers = { 'Authorization': 'Bearer ' + this.token };
            let url = API_BASE + '?path=/stores/' + id;

            try {
                const res = await fetch(url, { method: 'POST', headers, body: data });
                let json = null;
                try { json = await res.json(); } catch {}
                if (res.status === 200) {
                    this.storeImageFile = null;
                    this.storeImagePreview = null;
                    this.storeDetail = null;
                    this.navigate('store-detail/' + id);
                } else {
                    const msg = json?.messages ? Object.values(json.messages).join(', ') : (json?.error || 'Tidak diketahui');
                    alert('Gagal menyimpan (status ' + res.status + '): ' + msg);
                }
            } catch (err) {
                alert('Error: ' + (err.message || 'Koneksi terputus'));
                console.error('Update store failed:', err);
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

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { app };
}
