/**
 * Application JavaScript principale pour le scanner PWA
 * Gère l'authentification, le scan QR code, la communication API, 
 * le stockage local et la synchronisation
 */

console.log('[App] Script app.js chargé');

// ============================================
// CONFIGURATION ET CONSTANTES
// ============================================

const CONFIG = {
    API_BASE_URL: window.location.origin + '/api/v1',
    STORAGE_KEY_TOKEN: 'inventaire_token',
    STORAGE_KEY_USER: 'inventaire_user',
    STORAGE_KEY_INVENTAIRE: 'inventaire_current',
    STORAGE_KEY_LOCATION: 'inventaire_active_location',
    DB_NAME: 'InventaireDB',
    DB_VERSION: 1
};

// ============================================
// STATE MANAGEMENT
// ============================================

const AppState = {
    token: null,
    user: null,
    inventaire: null,
    activeLocation: null, // InventaireLocalisation actuelle
    localisation: null, // Localisation entity
    biensAttendus: [],
    scansSession: [],
    isOnline: navigator.onLine,
    pendingScansCount: 0,
    html5QrCode: null
};

// ============================================
// INDEXEDDB MANAGEMENT
// ============================================

/**
 * Gestionnaire de base de données IndexedDB
 * Gère le stockage local pour le mode offline
 */
class DBManager {
    constructor() {
        this.db = null;
    }

    /**
     * Initialise la base de données IndexedDB
     * Crée les object stores si nécessaire
     */
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(CONFIG.DB_NAME, CONFIG.DB_VERSION);

            request.onerror = () => {
                console.error('[DB] Erreur ouverture IndexedDB:', request.error);
                reject(request.error);
            };

            request.onsuccess = () => {
                this.db = request.result;
                console.log('[DB] IndexedDB initialisé');
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Object store pour scans en attente
                if (!db.objectStoreNames.contains('pending-scans')) {
                    const store = db.createObjectStore('pending-scans', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                    store.createIndex('inventaire_id', 'inventaire_id', { unique: false });
                    console.log('[DB] Object store "pending-scans" créé');
                }

                // Object store pour cache des biens
                if (!db.objectStoreNames.contains('biens-cache')) {
                    const store = db.createObjectStore('biens-cache', { keyPath: 'id' });
                    store.createIndex('localisation_id', 'localisation_id', { unique: false });
                    store.createIndex('code_inventaire', 'code_inventaire', { unique: false });
                    console.log('[DB] Object store "biens-cache" créé');
                }

                // Object store pour historique scans
                if (!db.objectStoreNames.contains('scans-history')) {
                    const store = db.createObjectStore('scans-history', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    store.createIndex('timestamp', 'timestamp', { unique: false });
                    store.createIndex('bien_id', 'bien_id', { unique: false });
                    console.log('[DB] Object store "scans-history" créé');
                }
            };
        });
    }

    /**
     * Ajoute un scan en attente de synchronisation
     * @param {Object} scanData - Données du scan à stocker
     * @returns {Promise<number>} ID du scan ajouté
     */
    async addPendingScan(scanData) {
        if (!this.db) {
            throw new Error('IndexedDB non initialisé');
        }

        const transaction = this.db.transaction(['pending-scans'], 'readwrite');
        const store = transaction.objectStore('pending-scans');
        
        const scan = {
            ...scanData,
            timestamp: Date.now(),
            token: AppState.token,
            inventaire_id: AppState.inventaire?.id
        };

        return new Promise((resolve, reject) => {
            const request = store.add(scan);
            request.onsuccess = () => {
                console.log('[DB] Scan en attente ajouté:', request.result);
                resolve(request.result);
            };
            request.onerror = () => {
                console.error('[DB] Erreur ajout scan:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Récupère tous les scans en attente
     * @returns {Promise<Array>} Liste des scans en attente
     */
    async getPendingScans() {
        if (!this.db) {
            return [];
        }

        const transaction = this.db.transaction(['pending-scans'], 'readonly');
        const store = transaction.objectStore('pending-scans');
        
        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => {
                console.error('[DB] Erreur récupération scans:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Supprime un scan en attente après synchronisation réussie
     * @param {number} id - ID du scan à supprimer
     * @returns {Promise<void>}
     */
    async deletePendingScan(id) {
        if (!this.db) {
            return;
        }

        const transaction = this.db.transaction(['pending-scans'], 'readwrite');
        const store = transaction.objectStore('pending-scans');
        
        return new Promise((resolve, reject) => {
            const request = store.delete(id);
            request.onsuccess = () => {
                console.log('[DB] Scan supprimé:', id);
                resolve();
            };
            request.onerror = () => {
                console.error('[DB] Erreur suppression scan:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Met en cache les biens d'une localisation
     * @param {Array} biens - Liste des biens à mettre en cache
     * @returns {Promise<void>}
     */
    async cacheBiens(biens) {
        if (!this.db || !biens || biens.length === 0) {
            return;
        }

        const transaction = this.db.transaction(['biens-cache'], 'readwrite');
        const store = transaction.objectStore('biens-cache');
        
        try {
            for (const bien of biens) {
                await new Promise((resolve, reject) => {
                    const request = store.put(bien);
                    request.onsuccess = () => resolve();
                    request.onerror = () => reject(request.error);
                });
            }
            console.log(`[DB] ${biens.length} bien(s) mis en cache`);
        } catch (error) {
            console.error('[DB] Erreur cache biens:', error);
        }
    }

    /**
     * Récupère un bien depuis le cache
     * @param {number} bienId - ID du bien
     * @returns {Promise<Object|null>} Le bien ou null si non trouvé
     */
    async getCachedBien(bienId) {
        if (!this.db) {
            return null;
        }

        const transaction = this.db.transaction(['biens-cache'], 'readonly');
        const store = transaction.objectStore('biens-cache');
        
        return new Promise((resolve, reject) => {
            const request = store.get(bienId);
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => {
                console.error('[DB] Erreur récupération bien:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Récupère un bien par son code inventaire depuis le cache
     * @param {string} codeInventaire - Code inventaire du bien
     * @returns {Promise<Object|null>} Le bien ou null si non trouvé
     */
    async getCachedBienByCode(codeInventaire) {
        if (!this.db) {
            return null;
        }

        const transaction = this.db.transaction(['biens-cache'], 'readonly');
        const store = transaction.objectStore('biens-cache');
        const index = store.index('code_inventaire');
        
        return new Promise((resolve, reject) => {
            const request = index.get(codeInventaire);
            request.onsuccess = () => resolve(request.result || null);
            request.onerror = () => {
                console.error('[DB] Erreur récupération bien par code:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Ajoute un scan à l'historique local
     * @param {Object} scanData - Données du scan
     * @returns {Promise<number>} ID du scan ajouté
     */
    async addToHistory(scanData) {
        if (!this.db) {
            return;
        }

        const transaction = this.db.transaction(['scans-history'], 'readwrite');
        const store = transaction.objectStore('scans-history');
        
        const scan = {
            ...scanData,
            timestamp: Date.now()
        };

        return new Promise((resolve, reject) => {
            const request = store.add(scan);
            request.onsuccess = () => {
                console.log('[DB] Scan ajouté à l\'historique:', request.result);
                resolve(request.result);
            };
            request.onerror = () => {
                console.error('[DB] Erreur ajout historique:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Récupère l'historique des scans (les plus récents en premier)
     * @param {number} limit - Nombre maximum de scans à récupérer
     * @returns {Promise<Array>} Liste des scans
     */
    async getHistory(limit = 20) {
        if (!this.db) {
            return [];
        }

        const transaction = this.db.transaction(['scans-history'], 'readonly');
        const store = transaction.objectStore('scans-history');
        const index = store.index('timestamp');
        
        return new Promise((resolve, reject) => {
            const request = index.openCursor(null, 'prev');
            const results = [];
            
            request.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor && results.length < limit) {
                    results.push(cursor.value);
                    cursor.continue();
                } else {
                    resolve(results);
                }
            };
            request.onerror = () => {
                console.error('[DB] Erreur récupération historique:', request.error);
                reject(request.error);
            };
        });
    }
}

// Instance globale du gestionnaire de base de données
const dbManager = new DBManager();

// ============================================
// API CALLS
// ============================================

/**
 * Classe pour gérer les appels API
 * Gère l'authentification, les erreurs et le mode offline
 */
class API {
    /**
     * Effectue une requête API avec gestion d'erreurs et authentification
     * @param {string} endpoint - Endpoint de l'API
     * @param {Object} options - Options de la requête (method, body, headers, etc.)
     * @returns {Promise<Object>} Réponse JSON de l'API
     */
    static async request(endpoint, options = {}) {
        const url = `${CONFIG.API_BASE_URL}${endpoint}`;
        const method = options.method || 'GET';
        console.log(`[API] ${method} ${url}`);
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        // Ajouter le token d'authentification si disponible
        if (AppState.token) {
            defaultOptions.headers['Authorization'] = `Bearer ${AppState.token}`;
            console.log('[API] Token d\'authentification ajouté');
        } else {
            console.warn('[API] Aucun token d\'authentification disponible');
        }

        // Ajouter le CSRF token si disponible
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }

        const finalOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };

        try {
            const response = await fetch(url, finalOptions);
            console.log(`[API] Réponse ${response.status} ${response.statusText}`);
            
            if (!response.ok) {
                // Gestion des erreurs HTTP
                if (response.status === 401) {
                    // Token invalide ou expiré, déconnecter l'utilisateur
                    console.warn('[API] Token invalide ou expiré, déconnexion...');
                    AuthManager.logout();
                    throw new Error('Session expirée. Veuillez vous reconnecter.');
                }
                
                // Essayer de récupérer le message d'erreur depuis la réponse
                let errorMessage = `Erreur HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorText = await response.text();
                    if (errorText) {
                        try {
                            const errorData = JSON.parse(errorText);
                            errorMessage = errorData.message || errorData.error || errorMessage;
                            console.error('[API] Détails erreur:', errorData);
                        } catch (parseError) {
                            console.warn('[API] Erreur non-JSON:', errorText);
                            errorMessage = errorText || errorMessage;
                        }
                    }
                } catch (textError) {
                    console.error('[API] Erreur lecture réponse:', textError);
                }
                
                const error = new Error(errorMessage);
                error.status = response.status;
                throw error;
            }

            // Parser la réponse JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const jsonData = await response.json();
                console.log('[API] Données JSON reçues');
                return jsonData;
            } else {
                // Si ce n'est pas du JSON, retourner le texte
                const textData = await response.text();
                console.log('[API] Données texte reçues:', textData);
                return textData;
            }
        } catch (error) {
            console.error('[API] Erreur requête:', error);
            console.error('[API] URL:', url);
            console.error('[API] Method:', method);
            
            // Si offline et requête GET, ne pas lever d'erreur (utiliser le cache)
            if (!navigator.onLine && method !== 'POST' && method !== 'PUT' && method !== 'DELETE') {
                console.warn('[API] Mode offline, utilisation du cache');
                return null;
            }
            
            // Propager l'erreur pour gestion par l'appelant
            throw error;
        }
    }

    /**
     * Connexion de l'utilisateur
     * @param {string} email - Email de l'utilisateur
     * @param {string} password - Mot de passe
     * @returns {Promise<Object>} Réponse avec token et user
     */
    static async login(users, mdp) {
        return this.request('/login', {
            method: 'POST',
            body: JSON.stringify({ users, mdp })
        });
    }

    /**
     * Déconnexion de l'utilisateur
     * @returns {Promise<void>}
     */
    static async logout() {
        try {
            await this.request('/logout', {
                method: 'POST'
            });
        } catch (error) {
            // Ignorer les erreurs de déconnexion (peut être offline)
            console.warn('[API] Erreur logout (ignorée):', error);
        }
    }

    /**
     * Récupère l'inventaire en cours de l'utilisateur
     * @returns {Promise<Object>} Données de l'inventaire
     */
    static async getCurrentInventaire() {
        return this.request('/inventaires/current');
    }

    /**
     * Récupère les localisations assignées à l'utilisateur pour un inventaire
     * @param {number} inventaireId - ID de l'inventaire
     * @returns {Promise<Array>} Liste des localisations
     */
    static async getMesLocalisations(inventaireId) {
        const response = await this.request(`/inventaires/${inventaireId}/mes-localisations`);
        // Le contrôleur retourne { localisations: [...] }, extraire le tableau
        return response?.localisations || response || [];
    }

    /**
     * Récupère une localisation par son code
     * @param {string} code - Code de la localisation
     * @returns {Promise<Object>} Données de la localisation
     */
    /**
     * Récupère une localisation par son ID
     * @param {number} id - ID de la localisation
     * @returns {Promise<Object>} Données de la localisation
     */
    static async getLocalisation(id) {
        console.log('[API] getLocalisation appelée avec ID:', id);
        const response = await this.request(`/localisations/${id}`);
        // Le contrôleur retourne { localisation: {...} }, extraire l'objet localisation
        const localisation = response?.localisation || response;
        console.log('[API] Localisation extraite:', localisation);
        return localisation;
    }

    /**
     * Récupère une localisation par son code
     * @param {string} code - Code de la localisation
     * @returns {Promise<Object>} Données de la localisation
     */
    static async getLocalisationByCode(code) {
        console.log('[API] getLocalisationByCode appelée avec code:', code);
        const response = await this.request(`/localisations/by-code/${encodeURIComponent(code)}`);
        // Le contrôleur retourne { localisation: {...} }, extraire l'objet localisation
        const localisation = response?.localisation || response;
        console.log('[API] Localisation extraite:', localisation);
        return localisation;
    }

    /**
     * Récupère les biens d'une localisation
     * @param {number} localisationId - ID de la localisation
     * @returns {Promise<Array>} Liste des biens
     */
    static async getBiensLocalisation(localisationId) {
        console.log('[API] getBiensLocalisation appelée avec ID:', localisationId);
        const response = await this.request(`/localisations/${localisationId}/biens`);
        // Le contrôleur retourne { biens: [...], total: ... }, extraire le tableau biens
        const biens = response?.biens || response || [];
        console.log('[API] Biens extraits:', biens.length, 'biens');
        return biens;
    }

    /**
     * Récupère les détails d'un bien
     * @param {number} bienId - ID du bien
     * @returns {Promise<Object>} Données du bien
     */
    static async getBien(bienId) {
        console.log('[API] getBien appelée avec ID:', bienId);
        const response = await this.request(`/biens/${bienId}`);
        // Le contrôleur retourne { bien: {...} }, extraire l'objet bien
        const bien = response?.bien || response;
        console.log('[API] Bien extrait:', bien);
        return bien;
    }

    /**
     * Récupère un bien par son code inventaire
     * @param {string} code - Code inventaire du bien
     * @returns {Promise<Object>} Données du bien
     */
    static async getBienByCode(code) {
        console.log('[API] getBienByCode appelée avec code:', code);
        const response = await this.request(`/biens/by-code/${encodeURIComponent(code)}`);
        // Le contrôleur retourne { bien: {...} }, extraire l'objet bien
        const bien = response?.bien || response;
        console.log('[API] Bien extrait par code:', bien);
        return bien;
    }

    /**
     * Démarre le scan d'une localisation
     * @param {number} inventaireId - ID de l'inventaire
     * @param {number} localisationId - ID de la localisation
     * @returns {Promise<Object>} Données de l'inventaire localisation créée
     */
    static async demarrerLocalisation(inventaireId, localisationId) {
        return this.request(`/inventaires/${inventaireId}/demarrer-localisation`, {
            method: 'POST',
            body: JSON.stringify({ 
                localisation_id: localisationId,
                user_id: AppState.user.id
            })
        });
    }

    /**
     * Termine le scan d'une localisation
     * @param {number} inventaireId - ID de l'inventaire
     * @param {number} inventaireLocalisationId - ID de l'inventaire localisation
     * @returns {Promise<Object>} Réponse de confirmation
     */
    static async terminerLocalisation(inventaireId, inventaireLocalisationId) {
        return this.request(`/inventaires/${inventaireId}/terminer-localisation`, {
            method: 'POST',
            body: JSON.stringify({ 
                inventaire_localisation_id: inventaireLocalisationId
            })
        });
    }

    /**
     * Enregistre un scan de bien
     * @param {number} inventaireId - ID de l'inventaire
     * @param {Object} scanData - Données du scan
     * @returns {Promise<Object>} Réponse de confirmation
     */
    static async enregistrerScan(inventaireId, scanData) {
        return this.request(`/inventaires/${inventaireId}/scan`, {
            method: 'POST',
            body: JSON.stringify(scanData)
        });
    }

    /**
     * Récupère les statistiques d'un inventaire
     * @param {number} inventaireId - ID de l'inventaire
     * @returns {Promise<Object>} Statistiques de l'inventaire
     */
    static async getStats(inventaireId) {
        return this.request(`/inventaires/${inventaireId}/stats`);
    }
}

// ============================================
// AUTHENTICATION MANAGER
// ============================================

/**
 * Gestionnaire d'authentification
 * Gère la connexion, déconnexion et la persistance de session
 */
class AuthManager {
    /**
     * Initialise l'authentification en chargeant les données depuis localStorage
     * @returns {boolean} True si une session valide existe
     */
    static init() {
        // Charger token et user depuis localStorage
        const token = localStorage.getItem(CONFIG.STORAGE_KEY_TOKEN);
        const userJson = localStorage.getItem(CONFIG.STORAGE_KEY_USER);

        if (token && userJson) {
            try {
                AppState.token = token;
                AppState.user = JSON.parse(userJson);
                console.log('[Auth] Session restaurée pour:', AppState.user.users);
                return true;
            } catch (error) {
                console.error('[Auth] Erreur parsing user data:', error);
                this.logout();
                return false;
            }
        }

        return false;
    }

    /**
     * Connecte l'utilisateur
     * @param {string} users - Nom d'utilisateur
     * @param {string} mdp - Mot de passe
     * @returns {Promise<Object>} Résultat de la connexion {success: boolean, error?: string}
     */
    static async login(users, mdp) {
        try {
            const response = await API.login(users, mdp);
            
            if (response.token && response.user) {
                // Mettre à jour le state
                AppState.token = response.token;
                AppState.user = response.user;

                // Sauvegarder dans localStorage
                localStorage.setItem(CONFIG.STORAGE_KEY_TOKEN, response.token);
                localStorage.setItem(CONFIG.STORAGE_KEY_USER, JSON.stringify(response.user));

                console.log('[Auth] Connexion réussie:', response.user.users);
                return { success: true };
            } else {
                throw new Error('Réponse invalide du serveur');
            }
        } catch (error) {
            console.error('[Auth] Erreur login:', error);
            return { 
                success: false, 
                error: error.message || 'Erreur de connexion' 
            };
        }
    }

    /**
     * Déconnecte l'utilisateur et nettoie toutes les données
     */
    static logout() {
        console.log('[Auth] Déconnexion...');

        // Nettoyer le state
        AppState.token = null;
        AppState.user = null;
        AppState.inventaire = null;
        AppState.activeLocation = null;
        AppState.localisation = null;
        AppState.biensAttendus = [];
        AppState.scansSession = [];

        // Nettoyer localStorage
        localStorage.removeItem(CONFIG.STORAGE_KEY_TOKEN);
        localStorage.removeItem(CONFIG.STORAGE_KEY_USER);
        localStorage.removeItem(CONFIG.STORAGE_KEY_INVENTAIRE);
        localStorage.removeItem(CONFIG.STORAGE_KEY_LOCATION);

        // Appeler l'API de déconnexion (sans attendre)
        API.logout().catch(() => {
            // Ignorer les erreurs (peut être offline)
        });

        // Rediriger vers login
        showView('login');
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     * @returns {boolean} True si authentifié
     */
    static isAuthenticated() {
        return AppState.token !== null && AppState.user !== null;
    }
}

// ============================================
// SYNC MANAGER (pour mode offline)
// ============================================

/**
 * Gestionnaire de synchronisation
 * Synchronise les scans en attente lorsque la connexion revient
 */
class SyncManager {
    /**
     * Synchronise tous les scans en attente avec l'API
     * @returns {Promise<void>}
     */
    static async syncPendingScans() {
        if (!navigator.onLine) {
            console.log('[Sync] Offline, sync impossible');
            return;
        }

        if (!AuthManager.isAuthenticated()) {
            console.log('[Sync] Non authentifié, sync impossible');
            return;
        }

        try {
            const pendingScans = await dbManager.getPendingScans();
            
            if (pendingScans.length === 0) {
                console.log('[Sync] Aucun scan en attente');
                await this.updatePendingScansBadge();
                return;
            }

            console.log(`[Sync] Synchronisation de ${pendingScans.length} scan(s) en attente...`);

            let successCount = 0;
            let errorCount = 0;

            for (const scan of pendingScans) {
                try {
                    // Restaurer le token pour cette requête (au cas où il aurait changé)
                    const savedToken = AppState.token;
                    if (scan.token) {
                        AppState.token = scan.token;
                    }

                    await API.enregistrerScan(scan.inventaire_id, scan.data);
                    await dbManager.deletePendingScan(scan.id);
                    
                    // Restaurer le token actuel
                    AppState.token = savedToken;
                    
                    successCount++;
                    console.log('[Sync] Scan synchronisé:', scan.id);
  } catch (error) {
                    errorCount++;
                    console.error('[Sync] Erreur sync scan:', error);
                    // On continue avec les autres scans même en cas d'erreur
                }
            }

            await this.updatePendingScansBadge();

            if (successCount > 0) {
                showToast(`${successCount} scan(s) synchronisé(s) avec succès`, 'success');
            }
            if (errorCount > 0) {
                showToast(`${errorCount} scan(s) n'ont pas pu être synchronisé(s)`, 'warning');
            }
        } catch (error) {
            console.error('[Sync] Erreur synchronisation générale:', error);
            showToast('Erreur lors de la synchronisation', 'error');
        }
    }

    /**
     * Met à jour le badge de scans en attente dans l'interface
     * @returns {Promise<void>}
     */
    static async updatePendingScansBadge() {
        try {
            const pendingScans = await dbManager.getPendingScans();
            AppState.pendingScansCount = pendingScans.length;

            const badge = document.getElementById('pending-scans-badge');
            const statusText = document.getElementById('sync-status');

            if (badge && statusText) {
                if (pendingScans.length > 0) {
                    badge.textContent = pendingScans.length;
                    badge.classList.remove('hidden');
                    if (navigator.onLine) {
                        statusText.textContent = `${pendingScans.length} scan(s) en attente`;
                    } else {
                        statusText.textContent = `${pendingScans.length} scan(s) hors ligne`;
                    }
                } else {
                    badge.classList.add('hidden');
                    if (navigator.onLine) {
                        statusText.textContent = 'Synchronisé';
                    } else {
                        statusText.textContent = 'Mode hors ligne';
                    }
                }
            }

            // Mettre à jour l'indicateur de connexion dans le header
            updateOnlineStatusIndicator();
        } catch (error) {
            console.error('[Sync] Erreur mise à jour badge:', error);
        }
    }
}

// ============================================
// UTILITIES
// ============================================

/**
 * Affiche une vue spécifique en masquant les autres
 * @param {string} viewName - Nom de la vue à afficher (sans le préfixe "view-")
 */
function showView(viewName) {
    console.log('[UI] ========== showView() appelée ==========');
    console.log('[UI] Paramètre viewName:', viewName);
    console.log('[UI] ID recherché:', `view-${viewName}`);
    
    // Masquer/afficher le header et le menu selon la vue
    const header = document.getElementById('app-header') || document.querySelector('header');
    const menuBtn = document.getElementById('menu-btn');
    const menuDrawer = document.getElementById('menu-drawer');
    
    if (viewName === 'login') {
        // Cacher le header et le menu sur la page de login
        if (header) header.classList.add('hidden');
        if (menuBtn) menuBtn.classList.add('hidden');
        if (menuDrawer) menuDrawer.classList.add('hidden');
        // Ajuster le padding du main pour la page de login (pas de header)
        const main = document.getElementById('main-content');
        if (main) {
            main.style.paddingTop = '2rem';
        }
    } else {
        // Afficher le header et le menu sur les autres pages
        if (header) header.classList.remove('hidden');
        if (menuBtn) menuBtn.classList.remove('hidden');
        // Restaurer le padding normal du main
        const main = document.getElementById('main-content');
        if (main) {
            main.style.paddingTop = '7rem';
        }
    }
    
    // Lister toutes les vues existantes
    const allViews = document.querySelectorAll('[id^="view-"]');
    console.log('[UI] Vues trouvées dans le DOM:', Array.from(allViews).map(v => v.id));
    
    // Cacher toutes les vues
    allViews.forEach(view => {
        console.log('[UI] Masquage de la vue:', view.id);
        view.classList.add('hidden');
    });

    // Afficher la vue demandée
    const viewId = `view-${viewName}`;
    const view = document.getElementById(viewId);
    
    if (view) {
        console.log('[UI] ✅ Vue trouvée:', viewId);
        console.log('[UI] Classes avant:', view.className);
        view.classList.remove('hidden');
        console.log('[UI] Classes après:', view.className);
        console.log('[UI] ✅ Vue affichée avec succès');
        
        // Vérification visuelle
        const isVisible = !view.classList.contains('hidden') && window.getComputedStyle(view).display !== 'none';
        console.log('[UI] Vérification visibilité:', isVisible ? 'VISIBLE' : 'CACHÉE');
        
        if (!isVisible) {
            console.error('[UI] ⚠️ La vue est toujours cachée malgré remove("hidden")');
            console.error('[UI] Style computed:', window.getComputedStyle(view).display);
        }
    } else {
        console.error(`[UI] ❌ Vue "${viewName}" introuvable (élément ${viewId} non trouvé)`);
        console.error('[UI] Vues disponibles:', Array.from(allViews).map(v => v.id));
    }
    
    console.log('[UI] ========== Fin showView() ==========');
}

/**
 * Affiche une notification toast
 * @param {string} message - Message à afficher
 * @param {string} type - Type de toast (success, error, warning, info)
 */
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    
    if (!container) {
        console.warn('[UI] Container toast introuvable');
        return;
    }

    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-orange-500',
        info: 'bg-blue-500'
    };

    const icons = {
        success: '✓',
        error: '✗',
        warning: '⚠',
        info: 'ℹ'
    };

    const toast = document.createElement('div');
    toast.className = `${colors[type] || colors.info} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 transform transition-all duration-300 translate-x-0 opacity-100`;
    toast.innerHTML = `
        <span class="text-xl">${icons[type] || icons.info}</span>
        <span class="flex-1">${message}</span>
        <button class="ml-4 hover:bg-white hover:bg-opacity-20 rounded p-1" aria-label="Fermer">✕</button>
    `;

    // Fermeture au clic sur le bouton
    toast.querySelector('button').addEventListener('click', () => {
        closeToast(toast);
    });

    container.appendChild(toast);

    // Auto-dismiss après 5 secondes
    setTimeout(() => {
        if (toast.parentElement) {
            closeToast(toast);
        }
    }, 5000);

    // Jouer son si disponible
    playSound(type);
}

/**
 * Ferme une notification toast avec animation
 * @param {HTMLElement} toast - Élément toast à fermer
 */
function closeToast(toast) {
    toast.style.transform = 'translateX(100%)';
    toast.style.opacity = '0';
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 300);
}

/**
 * Joue un son de feedback
 * @param {string} type - Type de son (success, error, warning, info)
 */
function playSound(type) {
    try {
        // Créer un contexte audio simple pour générer un beep
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Fréquences selon le type
        const frequencies = {
            success: 800,
            error: 400,
            warning: 600,
            info: 500
        };

        oscillator.frequency.value = frequencies[type] || 500;
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    } catch (error) {
        // Ignore les erreurs de son (peut ne pas être supporté)
        console.debug('[Sound] Audio non disponible:', error);
    }
}

/**
 * Formate une date en texte relatif (il y a X min, il y a Xh, etc.)
 * @param {Date|string|number} date - Date à formater
 * @returns {string} Date formatée
 */
function formatDate(date) {
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'À l\'instant';
    if (minutes < 60) return `Il y a ${minutes} min`;
    if (hours < 24) return `Il y a ${hours}h`;
    if (days < 7) return `Il y a ${days}j`;
    
    return d.toLocaleDateString('fr-FR', { 
        day: '2-digit', 
        month: 'short', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

// ============================================
// SCANNER MANAGER
// ============================================

/**
 * Gestionnaire du scanner QR code
 * Gère l'initialisation, le démarrage, l'arrêt et le traitement des scans
 */
class ScannerManager {
    constructor() {
        this.html5QrCode = null;
        this.isScanning = false;
        this.currentMode = 'localisation'; // 'localisation' ou 'bien'
    }

    /**
     * Initialise le scanner HTML5 QR Code
     * @returns {Promise<void>}
     */
    async init() {
        try {
            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('Bibliothèque Html5Qrcode non chargée');
            }
            this.html5QrCode = new Html5Qrcode("qr-reader");
            console.log('[Scanner] Scanner initialisé');
        } catch (error) {
            console.error('[Scanner] Erreur initialisation:', error);
            throw error;
        }
    }

    /**
     * Démarre le scanner avec la caméra
     * @returns {Promise<void>}
     */
    async start() {
        if (this.isScanning) {
            console.log('[Scanner] Scanner déjà actif');
            return;
        }

        if (!this.html5QrCode) {
            await this.init();
        }

        try {
            // Configuration du scanner
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            // Démarrer le scanner avec la caméra arrière
            await this.html5QrCode.start(
                { facingMode: "environment" }, // Caméra arrière
                config,
                this.onScanSuccess.bind(this),
                this.onScanError.bind(this)
            );

            this.isScanning = true;
            this.updateScannerUI('scanning');
            
            console.log('[Scanner] Scanner démarré');
        } catch (error) {
            console.error('[Scanner] Erreur démarrage scanner:', error);
            showToast('Impossible d\'accéder à la caméra', 'error');
            this.updateScannerUI('error');
        }
    }

    /**
     * Arrête le scanner
     * @returns {Promise<void>}
     */
    async stop() {
        if (!this.isScanning || !this.html5QrCode) {
            return;
        }

        try {
            await this.html5QrCode.stop();
            this.isScanning = false;
            this.updateScannerUI('stopped');
            console.log('[Scanner] Scanner arrêté');
        } catch (error) {
            console.error('[Scanner] Erreur arrêt scanner:', error);
        }
    }

    /**
     * Callback appelé lors d'un scan réussi
     * @param {string} decodedText - Texte décodé du QR code
     * @param {Object} decodedResult - Résultat complet du décodage
     */
    async onScanSuccess(decodedText, decodedResult) {
        console.log('[Scanner] ========== QR CODE SCANNÉ ==========');
        console.log('[Scanner] Texte brut décodé:', decodedText);
        console.log('[Scanner] Résultat complet:', decodedResult);

        // Vibration si disponible
        if (navigator.vibrate) {
            navigator.vibrate(200);
        }

        // Son de feedback
        playSound('success');

        // Arrêter temporairement le scanner
        await this.stop();

        // Parser le QR code
        let qrData;
        try {
            console.log('[Scanner] Tentative de parsing JSON...');
            qrData = JSON.parse(decodedText);
            console.log('[Scanner] ✓ JSON parsé avec succès:', qrData);
        } catch (error) {
            console.error('[Scanner] ✗ Erreur parsing JSON:', error);
            console.log('[Scanner] Tentative avec le texte brut comme code...');
            
            // Si le parsing échoue, essayer de deviner le type par le préfixe
            if (decodedText.startsWith('BUR-') || decodedText.startsWith('ATELIER-') || decodedText.startsWith('DEPOT-') || decodedText.startsWith('LOC-')) {
                qrData = { 
                    type: 'localisation', 
                    code: decodedText 
                };
                console.log('[Scanner] → Détecté comme localisation (par préfixe)');
            } else if (decodedText.startsWith('INV-')) {
                qrData = { 
                    type: 'bien', 
                    code: decodedText 
                };
                console.log('[Scanner] → Détecté comme bien (par préfixe)');
            } else {
                // Dernière tentative : considérer comme code brut
                qrData = { 
                    type: 'unknown', 
                    code: decodedText 
                };
                console.log('[Scanner] → Type inconnu, code brut:', decodedText);
            }
        }

        console.log('[Scanner] ========================================');
        console.log('[Scanner] MODE ACTUEL DU SCANNER:', this.currentMode);
        console.log('[Scanner] TYPE DU QR SCANNÉ:', qrData.type);
        console.log('[Scanner] ========================================');

        // ═══════════════════════════════════════════════════════
        // LOGIQUE DE ROUTAGE STRICTE
        // ═══════════════════════════════════════════════════════

        // ═══════════════════════════════════════════════════════
        // ROUTAGE STRICT : TYPE du QR détermine le traitement
        // ═══════════════════════════════════════════════════════

        if (qrData.type === 'localisation') {
            // C'est un QR de LOCALISATION (porte de bureau)
            console.log('[Scanner] ✓ QR de LOCALISATION (porte) détecté');
            
            if (this.currentMode === 'localisation') {
                console.log('[Scanner] ✓ Mode LOCALISATION actif → OK pour démarrer bureau');
                console.log('[Scanner] → Traitement LOCALISATION');
                await this.handleLocalisationScan(qrData);
            } else {
                console.warn('[Scanner] ⚠️ Un bureau est déjà actif');
                showToast('⚠️ Terminez d\'abord le bureau en cours', 'warning');
                setTimeout(() => this.start(), 2000);
            }
            
        } else if (qrData.type === 'bien') {
            // C'est un QR de BIEN (équipement)
            console.log('[Scanner] ✓ QR de BIEN (équipement) détecté');
            
            if (this.currentMode === 'bien') {
                console.log('[Scanner] ✓ Mode BIEN actif → OK pour scanner bien');
                console.log('[Scanner] → Traitement BIEN');
                await this.handleBienScan(qrData);
            } else {
                console.error('[Scanner] ❌ Aucun bureau actif !');
                console.log('[Scanner] → REFUS : Scanner d\'abord une PORTE');
                showToast('❌ Scannez d\'abord le QR code de la PORTE du bureau', 'error', 5000);
                playSound('error');
                setTimeout(() => this.start(), 3000);
            }
            
        } else {
            console.error('[Scanner] ❌ Type de QR non reconnu:', qrData.type);
            showToast('QR code non reconnu', 'error');
            setTimeout(() => this.start(), 2000);
        }

        console.log('[Scanner] ========== FIN TRAITEMENT QR ==========');
    }

    /**
     * Callback appelé lors d'une erreur de scan
     * @param {string} errorMessage - Message d'erreur
     */
    onScanError(errorMessage) {
        // Ignorer les erreurs de scan normales (pas de QR détecté)
        // Ces erreurs sont fréquentes et normales pendant le scan
        // console.log('[Scanner] Scan error:', errorMessage);
    }

    /**
     * Traite le scan d'une localisation
     * @param {Object} qrData - Données du QR code
     */
    async handleLocalisationScan(qrData) {
        console.log('[Scanner] ========== SCAN LOCALISATION ==========');
        console.log('[Scanner] Données QR:', qrData);

        try {
            // Validation des données d'entrée
            if (!qrData || (!qrData.code && !qrData.id)) {
                console.error('[Scanner] ✗ Données QR code invalides:', qrData);
                console.error('[Scanner] Le QR code doit contenir au moins "code" ou "id"');
                showToast('QR code invalide', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            // Validation de l'inventaire
            if (!AppState.inventaire || !AppState.inventaire.id) {
                console.error('[Scanner] ✗ Aucun inventaire chargé');
                showToast('Aucun inventaire en cours', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            showToast('Localisation détectée, vérification...', 'info');

            // Récupérer la localisation
            let localisation;
            
            // Cas 1 : QR contient un ID, essayer de récupérer par ID d'abord
            if (qrData.id) {
                console.log('[Scanner] Recherche par ID:', qrData.id);
                try {
                    localisation = await API.getLocalisation(qrData.id);
                    console.log('[Scanner] Localisation trouvée par ID:', localisation);
                } catch (error) {
                    console.warn('[Scanner] Localisation non trouvée par ID, tentative par code...');
                    console.warn('[Scanner] Erreur:', error.message);
                }
            }

            // Cas 2 : QR contient un code OU fallback si ID non trouvé
            if (!localisation && qrData.code) {
                console.log('[Scanner] Recherche par code:', qrData.code);
                try {
                    localisation = await API.getLocalisationByCode(qrData.code);
                    console.log('[Scanner] Localisation trouvée par code:', localisation);
                } catch (error) {
                    console.error('[Scanner] ✗ Erreur récupération localisation par code:', error);
                    console.error('[Scanner] Message:', error.message);
                }
            }

            if (!localisation || !localisation.id) {
                console.error('[Scanner] ✗ Localisation non trouvée');
                console.error('[Scanner] Données QR reçues:', qrData);
                showToast('Localisation non trouvée. Vérifiez le code scanné.', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            console.log('[Scanner] ✓ Localisation trouvée:', localisation.code, '(ID:', localisation.id, ')');

            // Vérifier que cette localisation est assignée à l'agent
            console.log('[Scanner] Vérification assignation...');
            console.log('[Scanner] Inventaire ID:', AppState.inventaire.id);
            
            let mesLocalisations;
            try {
                mesLocalisations = await API.getMesLocalisations(AppState.inventaire.id);
                console.log('[Scanner] Mes localisations:', mesLocalisations);
            } catch (error) {
                console.error('[Scanner] ✗ Erreur récupération localisations assignées:', error);
                showToast('Erreur lors de la vérification des assignations', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            if (!Array.isArray(mesLocalisations)) {
                console.error('[Scanner] ✗ Format de réponse invalide pour mesLocalisations:', mesLocalisations);
                showToast('Erreur de format de données', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            const isAssigned = mesLocalisations.some(loc => loc.localisation_id === localisation.id);
            console.log('[Scanner] Est assigné:', isAssigned);

            if (!isAssigned) {
                console.error('[Scanner] ✗ Localisation non assignée à cet agent');
                showToast('Cette localisation ne vous est pas assignée', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            // Démarrer le scan de cette localisation
            console.log('[Scanner] Démarrage scan localisation...');
            console.log('[Scanner] Inventaire ID:', AppState.inventaire.id);
            console.log('[Scanner] Localisation ID:', localisation.id);
            
            console.log('[Scanner] Démarrage scan localisation...');
            const response = await API.demarrerLocalisation(
                AppState.inventaire.id, 
                localisation.id
            );
            console.log('[Scanner] Réponse API complète:', response);
            
            // ✅ VALIDATION CORRECTE : Vérifier que inventaire_localisation existe dans la réponse
            if (!response || !response.inventaire_localisation) {
                console.error('[Scanner] ✗ Réponse API invalide:', response);
                
                // Gérer les cas spécifiques
                if (response?.statut === 'termine') {
                    showToast('❌ Ce bureau a déjà été inventorié', 'error');
                } else if (response?.message) {
                    showToast('❌ ' + response.message, 'error');
                } else {
                    showToast('❌ Erreur lors du démarrage du bureau', 'error');
                }
                
                setTimeout(() => this.start(), 2000);
                return;
            }
            
            // ✅ Extraire l'objet inventaire_localisation de la réponse
            const inventaireLocalisation = response.inventaire_localisation;
            console.log('[Scanner] ✓ Inventaire localisation valide:', inventaireLocalisation);
            
            // Vérifier que l'objet contient un ID
            if (!inventaireLocalisation.id) {
                console.error('[Scanner] ✗ Inventaire localisation sans ID:', inventaireLocalisation);
                showToast('❌ Données invalides reçues du serveur', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }
            
            console.log('[Scanner] ✓ Bureau démarré (ID:', inventaireLocalisation.id, ')');

            // Charger les biens attendus
            console.log('[Scanner] Chargement biens attendus...');
            let biens;
            try {
                biens = await API.getBiensLocalisation(localisation.id);
                console.log('[Scanner] ✓ Biens chargés:', biens?.length || 0, 'biens');
            } catch (error) {
                console.error('[Scanner] Erreur récupération biens:', error);
                // Continuer même si les biens ne peuvent pas être chargés
                biens = [];
                showToast('Attention: impossible de charger les biens', 'warning');
            }

            // Mettre à jour le state
            AppState.activeLocation = inventaireLocalisation;
            AppState.localisation = localisation;
            AppState.biensAttendus = Array.isArray(biens) ? biens : [];
            AppState.scansSession = [];
            console.log('[Scanner] State mis à jour');

            // Mettre en cache
            try {
                await dbManager.cacheBiens(AppState.biensAttendus);
                console.log('[Scanner] Biens mis en cache');
            } catch (error) {
                console.error('[Scanner] Erreur mise en cache biens:', error);
                // Ne pas bloquer le workflow si le cache échoue
            }

            // Sauvegarder dans localStorage
            try {
                localStorage.setItem(CONFIG.STORAGE_KEY_LOCATION, JSON.stringify({
                    activeLocation: inventaireLocalisation,
                    localisation: localisation,
                    biens: AppState.biensAttendus
                }));
                console.log('[Scanner] Données sauvegardées dans localStorage');
            } catch (error) {
                console.error('[Scanner] Erreur sauvegarde localStorage:', error);
                // Ne pas bloquer le workflow si localStorage échoue
            }

            // Changer le mode en 'bien'
            this.currentMode = 'bien';
            console.log('[Scanner] Mode changé en: bien');

            // ✨ NOUVEAU : Afficher l'indicateur de mode BIEN
            showModeBien(
                localisation,
                biens.length,
                0  // Aucun bien scanné pour l'instant
            );

            // Mettre à jour l'UI
            updateActiveLocationUI();
            
            // Message de succès
            showToast(`✓ Bureau activé : ${localisation.code}`, 'success');
            playSound('success');
            showToast(`✓ Bureau activé : ${localisation.code}`, 'success');
            playSound('success');

            // Redémarrer le scanner pour les biens
            setTimeout(() => this.start(), 1500);

            console.log('[Scanner] ========== LOCALISATION ACTIVÉE ==========');

        } catch (error) {
            console.error('[Scanner] ========== ERREUR SCAN LOCALISATION ==========');
            console.error('[Scanner] Erreur:', error);
            console.error('[Scanner] Message:', error.message);
            console.error('[Scanner] Stack:', error.stack);
            
            // Message spécifique selon le type d'erreur
            let message = error.message || 'Erreur lors du scan';
            
            if (error.message && error.message.includes("n'est pas en cours")) {
                message = "❌ L'inventaire n'est pas actif. Contactez l'administrateur.";
            } else if (error.message && error.message.includes("non assignée")) {
                message = "⚠️ Cette localisation ne vous est pas assignée.";
            } else if (error.message && error.message.includes("non trouvée")) {
                message = "❌ Localisation introuvable.";
            }
            
            showToast(message, 'error');
            setTimeout(() => this.start(), 2000);
        }
    }

    /**
     * Traite le scan d'un bien
     * @param {Object} qrData - Données du QR code
     */
    async handleBienScan(qrData) {
        console.log('[Scanner] ========== SCAN BIEN ==========');
        console.log('[Scanner] Données QR:', qrData);

        try {
            // Vérifier qu'un bureau est actif
            if (!AppState.activeLocation) {
                console.error('[Scanner] ✗ Aucun bureau actif');
                showToast('Scannez d\'abord un bureau', 'warning');
                this.currentMode = 'localisation';
                setTimeout(() => this.start(), 2000);
                return;
            }

            showToast('Bien détecté, chargement...', 'info');

            // Récupérer le bien
            console.log('[Scanner] Récupération bien...');
            let bien;
            
            if (qrData.id) {
                console.log('[Scanner] Appel API getBien avec ID:', qrData.id);
                try {
                    bien = await API.getBien(qrData.id);
                    console.log('[Scanner] Bien reçu:', bien);
                } catch (error) {
                    console.warn('[Scanner] Erreur récupération bien depuis API:', error);
                    // Si offline, essayer depuis le cache
                    if (!navigator.onLine) {
                        try {
                            bien = await dbManager.getCachedBien(qrData.id);
                            console.log('[Scanner] Bien récupéré depuis cache:', bien?.id);
                        } catch (cacheError) {
                            console.error('[Scanner] Erreur récupération depuis cache:', cacheError);
                        }
                    }
                }
            } else if (qrData.code) {
                console.log('[Scanner] Appel API getBienByCode avec code:', qrData.code);
                try {
                    bien = await API.getBienByCode(qrData.code);
                    console.log('[Scanner] Bien reçu par code:', bien);
                } catch (error) {
                    console.warn('[Scanner] Erreur récupération bien par code:', error);
                }
            }

            // Si toujours pas de bien, essayer le cache même en ligne (fallback)
            if (!bien && qrData.id) {
                try {
                    bien = await dbManager.getCachedBien(qrData.id);
                    console.log('[Scanner] Bien récupéré depuis cache (fallback):', bien?.id);
                } catch (cacheError) {
                    console.error('[Scanner] Erreur récupération cache (fallback):', cacheError);
                }
            }

            if (!bien || !bien.id) {
                console.error('[Scanner] ✗ Bien non trouvé');
                showToast('Bien non trouvé', 'error');
                setTimeout(() => this.start(), 2000);
                return;
            }

            // Vérifier si déjà scanné dans cet inventaire
            const dejaScan = AppState.scansSession.some(s => s.bien_id === bien.id);
            console.log('[Scanner] Déjà scanné:', dejaScan);
            
            if (dejaScan) {
                console.warn('[Scanner] ⚠ Bien déjà scanné');
                showToast('Ce bien a déjà été scanné', 'warning');
                playSound('warning');
                if (navigator.vibrate) {
                    navigator.vibrate([100, 50, 100, 50, 100]);
                }
                setTimeout(() => this.start(), 2000);
                return;
            }

            // Afficher la fiche du bien et les boutons d'action
            console.log('[Scanner] Affichage fiche bien...');
            displayBienResult(bien);

            console.log('[Scanner] ========== BIEN DÉTECTÉ ==========');

        } catch (error) {
            console.error('[Scanner] ========== ERREUR SCAN BIEN ==========');
            console.error('[Scanner] Erreur:', error);
            console.error('[Scanner] Message:', error.message);
            console.error('[Scanner] Stack:', error.stack);
            showToast(error.message || 'Erreur lors du scan', 'error');
            setTimeout(() => this.start(), 2000);
        }
    }

    /**
     * Met à jour l'interface du scanner selon son état
     * @param {string} status - État du scanner ('scanning', 'stopped', 'error')
     */
    updateScannerUI(status) {
        const statusDiv = document.getElementById('scanner-status');
        const messageDiv = document.getElementById('scanner-message');

        if (!statusDiv || !messageDiv) {
            return;
        }

        switch (status) {
            case 'scanning':
                statusDiv.innerHTML = '<p class="text-green-600 font-semibold">📷 Caméra active</p>';
                messageDiv.textContent = this.currentMode === 'localisation' 
                    ? 'Pointez vers le QR code d\'un bureau' 
                    : 'Pointez vers le QR code d\'un bien';
                break;

            case 'stopped':
                statusDiv.innerHTML = `
                    <button id="start-scanner-btn" class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        📷 Activer la caméra
                    </button>
                `;
                messageDiv.textContent = 'Prêt à scanner';
                // Re-attacher l'event listener
                const startBtn = document.getElementById('start-scanner-btn');
                if (startBtn) {
                    startBtn.addEventListener('click', () => this.start());
                }
                break;

            case 'error':
                statusDiv.innerHTML = `
                    <button id="start-scanner-btn" class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                        🔄 Réessayer
                    </button>
                `;
                messageDiv.textContent = 'Erreur caméra';
                const retryBtn = document.getElementById('start-scanner-btn');
                if (retryBtn) {
                    retryBtn.addEventListener('click', () => this.start());
                }
                break;
        }
    }
}

// Instance globale du gestionnaire de scanner
const scannerManager = new ScannerManager();

// ============================================
// UI UPDATES
// ============================================

/**
 * Afficher l'indicateur de mode LOCALISATION
 */
function showModeLocalisation() {
    console.log('[UI] Affichage mode LOCALISATION');
    
    const modeLocDiv = document.getElementById('mode-localisation');
    const modeBienDiv = document.getElementById('mode-bien');
    
    if (modeLocDiv) {
        modeLocDiv.classList.remove('hidden');
    }
    
    if (modeBienDiv) {
        modeBienDiv.classList.add('hidden');
    }
}

/**
 * Afficher l'indicateur de mode BIEN
 */
function showModeBien(localisation, biensAttendus, biensScannés) {
    console.log('[UI] Affichage mode BIEN');
    
    const modeLocDiv = document.getElementById('mode-localisation');
    const modeBienDiv = document.getElementById('mode-bien');
    
    if (modeLocDiv) {
        modeLocDiv.classList.add('hidden');
    }
    
    if (modeBienDiv) {
        modeBienDiv.classList.remove('hidden');
    }
    
    // Mettre à jour le nom du bureau (format simplifié)
    const bureauName = document.getElementById('bureau-actif-name');
    if (bureauName && localisation) {
        bureauName.textContent = `📦 Scannez les biens de ${localisation.code}`;
    }
    
    // Mettre à jour la progression
    updateProgressIndicator(biensAttendus, biensScannés);
}

/**
 * Mettre à jour la barre de progression
 */
function updateProgressIndicator(total, scanned) {
    const progressText = document.getElementById('progress-text');
    const progressPercent = document.getElementById('progress-percent');
    const progressBar = document.getElementById('progress-bar');
    
    const percent = total > 0 ? Math.round((scanned / total) * 100) : 0;
    
    if (progressText) {
        progressText.textContent = `${scanned}/${total} biens`;
    }
    
    if (progressPercent) {
        progressPercent.textContent = `${percent}%`;
    }
    
    if (progressBar) {
        progressBar.style.width = `${percent}%`;
    }
}

// Exposer globalement
window.showModeLocalisation = showModeLocalisation;
window.showModeBien = showModeBien;
window.updateProgressIndicator = updateProgressIndicator;

/**
 * Met à jour l'interface de la localisation active
 */
function updateActiveLocationUI() {
    const stateNoLocation = document.getElementById('state-no-location');
    const stateActiveLocation = document.getElementById('state-active-location');
    const progressBar = document.getElementById('progress-bar');

    if (!AppState.activeLocation || !AppState.localisation) {
        // Aucun bureau actif
        if (stateNoLocation) stateNoLocation.classList.remove('hidden');
        if (stateActiveLocation) stateActiveLocation.classList.add('hidden');
        if (progressBar) progressBar.classList.add('hidden');
        return;
    }

    // Bureau actif
    if (stateNoLocation) stateNoLocation.classList.add('hidden');
    if (stateActiveLocation) stateActiveLocation.classList.remove('hidden');
    if (progressBar) progressBar.classList.remove('hidden');

    // Infos localisation
    const locationName = document.getElementById('active-location-name');
    const locationDetails = document.getElementById('active-location-details');
    const locationProgress = document.getElementById('active-location-progress');
    const progressText = document.getElementById('progress-text');
    const currentLocationName = document.getElementById('current-location-name');
    const progressFill = document.getElementById('progress-fill');

    if (locationName) {
        locationName.textContent = `${AppState.localisation.code} - ${AppState.localisation.designation}`;
    }
    
    if (locationDetails) {
        locationDetails.textContent = 
            `${AppState.localisation.batiment || ''} ${AppState.localisation.etage ? 'Étage ' + AppState.localisation.etage : ''}`.trim();
    }

    // Progression
    const scanned = AppState.activeLocation.nombre_biens_scannes || 0;
    const total = AppState.activeLocation.nombre_biens_attendus || 0;
    const percentage = total > 0 ? Math.round((scanned / total) * 100) : 0;

    // Mettre à jour la progression dans la carte du bureau actif
    if (progressText) {
        progressText.textContent = `${scanned}/${total} biens`;
    }
    if (progressPercent) {
        const percent = total > 0 ? Math.round((scanned / total) * 100) : 0;
        progressPercent.textContent = `${percent}%`;
    }
    if (progressBar) {
        const percent = total > 0 ? Math.round((scanned / total) * 100) : 0;
        progressBar.style.width = `${percent}%`;
    }
    if (progressText) {
        progressText.textContent = `${scanned}/${total}`;
    }
    if (currentLocationName) {
        currentLocationName.textContent = AppState.localisation.code;
    }
    if (progressFill) {
        progressFill.style.width = `${percentage}%`;
    }
}

/**
 * Affiche le résultat du scan d'un bien avec les actions possibles
 * @param {Object} bien - Données du bien scanné
 */
function displayBienResult(bien) {
    const resultDiv = document.getElementById('scan-result');
    
    if (!resultDiv) {
        console.warn('[UI] Élément scan-result introuvable');
        return;
    }

    // Vérifier si le bien est dans la bonne localisation
    const isCorrectLocation = bien.localisation_id === AppState.localisation.id;
    const statusClass = isCorrectLocation ? 'bg-green-50 border-green-200' : 'bg-orange-50 border-orange-200';
    const statusIcon = isCorrectLocation ? '✓' : '⚠️';
    const statusText = isCorrectLocation ? 'Localisation conforme' : 'Bien déplacé !';
    const statusColor = isCorrectLocation ? 'text-green-700' : 'text-orange-700';

    resultDiv.innerHTML = `
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-2xl">${statusIcon}</span>
                        <span class="font-semibold ${statusColor}">${statusText}</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">${bien.designation || 'N/A'}</h3>
                    <p class="text-gray-600 text-sm">${bien.code_inventaire || 'N/A'}</p>
                </div>
            </div>

            <!-- Infos bien -->
            <div class="${statusClass} border rounded-lg p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Nature</span>
                    <span class="font-medium">${bien.nature || 'N/A'}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Localisation prévue</span>
                    <span class="font-medium">${bien.localisation?.code || '-'}</span>
                </div>
                ${!isCorrectLocation ? `
                <div class="flex items-center justify-between border-t pt-2 mt-2">
                    <span class="text-sm text-gray-600">Localisation actuelle</span>
                    <span class="font-medium text-orange-700">${AppState.localisation.code}</span>
                </div>
                ` : ''}
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Service</span>
                    <span class="font-medium">${bien.service_usager || '-'}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Valeur</span>
                    <span class="font-medium">${(bien.valeur_acquisition || 0).toLocaleString('fr-FR')} MRU</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <button onclick="enregistrerScan('${bien.id}', 'present')" 
                        class="action-button-large btn-success">
                    <span>✓</span>
                    <span>PRÉSENT</span>
                </button>

                ${!isCorrectLocation ? `
                <button onclick="enregistrerScan('${bien.id}', 'deplace')" 
                        class="action-button-large btn-warning">
                    <span>⚠️</span>
                    <span>CONFIRMER DÉPLACEMENT</span>
                </button>
                ` : `
                <button onclick="enregistrerScan('${bien.id}', 'deplace')" 
                        class="action-button-large btn-warning">
                    <span>⚠️</span>
                    <span>DÉPLACÉ</span>
                </button>
                `}

                <button onclick="enregistrerScan('${bien.id}', 'absent')" 
                        class="action-button-large btn-danger">
                    <span>✗</span>
                    <span>ABSENT</span>
                </button>

                <button onclick="annulerScan()" 
                        class="w-full bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition">
                    Annuler
                </button>
            </div>
        </div>
    `;

    resultDiv.classList.remove('hidden');
}

/**
 * Enregistre un scan de bien avec le statut spécifié
 * @param {string|number} bienId - ID du bien
 * @param {string} statut - Statut du scan ('present', 'deplace', 'absent', 'deteriore')
 */
async function enregistrerScan(bienId, statut) {
    try {
        console.log('[Scan] Enregistrement scan - Bien ID:', bienId, 'Statut:', statut);
        
        // Validation des données d'entrée
        if (!bienId) {
            console.error('[Scan] ID bien manquant');
            showToast('ID du bien manquant', 'error');
            return;
        }

        if (!statut || !['present', 'deplace', 'absent', 'deteriore'].includes(statut)) {
            console.error('[Scan] Statut invalide:', statut);
            showToast('Statut de scan invalide', 'error');
            return;
        }

        // Validation de l'état de l'application
        if (!AppState.inventaire || !AppState.inventaire.id) {
            console.error('[Scan] Aucun inventaire chargé');
            showToast('Aucun inventaire en cours', 'error');
            return;
        }

        if (!AppState.activeLocation || !AppState.activeLocation.id) {
            console.error('[Scan] Aucun bureau actif');
            showToast('Aucun bureau actif', 'error');
            return;
        }

        if (!AppState.localisation || !AppState.localisation.id) {
            console.error('[Scan] Aucune localisation chargée');
            showToast('Localisation non chargée', 'error');
            return;
        }

        if (!AppState.user || !AppState.user.id) {
            console.error('[Scan] Utilisateur non authentifié');
            showToast('Session expirée, veuillez vous reconnecter', 'error');
            return;
        }

        // Préparer les données du scan
        const scanData = {
            inventaire_id: AppState.inventaire.id,
            inventaire_localisation_id: AppState.activeLocation.id,
            bien_id: parseInt(bienId),
            statut_scan: statut,
            localisation_reelle_id: AppState.localisation.id,
            etat_constate: 'bon', // TODO: demander à l'utilisateur
            commentaire: null,
            photo_path: null,
            user_id: AppState.user.id
        };

        console.log('[Scan] Données du scan préparées:', scanData);

        let scanResult;
        let scanEnregistre = false;

        if (navigator.onLine) {
            try {
                console.log('[Scan] Mode online - Envoi à l\'API...');
                // Mode online : envoyer directement à l'API
                scanResult = await API.enregistrerScan(AppState.inventaire.id, scanData);
                console.log('[Scan] Scan enregistré avec succès:', scanResult);
                scanEnregistre = true;
                showToast('Scan enregistré', 'success');
                
                // Vibration de succès
                if (navigator.vibrate) {
                    navigator.vibrate(100);
                }
            } catch (error) {
                // Si erreur API, basculer en mode offline
                console.warn('[Scan] Erreur API, basculement en mode offline:', error);
                console.warn('[Scan] Détails erreur:', error.message, error.stack);
                
                try {
                    await dbManager.addPendingScan({
                        inventaire_id: AppState.inventaire.id,
                        data: scanData
                    });
                    console.log('[Scan] Scan ajouté à la file d\'attente');
                    showToast('Scan enregistré (hors ligne)', 'warning');
                    scanEnregistre = true;
                } catch (dbError) {
                    console.error('[Scan] Erreur ajout file d\'attente:', dbError);
                    showToast('Erreur lors de l\'enregistrement', 'error');
                    return;
                }
            }
        } else {
            console.log('[Scan] Mode offline - Ajout à la file d\'attente...');
            // Mode offline : ajouter à la file d'attente
            try {
                await dbManager.addPendingScan({
                    inventaire_id: AppState.inventaire.id,
                    data: scanData
                });
                console.log('[Scan] Scan ajouté à la file d\'attente');
                showToast('Scan enregistré (hors ligne)', 'warning');
                scanEnregistre = true;
                
                // Vibration différente pour offline
                if (navigator.vibrate) {
                    navigator.vibrate([50, 50, 50]);
                }
            } catch (dbError) {
                console.error('[Scan] Erreur ajout file d\'attente:', dbError);
                showToast('Erreur lors de l\'enregistrement', 'error');
                return;
            }
        }

        // Si le scan n'a pas été enregistré, ne pas continuer
        if (!scanEnregistre) {
            console.error('[Scan] Scan non enregistré, arrêt du traitement');
            return;
        }

        // Mettre à jour le state local
        try {
            if (AppState.activeLocation) {
                AppState.activeLocation.nombre_biens_scannes = (AppState.activeLocation.nombre_biens_scannes || 0) + 1;
                console.log('[Scan] Nombre de biens scannés mis à jour:', AppState.activeLocation.nombre_biens_scannes);
            }
            AppState.scansSession.push(scanData);
            console.log('[Scan] Scan ajouté à la session');
            
            // ✨ NOUVEAU : Mettre à jour la progression
            updateProgressIndicator(
                AppState.biensAttendus.length,
                AppState.activeLocation.nombre_biens_scannes
            );
        } catch (stateError) {
            console.error('[Scan] Erreur mise à jour state:', stateError);
            // Ne pas bloquer si la mise à jour du state échoue
        }

        // Ajouter à l'historique local
        try {
            const bien = await dbManager.getCachedBien(bienId);
            await dbManager.addToHistory({
                ...scanData,
                bien: bien
            });
            console.log('[Scan] Scan ajouté à l\'historique');
        } catch (historyError) {
            console.error('[Scan] Erreur ajout historique:', historyError);
            // Ne pas bloquer si l'historique échoue
        }

        // Mettre à jour l'UI
        try {
            updateActiveLocationUI();
            updateHistoryUI();
            await SyncManager.updatePendingScansBadge();
            console.log('[Scan] UI mise à jour');
        } catch (uiError) {
            console.error('[Scan] Erreur mise à jour UI:', uiError);
            // Ne pas bloquer si l'UI échoue
        }

        // Cacher le résultat et redémarrer le scanner
        try {
            const resultDiv = document.getElementById('scan-result');
            if (resultDiv) {
                resultDiv.classList.add('hidden');
            }
            setTimeout(() => {
                try {
                    scannerManager.start();
                    console.log('[Scan] Scanner redémarré');
                } catch (startError) {
                    console.error('[Scan] Erreur redémarrage scanner:', startError);
                }
            }, 500);
        } catch (uiError) {
            console.error('[Scan] Erreur masquage résultat:', uiError);
        }

    } catch (error) {
        console.error('[Scan] Erreur critique enregistrement scan:', error);
        console.error('[Scan] Stack trace:', error.stack);
        showToast(error.message || 'Erreur lors de l\'enregistrement du scan', 'error');
    }
}

/**
 * Annule le scan en cours et redémarre le scanner
 */
function annulerScan() {
    const resultDiv = document.getElementById('scan-result');
    if (resultDiv) {
        resultDiv.classList.add('hidden');
    }
    scannerManager.start();
}

/**
 * Affiche la modal de confirmation pour terminer un bureau
 */
function showTerminerBureauModal() {
    if (!AppState.activeLocation || !AppState.localisation) {
        return;
    }

    // Calculer les statistiques
    const scanned = AppState.activeLocation.nombre_biens_scannes || 0;
    const total = AppState.activeLocation.nombre_biens_attendus || 0;
    const nonScannes = total - scanned;

    // Compter les statuts des scans
    const stats = {
        present: AppState.scansSession.filter(s => s.statut_scan === 'present').length,
        deplace: AppState.scansSession.filter(s => s.statut_scan === 'deplace').length,
        absent: AppState.scansSession.filter(s => s.statut_scan === 'absent').length
    };

    // Créer la modal
    const modalHTML = `
        <div id="modal-terminer-bureau" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="text-xl font-bold text-gray-800">Terminer le bureau</h3>
                    <button onclick="closeTerminerBureauModal()" class="modal-close" aria-label="Fermer">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">
                            Vous êtes sur le point de terminer le scan de :
                        </p>
                        <p class="text-lg font-semibold text-indigo-600">
                            ${AppState.localisation.code} - ${AppState.localisation.designation}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Récapitulatif des scans</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">✓ Présents</span>
                                <span class="font-semibold text-green-600">${stats.present}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">⚠️ Déplacés</span>
                                <span class="font-semibold text-orange-600">${stats.deplace}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">✗ Absents</span>
                                <span class="font-semibold text-red-600">${stats.absent}</span>
                            </div>
                            <div class="border-t border-gray-300 pt-2 mt-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-medium">Total scannés</span>
                                    <span class="font-bold text-gray-800">${scanned}/${total}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    ${nonScannes > 0 ? `
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-orange-800 mb-1">
                                    ${nonScannes} bien(s) non scanné(s)
                                </p>
                                <p class="text-sm text-orange-700">
                                    Certains biens attendus dans ce bureau n'ont pas été scannés.
                                </p>
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    <div class="mb-4">
                        <label class="flex items-start space-x-2 cursor-pointer">
                            <input type="checkbox" id="mark-non-scanned-absent" class="mt-1">
                            <span class="text-sm text-gray-700">
                                Marquer les biens non scannés comme absents
                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button onclick="closeTerminerBureauModal()" 
                            class="btn-secondary">
                        Annuler
                    </button>
                    <button onclick="confirmTerminerBureau()" 
                            class="btn-primary">
                        Terminer le bureau
                    </button>
                </div>
            </div>
        </div>
    `;

    // Ajouter la modal au body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer.firstElementChild);

    // Animation d'entrée
    setTimeout(() => {
        const modal = document.getElementById('modal-terminer-bureau');
        if (modal) {
            modal.classList.add('modal-show');
        }
    }, 10);
}

/**
 * Ferme la modal de terminer bureau
 */
function closeTerminerBureauModal() {
    const modal = document.getElementById('modal-terminer-bureau');
    if (modal) {
        modal.classList.remove('modal-show');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

/**
 * Confirme et exécute la terminaison du bureau
 */
async function confirmTerminerBureau() {
    try {
        console.log('[Scan] Confirmation terminer bureau...');
        
        // Validation de l'état
        if (!AppState.activeLocation || !AppState.activeLocation.id) {
            console.warn('[Scan] Aucun bureau actif');
            showToast('Aucun bureau actif', 'warning');
            return;
        }

        if (!AppState.inventaire || !AppState.inventaire.id) {
            console.error('[Scan] Aucun inventaire chargé');
            showToast('Aucun inventaire en cours', 'error');
            return;
        }

        if (!AppState.localisation || !AppState.localisation.id) {
            console.error('[Scan] Aucune localisation chargée');
            showToast('Localisation non chargée', 'error');
            return;
        }

        const markNonScannedAbsent = document.getElementById('mark-non-scanned-absent')?.checked || false;
        const scanned = AppState.activeLocation.nombre_biens_scannes || 0;
        const total = AppState.activeLocation.nombre_biens_attendus || 0;
        const nonScannes = total - scanned;

        console.log('[Scan] Statistiques - Scannés:', scanned, 'Total:', total, 'Non scannés:', nonScannes);
        console.log('[Scan] Marquer non-scannés comme absents:', markNonScannedAbsent);

        // Désactiver le bouton pendant le traitement
        const confirmBtn = document.querySelector('#modal-terminer-bureau .btn-primary');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Traitement...';
        }

        // Si demandé, marquer les non-scannés comme absents
        if (markNonScannedAbsent && nonScannes > 0) {
            console.log('[Scan] Marquage des biens non-scannés comme absents...');
            const biensNonScannes = AppState.biensAttendus.filter(bien => {
                return !AppState.scansSession.some(scan => scan.bien_id === bien.id);
            });

            console.log('[Scan] Nombre de biens à marquer absents:', biensNonScannes.length);

            for (const bien of biensNonScannes) {
                try {
                    const scanData = {
                        inventaire_id: AppState.inventaire.id,
                        inventaire_localisation_id: AppState.activeLocation.id,
                        bien_id: bien.id,
                        statut_scan: 'absent',
                        localisation_reelle_id: AppState.localisation.id,
                        etat_constate: 'bon',
                        commentaire: 'Marqué absent automatiquement lors de la fermeture du bureau',
                        user_id: AppState.user.id
                    };

                    if (navigator.onLine) {
                        try {
                            await API.enregistrerScan(AppState.inventaire.id, scanData);
                            console.log('[Scan] Bien marqué absent:', bien.id);
                        } catch (error) {
                            console.error('[Scan] Erreur marquage absent pour bien', bien.id, ':', error);
                            // En cas d'erreur, ajouter à la file d'attente
                            try {
                                await dbManager.addPendingScan({
                                    inventaire_id: AppState.inventaire.id,
                                    data: scanData
                                });
                                console.log('[Scan] Bien ajouté à la file d\'attente:', bien.id);
                            } catch (dbError) {
                                console.error('[Scan] Erreur ajout file d\'attente:', dbError);
                                // Continuer avec les autres biens même en cas d'erreur
                            }
                        }
                    } else {
                        try {
                            await dbManager.addPendingScan({
                                inventaire_id: AppState.inventaire.id,
                                data: scanData
                            });
                            console.log('[Scan] Bien ajouté à la file d\'attente (offline):', bien.id);
                        } catch (dbError) {
                            console.error('[Scan] Erreur ajout file d\'attente (offline):', dbError);
                            // Continuer avec les autres biens même en cas d'erreur
                        }
                    }
                } catch (bienError) {
                    console.error('[Scan] Erreur traitement bien', bien.id, ':', bienError);
                    // Continuer avec les autres biens même en cas d'erreur
                }
            }
        }

        // Terminer la localisation
        console.log('[Scan] Terminaison de la localisation...');
        try {
            await API.terminerLocalisation(
                AppState.inventaire.id,
                AppState.activeLocation.id
            );
            console.log('[Scan] Localisation terminée avec succès');
        } catch (error) {
            console.error('[Scan] Erreur terminaison localisation:', error);
            // Si offline, on peut quand même continuer (la sync se fera plus tard)
            if (!navigator.onLine) {
                console.warn('[Scan] Mode offline, terminaison reportée');
                showToast('Terminaison reportée (hors ligne)', 'warning');
            } else {
                throw error; // Relancer l'erreur si on est en ligne
            }
        }

        // Fermer la modal
        closeTerminerBureauModal();

        // Vibration de succès
        if (navigator.vibrate) {
            navigator.vibrate([100, 50, 100]);
        }

        showToast(`✓ ${AppState.localisation.code} terminé ! Passez au bureau suivant`, 'success');

        // Réinitialiser le state
        const localisationCode = AppState.localisation?.code || 'Bureau';
        AppState.activeLocation = null;
        AppState.localisation = null;
        AppState.biensAttendus = [];
        AppState.scansSession = [];
        
        try {
            localStorage.removeItem(CONFIG.STORAGE_KEY_LOCATION);
            console.log('[Scan] Données localisation supprimées de localStorage');
        } catch (storageError) {
            console.error('[Scan] Erreur suppression localStorage:', storageError);
        }

        // Changer le mode
        scannerManager.currentMode = 'localisation';
        console.log('[Scan] Mode changé en "localisation"');

        // Afficher l'indicateur de mode localisation
        showModeLocalisation();

        // Mettre à jour l'UI
        try {
            updateActiveLocationUI();
            updateHistoryUI();
            await SyncManager.updatePendingScansBadge();
            console.log('[Scan] UI mise à jour');
        } catch (uiError) {
            console.error('[Scan] Erreur mise à jour UI:', uiError);
        }

        // Redémarrer le scanner
        try {
            await scannerManager.stop();
            setTimeout(() => {
                try {
                    scannerManager.start();
                    console.log('[Scan] Scanner redémarré');
                } catch (startError) {
                    console.error('[Scan] Erreur redémarrage scanner:', startError);
                }
            }, 1000);
        } catch (stopError) {
            console.error('[Scan] Erreur arrêt scanner:', stopError);
        }

    } catch (error) {
        console.error('[Scan] Erreur critique terminer bureau:', error);
        console.error('[Scan] Stack trace:', error.stack);
        showToast(error.message || 'Erreur lors de la fermeture du bureau', 'error');
        
        // Réactiver le bouton
        const confirmBtn = document.querySelector('#modal-terminer-bureau .btn-primary');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Terminer le bureau';
        }
    }
}

/**
 * Termine le scan de la localisation active (fonction legacy pour compatibilité)
 */
async function terminerBureau() {
    showTerminerBureauModal();
}

/**
 * Met à jour l'indicateur de statut de connexion dans l'interface
 */
function updateOnlineStatusIndicator() {
    const header = document.querySelector('header');
    if (!header) return;

    // Supprimer l'ancien indicateur s'il existe
    const existingIndicator = document.getElementById('online-status-indicator');
    if (existingIndicator) {
        existingIndicator.remove();
    }

    // Créer le nouvel indicateur
    const indicator = document.createElement('div');
    indicator.id = 'online-status-indicator';
    indicator.className = `absolute top-2 right-2 w-3 h-3 rounded-full ${
        navigator.onLine ? 'bg-green-500' : 'bg-red-500'
    } ${navigator.onLine ? 'animate-pulse' : ''}`;
    indicator.title = navigator.onLine ? 'En ligne' : 'Hors ligne';
    indicator.setAttribute('aria-label', navigator.onLine ? 'En ligne' : 'Hors ligne');
    
    header.style.position = 'relative';
    header.appendChild(indicator);
}

/**
 * Met à jour l'interface de l'historique des scans
 */
async function updateHistoryUI() {
    const historyList = document.getElementById('history-list');
    const historyCount = document.getElementById('history-count');

    if (!historyList) {
        return;
    }

    try {
        const history = await dbManager.getHistory(20);
        
        if (historyCount) {
            historyCount.textContent = history.length;
        }

        if (history.length === 0) {
            historyList.innerHTML = `
                <div class="p-8 text-center text-gray-500">
                    <p>Aucun scan effectué</p>
                </div>
            `;
            return;
        }

        const statusColors = {
            present: 'text-green-600 bg-green-50',
            deplace: 'text-orange-600 bg-orange-50',
            absent: 'text-red-600 bg-red-50',
            deteriore: 'text-purple-600 bg-purple-50'
        };

        const statusIcons = {
            present: '✓',
            deplace: '⚠️',
            absent: '✗',
            deteriore: '⚠'
        };

        const statusLabels = {
            present: 'Présent',
            deplace: 'Déplacé',
            absent: 'Absent',
            deteriore: 'Détérioré'
        };

        historyList.innerHTML = history.map(scan => {
            const statusClass = statusColors[scan.statut_scan] || statusColors.present;
            const statusIcon = statusIcons[scan.statut_scan] || statusIcons.present;
            const statusLabel = statusLabels[scan.statut_scan] || 'Inconnu';

            return `
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="font-semibold text-gray-800">${scan.bien?.code_inventaire || 'N/A'}</span>
                                <span class="px-2 py-1 rounded text-xs font-medium ${statusClass}">
                                    ${statusIcon} ${statusLabel}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">${scan.bien?.designation || 'Bien inconnu'}</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDate(scan.timestamp)}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('[UI] Erreur mise à jour historique:', error);
        historyList.innerHTML = `
            <div class="p-8 text-center text-red-500">
                <p>Erreur lors du chargement</p>
            </div>
        `;
    }
}

// ============================================
// EVENT LISTENERS
// ============================================

/**
 * Attache tous les event listeners de l'application
 */
function attachEventListeners() {
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const users = document.getElementById('login-users')?.value;
            const mdp = document.getElementById('login-mdp')?.value;
            const errorDiv = document.getElementById('login-error');
            const loginBtn = document.getElementById('login-btn');
            const loginBtnText = document.getElementById('login-btn-text');
            const loginSpinner = document.getElementById('login-spinner');

            if (!users || !mdp) {
                if (errorDiv) {
                    const errorText = errorDiv.querySelector('p');
                    if (errorText) {
                        errorText.textContent = 'Veuillez remplir tous les champs';
                    } else {
                        errorDiv.textContent = 'Veuillez remplir tous les champs';
                    }
                    errorDiv.classList.remove('hidden');
                }
                return;
            }

            // Désactiver le bouton
            if (loginBtn) loginBtn.disabled = true;
            if (loginBtnText) loginBtnText.textContent = 'Connexion...';
            if (loginSpinner) loginSpinner.classList.remove('hidden');
            if (errorDiv) errorDiv.classList.add('hidden');

            const result = await AuthManager.login(users, mdp);

            if (result.success) {
                await loadInventaire();
                showView('scanner');
                await scannerManager.init();
            } else {
                if (errorDiv) {
                    const errorText = errorDiv.querySelector('p');
                    if (errorText) {
                        errorText.textContent = result.error || 'Erreur de connexion';
                    } else {
                        errorDiv.textContent = result.error || 'Erreur de connexion';
                    }
                    errorDiv.classList.remove('hidden');
                }
                if (loginBtn) loginBtn.disabled = false;
                if (loginBtnText) loginBtnText.textContent = 'Se connecter';
                if (loginSpinner) loginSpinner.classList.add('hidden');
            }
        });
    }

    // Menu toggle
    const menuBtn = document.getElementById('menu-btn');
    const closeMenuBtn = document.getElementById('close-menu');
    const menuOverlay = document.getElementById('menu-overlay');
    const menuDrawer = document.getElementById('menu-drawer');

    if (menuBtn && menuDrawer) {
        menuBtn.addEventListener('click', () => {
            menuDrawer.classList.remove('hidden');
        });
    }

    if (closeMenuBtn && menuDrawer) {
        closeMenuBtn.addEventListener('click', () => {
            menuDrawer.classList.add('hidden');
        });
    }

    if (menuOverlay && menuDrawer) {
        menuOverlay.addEventListener('click', () => {
            menuDrawer.classList.add('hidden');
        });
    }

    // Navigation
    console.log('[App] Attachement event listener pour nav-mes-localisations...');
    const navMesLocalisations = document.getElementById('nav-mes-localisations');
    if (navMesLocalisations) {
        console.log('[App] ✅ Élément nav-mes-localisations trouvé');
        navMesLocalisations.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('[App] ========== Clic sur nav-mes-localisations ==========');
            console.log('[App] Navigation vers Mes Localisations (depuis le menu)');
            
            // Vérifier que la vue existe avant de l'afficher
            const viewElement = document.getElementById('view-mes-localisations');
            if (!viewElement) {
                console.error('[App] ❌ ERREUR: view-mes-localisations n\'existe pas dans le DOM!');
                showToast('Erreur: Vue introuvable', 'error');
                return;
            }
            console.log('[App] ✅ Vue view-mes-localisations trouvée dans le DOM');
            
            showView('mes-localisations');
            
            // Attendre un peu avant de charger les données
            setTimeout(() => {
                loadMesLocalisations();
            }, 100);
            
            if (menuDrawer) {
                menuDrawer.classList.add('hidden');
                console.log('[App] Menu drawer fermé');
            }
            console.log('[App] ========== Fin clic nav-mes-localisations ==========');
        });
        console.log('[App] ✅ Event listener attaché pour nav-mes-localisations');
    } else {
        console.error('[App] ❌ Élément nav-mes-localisations introuvable dans le DOM');
        console.error('[App] Vérifiez que l\'élément existe dans index.html');
    }

    const navHistorique = document.getElementById('nav-historique');
    if (navHistorique) {
        navHistorique.addEventListener('click', (e) => {
            e.preventDefault();
            const toggleHistory = document.getElementById('toggle-history');
            if (toggleHistory) toggleHistory.click();
            if (menuDrawer) menuDrawer.classList.add('hidden');
        });
    }

    const navLogout = document.getElementById('nav-logout');
    if (navLogout) {
        navLogout.addEventListener('click', async () => {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                try {
                    await API.logout();
                } catch (error) {
                    console.error('[Auth] Erreur logout API:', error);
                }
                AuthManager.logout();
            }
        });
    }

    // Scanner
    const startScannerBtn = document.getElementById('start-scanner-btn');
    if (startScannerBtn) {
        startScannerBtn.addEventListener('click', () => {
            scannerManager.start();
        });
    }

    // Terminer bureau
    const btnTerminerBureau = document.getElementById('btn-terminer-bureau');
    if (btnTerminerBureau) {
        btnTerminerBureau.addEventListener('click', () => {
            showTerminerBureauModal();
        });
    }

    // Terminer bureau (bouton dans l'indicateur de mode)
    const btnTerminerBureauTop = document.getElementById('btn-terminer-bureau-top');
    if (btnTerminerBureauTop) {
        btnTerminerBureauTop.addEventListener('click', () => {
            showTerminerBureauModal();
        });
    }

    // Toggle history
    const toggleHistory = document.getElementById('toggle-history');
    if (toggleHistory) {
        toggleHistory.addEventListener('click', () => {
            const content = document.getElementById('history-content');
            const chevron = document.getElementById('history-chevron');
            
            if (content && chevron) {
                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    chevron.style.transform = 'rotate(180deg)';
                    updateHistoryUI();
                } else {
                    content.classList.add('hidden');
                    chevron.style.transform = 'rotate(0deg)';
                }
            }
        });
    }

    // Back to scanner
    console.log('[App] Attachement event listener pour back-to-scanner...');
    const backToScanner = document.getElementById('back-to-scanner');
    if (backToScanner) {
        console.log('[App] ✅ Élément back-to-scanner trouvé');
        backToScanner.addEventListener('click', () => {
            console.log('[App] Clic sur bouton retour au scanner');
            showView('scanner');
        });
        console.log('[App] ✅ Event listener attaché pour back-to-scanner');
    } else {
        console.warn('[App] ⚠️ Élément back-to-scanner introuvable');
    }

    // Quick access to localisations (bouton dans la vue scanner)
    const quickAccessLocalisations = document.getElementById('quick-access-localisations');
    if (quickAccessLocalisations) {
        quickAccessLocalisations.addEventListener('click', () => {
            console.log('[App] Bouton quick-access-localisations cliqué');
            showView('mes-localisations');
            loadMesLocalisations();
        });
    }

    // Bouton "Voir mes localisations" dans state-no-location
    const btnVoirLocalisations = document.getElementById('btn-voir-localisations');
    if (btnVoirLocalisations) {
        btnVoirLocalisations.addEventListener('click', () => {
            console.log('[App] Bouton "Voir mes localisations" cliqué');
            showView('mes-localisations');
            loadMesLocalisations();
        });
    }

    // Network status
    window.addEventListener('online', () => {
        AppState.isOnline = true;
        showToast('Connexion rétablie', 'success');
        updateOnlineStatusIndicator();
        SyncManager.syncPendingScans();
    });

    window.addEventListener('offline', () => {
        AppState.isOnline = false;
        showToast('Mode hors ligne activé', 'warning');
        updateOnlineStatusIndicator();
        SyncManager.updatePendingScansBadge();
    });
}

// ============================================
// DATA LOADING
// ============================================

/**
 * Charge l'inventaire en cours de l'utilisateur
 */
async function loadInventaire() {
    try {
        console.log('[App] Chargement de l\'inventaire...');
        
        // Vérifier l'authentification
        if (!AuthManager.isAuthenticated()) {
            console.error('[App] Utilisateur non authentifié');
            showToast('Session expirée, veuillez vous reconnecter', 'error');
            showView('login');
            return;
        }

        let response;
        try {
            response = await API.getCurrentInventaire();
            console.log('[App] Réponse API inventaire:', response);
        } catch (error) {
            console.error('[App] Erreur API getCurrentInventaire:', error);
            showToast('Erreur lors de la récupération de l\'inventaire', 'error');
            return;
        }
        
        // Le contrôleur retourne { inventaire: {...}, statistiques: {...} }
        const inventaire = response?.inventaire || response;
        console.log('[App] Inventaire extrait:', inventaire);
        
        if (!inventaire || !inventaire.id) {
            console.warn('[App] Aucun inventaire en cours');
            showToast('Aucun inventaire en cours', 'warning');
            return;
        }

        // Validation des données de l'inventaire
        if (!inventaire.id || !inventaire.annee) {
            console.error('[App] Données inventaire invalides:', inventaire);
            showToast('Données d\'inventaire invalides', 'error');
            return;
        }

        AppState.inventaire = inventaire;
        
        try {
            localStorage.setItem(CONFIG.STORAGE_KEY_INVENTAIRE, JSON.stringify(inventaire));
            console.log('[App] Inventaire sauvegardé dans localStorage');
        } catch (storageError) {
            console.error('[App] Erreur sauvegarde localStorage:', storageError);
            // Ne pas bloquer si localStorage échoue
        }
        
        console.log('[App] Inventaire chargé avec succès:', inventaire.id, inventaire.annee);

        // Charger la localisation active si elle existe
        try {
            const savedLocation = localStorage.getItem(CONFIG.STORAGE_KEY_LOCATION);
            if (savedLocation) {
                console.log('[App] Localisation sauvegardée trouvée, chargement...');
                try {
                    const locationData = JSON.parse(savedLocation);
                    
                    // Validation des données
                    if (locationData.activeLocation && locationData.localisation) {
                        AppState.activeLocation = locationData.activeLocation;
                        AppState.localisation = locationData.localisation;
                        AppState.biensAttendus = Array.isArray(locationData.biens) ? locationData.biens : [];
                        scannerManager.currentMode = 'bien';
                        updateActiveLocationUI();
                        console.log('[App] Localisation active restaurée:', locationData.localisation?.code);
                    } else {
                        console.warn('[App] Données localisation invalides, suppression...');
                        localStorage.removeItem(CONFIG.STORAGE_KEY_LOCATION);
                    }
                } catch (parseError) {
                    console.error('[App] Erreur parsing localisation sauvegardée:', parseError);
                    // Supprimer les données corrompues
                    localStorage.removeItem(CONFIG.STORAGE_KEY_LOCATION);
                }
            }
        } catch (locationError) {
            console.error('[App] Erreur chargement localisation sauvegardée:', locationError);
            // Ne pas bloquer si le chargement de la localisation échoue
        }

        // Mettre à jour l'UI
        try {
            const userName = document.getElementById('user-name');
            const menuUserName = document.getElementById('menu-user-name');
            const menuUserRole = document.getElementById('menu-user-role');

            if (userName && AppState.user) {
                userName.textContent = AppState.user.users || 'Utilisateur';
            }
            if (menuUserName && AppState.user) {
                menuUserName.textContent = AppState.user.users || 'Utilisateur';
            }
            if (menuUserRole && AppState.user) {
                menuUserRole.textContent = AppState.user.role === 'admin' ? 'Administrateur' : 'Agent';
            }
            console.log('[App] UI utilisateur mise à jour');
        } catch (uiError) {
            console.error('[App] Erreur mise à jour UI utilisateur:', uiError);
            // Ne pas bloquer si l'UI échoue
        }

    } catch (error) {
        console.error('[App] Erreur critique chargement inventaire:', error);
        console.error('[App] Stack trace:', error.stack);
        showToast('Erreur lors du chargement de l\'inventaire', 'error');
    }
}

/**
 * Charge et affiche les localisations assignées à l'utilisateur
 */
async function loadMesLocalisations() {
    console.log('[App] loadMesLocalisations() appelée');
    const listDiv = document.getElementById('localisations-list');
    
    if (!listDiv) {
        console.error('[App] Élément localisations-list introuvable');
        return;
    }

    try {
        console.log('[App] Vérification de l\'inventaire...');
        // Vérifier qu'un inventaire est chargé
        if (!AppState.inventaire || !AppState.inventaire.id) {
            console.warn('[App] Aucun inventaire chargé');
            listDiv.innerHTML = `
                <div class="text-center py-12 text-red-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold">Aucun inventaire chargé</p>
                    <p class="text-sm mt-2">Veuillez vous reconnecter</p>
                </div>
            `;
            return;
        }

        console.log('[App] Affichage du loader...');
        listDiv.innerHTML = `
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Chargement de vos localisations...</p>
            </div>
        `;

        console.log('[App] Appel API getMesLocalisations pour inventaire:', AppState.inventaire.id);
        const localisations = await API.getMesLocalisations(AppState.inventaire.id);
        
        console.log('[App] Localisations reçues:', localisations);
        console.log('[App] Type:', Array.isArray(localisations) ? 'Array' : typeof localisations);
        console.log('[App] Nombre de localisations:', localisations?.length || 0);
        console.log('[App] Inventaire ID:', AppState.inventaire?.id);

        if (!Array.isArray(localisations)) {
            console.error('[App] Erreur: localisations n\'est pas un tableau:', localisations);
            listDiv.innerHTML = `
                <div class="text-center py-12 text-red-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="font-semibold">Erreur lors du chargement</p>
                    <p class="text-sm mt-2">Format de réponse inattendu</p>
                    <button onclick="loadMesLocalisations()" class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                        Réessayer
                    </button>
                </div>
            `;
            return;
        }

        if (localisations.length === 0) {
            console.warn('[App] Aucune localisation assignée');
            listDiv.innerHTML = `
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="font-semibold">Aucune localisation assignée</p>
                    <p class="text-sm mt-2">Contactez votre administrateur pour obtenir des localisations</p>
                </div>
            `;
            return;
        }

        console.log('[App] Génération des cards pour', localisations.length, 'localisations');

        const statusColors = {
            en_attente: 'bg-gray-100 text-gray-700',
            en_cours: 'bg-blue-100 text-blue-700',
            termine: 'bg-green-100 text-green-700'
        };

        const statusLabels = {
            en_attente: 'En attente',
            en_cours: 'En cours',
            termine: 'Terminé'
        };

        listDiv.innerHTML = localisations.map(invLoc => {
            const percentage = invLoc.nombre_biens_attendus > 0 
                ? Math.round((invLoc.nombre_biens_scannes / invLoc.nombre_biens_attendus) * 100)
                : 0;

            return `
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800">${invLoc.localisation?.code || 'N/A'}</h3>
                                <p class="text-gray-600">${invLoc.localisation?.designation || 'N/A'}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    ${invLoc.localisation?.batiment || ''} 
                                    ${invLoc.localisation?.etage ? 'Étage ' + invLoc.localisation.etage : ''}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[invLoc.statut] || statusColors.en_attente}">
                                ${statusLabels[invLoc.statut] || 'Inconnu'}
                            </span>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Progression</span>
                                <span class="font-semibold">${invLoc.nombre_biens_scannes || 0}/${invLoc.nombre_biens_attendus || 0} biens</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: ${percentage}%"></div>
                            </div>
                        </div>

                        ${invLoc.statut !== 'termine' ? `
                        <button onclick="activerLocalisation('${invLoc.localisation?.code || ''}')" 
                                class="mt-4 w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                            Scanner ce bureau
                        </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        console.log('[App] Cards générées avec succès');

    } catch (error) {
        console.error('[App] Erreur critique chargement localisations:', error);
        console.error('[App] Stack trace:', error.stack);
        listDiv.innerHTML = `
            <div class="text-center py-12 text-red-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-red-600 text-lg mb-2">Erreur de chargement</p>
                <p class="text-gray-600 text-sm mb-4">${error.message || 'Une erreur est survenue'}</p>
                <button onclick="loadMesLocalisations()" class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Réessayer
                </button>
            </div>
        `;
    }
}

/**
 * Active une localisation pour le scan
 * @param {string} code - Code de la localisation
 */
function activerLocalisation(code) {
    console.log('[App] Activation localisation:', code);
    
    if (!code) {
        console.error('[App] Code localisation manquant');
        showToast('Code localisation manquant', 'error');
        return;
    }
    
    // Retourner à la vue scanner
    showView('scanner');
    console.log('[App] Vue scanner affichée');
    
    // Afficher message à l'utilisateur
    showToast(`Scannez le QR code du bureau ${code}`, 'info');
    
    // Forcer le mode localisation
    if (scannerManager) {
        scannerManager.currentMode = 'localisation';
        console.log('[App] Mode scanner changé en "localisation"');
        
        // Redémarrer le scanner si nécessaire
        if (!scannerManager.isScanning) {
            console.log('[App] Redémarrage du scanner...');
            setTimeout(() => {
                try {
                    scannerManager.start();
                    console.log('[App] Scanner redémarré');
                } catch (error) {
                    console.error('[App] Erreur redémarrage scanner:', error);
                }
            }, 500);
        } else {
            console.log('[App] Scanner déjà actif');
        }
    } else {
        console.warn('[App] ScannerManager non initialisé');
    }
    
    // Fermer le menu si ouvert
    const menuDrawer = document.getElementById('menu-drawer');
    if (menuDrawer && !menuDrawer.classList.contains('hidden')) {
        menuDrawer.classList.add('hidden');
        console.log('[App] Menu fermé');
    }
}

/**
 * Fonction de diagnostic pour vérifier l'état de la page "Mes Localisations"
 * À appeler depuis la console du navigateur: diagnosticMesLocalisations()
 * OU: window.diagnosticMesLocalisations()
 */
window.diagnosticMesLocalisations = function diagnosticMesLocalisations() {
    try {
        console.log('========== 🔍 DIAGNOSTIC MES LOCALISATIONS ==========');
        
        // 1. Vérifier l'élément de la vue
        const viewElement = document.getElementById('view-mes-localisations');
        console.log('1️⃣ Élément view-mes-localisations:', viewElement ? '✅ TROUVÉ' : '❌ INTROUVABLE');
        if (viewElement) {
            console.log('   - Classes:', viewElement.className);
            console.log('   - Contient "hidden"?', viewElement.classList.contains('hidden'));
            console.log('   - Display:', window.getComputedStyle(viewElement).display);
            console.log('   - Visibility:', window.getComputedStyle(viewElement).visibility);
            console.log('   - OffsetParent:', viewElement.offsetParent ? 'Visible' : 'Hidden');
        }
        
        // 2. Vérifier le lien de navigation
        const navElement = document.getElementById('nav-mes-localisations');
        console.log('2️⃣ Élément nav-mes-localisations:', navElement ? '✅ TROUVÉ' : '❌ INTROUVABLE');
        if (navElement) {
            console.log('   - Visible?', navElement.offsetParent !== null);
            console.log('   - Classes:', navElement.className);
        }
        
        // 3. Vérifier le bouton quick-access
        const quickAccessElement = document.getElementById('quick-access-localisations');
        console.log('3️⃣ Élément quick-access-localisations:', quickAccessElement ? '✅ TROUVÉ' : '❌ INTROUVABLE');
        
        // 4. Vérifier le bouton btn-voir-localisations
        const btnVoirElement = document.getElementById('btn-voir-localisations');
        console.log('4️⃣ Élément btn-voir-localisations:', btnVoirElement ? '✅ TROUVÉ' : '❌ INTROUVABLE');
        
        // 5. Vérifier l'état de l'application
        console.log('5️⃣ État de l\'application:');
        console.log('   - Inventaire chargé?', AppState.inventaire ? '✅ OUI' : '❌ NON');
        console.log('   - Inventaire ID:', AppState.inventaire?.id || 'N/A');
        console.log('   - Utilisateur connecté?', AppState.user ? '✅ OUI' : '❌ NON');
        console.log('   - Token présent?', AppState.token ? '✅ OUI' : '❌ NON');
        
        // 6. Vérifier toutes les vues
        console.log('6️⃣ Toutes les vues disponibles:');
        const allViews = document.querySelectorAll('[id^="view-"]');
        allViews.forEach(v => {
            const isHidden = v.classList.contains('hidden');
            const display = window.getComputedStyle(v).display;
            console.log(`   - ${v.id}: ${isHidden ? '❌ CACHÉE' : '✅ VISIBLE'} (display: ${display})`);
        });
        
        // 7. Tester l'API
        if (AppState.inventaire?.id && AppState.token) {
            console.log('7️⃣ Test de l\'API...');
            const apiUrl = `${CONFIG.API_BASE_URL}/inventaires/${AppState.inventaire.id}/mes-localisations`;
            console.log('   - URL:', apiUrl);
            
            fetch(apiUrl, {
                headers: {
                    'Authorization': `Bearer ${AppState.token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('   - Status:', response.status, response.statusText);
                return response.json();
            })
            .then(data => {
                console.log('   - ✅ Réponse reçue:', data);
                console.log('   - Type:', Array.isArray(data) ? 'Array' : typeof data);
                if (data?.localisations) {
                    console.log('   - Localisations dans data.localisations:', data.localisations.length);
                } else if (Array.isArray(data)) {
                    console.log('   - Localisations (array direct):', data.length);
                }
            })
            .catch(error => {
                console.error('   - ❌ Erreur API:', error);
            });
        } else {
            console.log('7️⃣ Test API: ❌ Impossible');
            console.log('   - Inventaire ID:', AppState.inventaire?.id || 'MANQUANT');
            console.log('   - Token:', AppState.token ? 'PRÉSENT' : 'MANQUANT');
        }
        
        console.log('========== ✅ FIN DIAGNOSTIC ==========');
        console.log('💡 Commandes utiles:');
        console.log('   - showView("mes-localisations")');
        console.log('   - loadMesLocalisations()');
        console.log('   - document.getElementById("view-mes-localisations").classList.remove("hidden")');
    } catch (error) {
        console.error('❌ Erreur dans diagnosticMesLocalisations:', error);
        console.error('Stack trace:', error.stack);
    }
}

// Vérification immédiate que la fonction est disponible
console.log('[App] ✅ diagnosticMesLocalisations définie:', typeof window.diagnosticMesLocalisations);

// Exposer les fonctions globalement pour les event handlers inline
window.enregistrerScan = enregistrerScan;
window.annulerScan = annulerScan;
window.activerLocalisation = activerLocalisation;
window.showTerminerBureauModal = showTerminerBureauModal;
window.closeTerminerBureauModal = closeTerminerBureauModal;
window.confirmTerminerBureau = confirmTerminerBureau;
window.loadMesLocalisations = loadMesLocalisations; // Pour le bouton "Réessayer" dans les erreurs
window.showView = showView; // Pour tester depuis la console
// diagnosticMesLocalisations est déjà exposée directement dans sa déclaration

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialise l'application complète
 */
async function init() {
    console.log('[App] Initialisation de l\'application...');

    try {
        // Initialiser IndexedDB
        await dbManager.init();
        console.log('[App] IndexedDB initialisé');

        // Vérifier l'authentification
        const isAuthenticated = AuthManager.init();

        // Attacher les event listeners
        attachEventListeners();

        if (isAuthenticated) {
            // Charger l'inventaire
            await loadInventaire();
            
            // Initialiser le scanner
            await scannerManager.init();
            
            // Afficher le mode initial
            if (AppState.activeLocation) {
                // Un bureau est déjà actif (restauré depuis localStorage)
                showModeBien(
                    AppState.localisation,
                    AppState.biensAttendus.length,
                    AppState.activeLocation.nombre_biens_scannes || 0
                );
                scannerManager.currentMode = 'bien';
            } else {
                // Aucun bureau actif, mode localisation
                showModeLocalisation();
                scannerManager.currentMode = 'localisation';
            }
            
            // Afficher la vue scanner
            showView('scanner');
            
            // Mettre à jour le badge des scans en attente
            await SyncManager.updatePendingScansBadge();

            // Mettre à jour l'indicateur de connexion
            updateOnlineStatusIndicator();

            // Synchroniser si en ligne
            if (navigator.onLine) {
                SyncManager.syncPendingScans();
            }
        } else {
            // Afficher la vue login
            showView('login');
        }

        console.log('[App] Initialisation terminée');

    } catch (error) {
        console.error('[App] Erreur initialisation:', error);
        showToast('Erreur lors de l\'initialisation', 'error');
    }
}

// Démarrer l'app quand le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Confirmation que les fonctions globales sont disponibles
setTimeout(() => {
    console.log('[App] ✅ Vérification des fonctions globales:');
    console.log('[App]   - diagnosticMesLocalisations:', typeof window.diagnosticMesLocalisations);
    console.log('[App]   - showView:', typeof window.showView);
    console.log('[App]   - loadMesLocalisations:', typeof window.loadMesLocalisations);
    console.log('[App] 💡 Tapez "diagnosticMesLocalisations()" dans la console pour diagnostiquer');
}, 1000);
