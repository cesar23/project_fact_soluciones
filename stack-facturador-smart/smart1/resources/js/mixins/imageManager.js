/**
 * Image Manager Mixin para POS
 * Maneja carga optimizada de imágenes con cache localStorage
 *
 * Características:
 * - Cache persistente en localStorage
 * - Carga en lotes con debounce
 * - Intersection Observer para lazy loading
 * - Fallbacks automáticos para imágenes faltantes
 * - Limpieza automática de cache viejo
 */

export const imageManager = {
    data() {
        return {
            // Image optimization properties
            loadingImages: new Set(),
            errorImages: new Set(),
            imageObserver: null,
            criticalImageCount: 6, // Primeras 6 imágenes visibles
            batchTimeout: null, // Timeout para debounce de batching
            cachedBase64Images: new Map(), // Cache de imágenes base64

            // Configuración del cache
            imageCacheConfig: {
                maxAge: 3600000, // 1 hora en milliseconds
                keyPrefix: 'pos_images_cache_',
                maxCacheSize: 100, // Máximo 100 imágenes en cache
                cleanupInterval: 300000 // Limpiar cache cada 5 minutos
            },

            // Control de carga duplicada
            isLoadingAll: false, // Flag para evitar cargas simultáneas
            loadingBatches: new Set() // Track de lotes en proceso
        };
    },

    mounted() {
        // Cargar cache desde localStorage al iniciar
        this.loadImageCacheFromStorage();

        // Configurar limpieza automática del cache
        this.setupCacheCleanup();
    },

    beforeUnmount() {
        // Limpiar observers y timeouts
        if (this.imageObserver) {
            this.imageObserver.disconnect();
        }
        if (this.batchTimeout) {
            clearTimeout(this.batchTimeout);
        }
    },

    methods: {
        /**
         * Inicializar sistema de carga de imágenes
         */
        initImageLoading() {
            // Resetear flags de control
            this.isLoadingAll = false;
            this.loadingBatches.clear();

            // Cargar cache desde localStorage
            this.loadImageCacheFromStorage();

            // Solo cargar si hay imágenes que necesitan carga
            if (this.needsImageLoading()) {
                // Cargar todas las imágenes visibles al entrar divididas dinámicamente
                this.loadAllVisibleImages();

                // Sistema de respaldo: verificar imágenes no cargadas
                setTimeout(() => this.checkMissingImages(), 8000); // 8 segundos después
                setTimeout(() => this.checkMissingImages(), 15000); // 15 segundos después
            } else {
                // Si ya están cacheadas, solo configurar lazy loading
                setTimeout(() => this.setupLazyLoading(), 200);
            }
        },

        /**
         * Verificar si necesita cargar imágenes
         */
        needsImageLoading() {
            if (!this.all_items || this.all_items.length === 0) return false;

            const criticalItems = this.all_items.slice(0, this.criticalImageCount);
            return criticalItems.some(item =>
                item.image_url &&
                !item.image_url.includes('imagen-no-disponible.jpg') &&
                !this.cachedBase64Images.has(item.id)
            );
        },

        /**
         * Obtener fuente de imagen con cache inteligente
         */
        getImageSrc(item, index) {
            // Si la imagen es imagen-no-disponible.jpg, cargar siempre (está cacheada)
            if (!item.image_url || item.image_url.includes('imagen-no-disponible.jpg')) {
                return item.image_url || '/logo/imagen-no-disponible.jpg';
            }

            // Si está en cache base64, usar esa
            if (this.cachedBase64Images.has(item.id)) {
                return this.cachedBase64Images.get(item.id);
            }

            // Si está cargando, mostrar placeholder SVG
            if (this.loadingImages.has(item.id)) {
                return this.getPlaceholderSVG();
            }

            // Si tuvo error, mostrar imagen por defecto
            if (this.errorImages.has(item.id)) {
                return '/logo/imagen-no-disponible.jpg';
            }

            // Fallback: mostrar placeholder mientras se carga
            return this.getPlaceholderSVG();
        },

        /**
         * Cargar todas las imágenes visibles divididas dinámicamente
         */
        async loadAllVisibleImages() {
            // ⚠️ PROTECCIÓN CONTRA LLAMADAS DUPLICADAS
            if (this.isLoadingAll) {
                console.log(`⚠️ loadAllVisibleImages ya está en ejecución, saltando...`);
                return;
            }

            if (!this.all_items || this.all_items.length === 0) return;

            // Marcar como en proceso
            this.isLoadingAll = true;

            // Filtrar items que necesitan carga (sin imagen por defecto y no en cache)
            const allItemsToLoad = this.all_items
                .filter(item => item.image_url &&
                    !item.image_url.includes('imagen-no-disponible.jpg') &&
                    !this.cachedBase64Images.has(item.id))
                .map(item => ({ itemId: item.id, imageUrl: item.image_url }));

            if (allItemsToLoad.length === 0) {
                // Si no hay imágenes para cargar, configurar lazy loading inmediatamente
                this.isLoadingAll = false;
                setTimeout(() => this.setupLazyLoading(), 200);
                return;
            }

            // Calcular número de divisiones dinámicamente
            const totalImages = allItemsToLoad.length;
            let batchCount = 1; // Por defecto 1 lote (una sola petición)

            // Solo dividir en múltiples lotes si hay muchas imágenes
            if (totalImages >= 20 && totalImages < 50) {
                batchCount = 3;
            } else if (totalImages >= 50 && totalImages < 70) {
                batchCount = 4;
            } else if (totalImages >= 70) {
                batchCount = 5;
            }

            console.log(`📊 Total imágenes: ${totalImages}, Divisiones: ${batchCount}`);

            // Dividir en lotes
            const batchSize = Math.ceil(totalImages / batchCount);
            const batches = [];

            for (let i = 0; i < totalImages; i += batchSize) {
                batches.push(allItemsToLoad.slice(i, i + batchSize));
            }

            // Cargar lotes con espaciado temporal
            for (let i = 0; i < batches.length; i++) {
                const batch = batches[i];
                const batchId = `batch_${Date.now()}_${i}`;
                const delay = i * 2000; // 2 segundos entre cada lote

                setTimeout(async () => {
                    // Verificar si este lote ya se está cargando
                    if (this.loadingBatches.has(batchId)) {
                        console.log(`⚠️ Lote ${batchId} ya en proceso, saltando...`);
                        return;
                    }

                    this.loadingBatches.add(batchId);
                    console.log(`🚀 Cargando lote ${i + 1}/${batches.length}: ${batch.length} imágenes`);

                    try {
                        await this.loadImageBatchOptimized(batch);
                    } finally {
                        this.loadingBatches.delete(batchId);
                    }

                    // Configurar lazy loading después del último lote
                    if (i === batches.length - 1) {
                        this.isLoadingAll = false; // Marcar como completado
                        setTimeout(() => this.setupLazyLoading(), 1000);
                    }
                }, delay);
            }
        },

        /**
         * Cargar imágenes críticas (primeras 6) - Método legacy mantenido para compatibilidad
         */
        async loadCriticalImages() {
            if (!this.all_items || this.all_items.length === 0) return;

            const criticalItems = this.all_items.slice(0, this.criticalImageCount);

            // Filtrar items sin imagen por defecto y que no estén en cache base64
            const itemsToLoad = criticalItems
                .filter(item => !item.image_url.includes('imagen-no-disponible.jpg') && !this.cachedBase64Images.has(item.id))
                .map(item => ({ itemId: item.id, imageUrl: item.image_url }));

            if (itemsToLoad.length > 0) {
                await this.loadImageBatchOptimized(itemsToLoad);
            }
        },

        /**
         * Cargar imágenes de alta prioridad (siguientes 8)
         */
        async loadHighPriorityImages() {
            if (!this.all_items || this.all_items.length === 0) return;

            const highPriorityItems = this.all_items.slice(this.criticalImageCount, this.criticalImageCount + 8);

            // Filtrar items sin imagen por defecto y que no estén en cache base64
            const itemsToLoad = highPriorityItems
                .filter(item => !item.image_url.includes('imagen-no-disponible.jpg') && !this.cachedBase64Images.has(item.id))
                .map(item => ({ itemId: item.id, imageUrl: item.image_url }));

            if (itemsToLoad.length > 0) {
                await this.loadImageBatchOptimized(itemsToLoad);
            }
        },

        /**
         * Método optimizado para cargar imágenes como base64 en una sola petición
         */
        async loadImageBatchOptimized(itemsToLoad) {
            if (itemsToLoad.length === 0) return;

            // ⚠️ FILTRAR IMÁGENES YA EN PROCESO O CACHEADAS
            const filteredItems = itemsToLoad.filter(item =>
                !this.loadingImages.has(item.itemId) &&
                !this.cachedBase64Images.has(item.itemId)
            );

            if (filteredItems.length === 0) {
                console.log(`⚠️ Todas las imágenes del lote ya están cargadas o en proceso`);
                return;
            }

            const itemIds = filteredItems.map(item => item.itemId);
            const filenames = filteredItems.map(item => this.extractFilenameFromUrl(item.imageUrl));

            console.log(`📥 Procesando lote real: ${filteredItems.length} imágenes (filtradas de ${itemsToLoad.length})`);

            // Marcar como loading
            itemIds.forEach(id => this.loadingImages.add(id));

            try {
                // Hacer una sola petición HTTP que retorna las imágenes como base64
                const response = await this.$http.post('/pos/images-batch', {
                    filenames: filenames
                });

                const batchImages = response.data.images || [];
                const imageMap = new Map(batchImages.map(img => [img.filename, img.data_url]));

                // Cache las imágenes base64 directamente (sin peticiones HTTP adicionales)
                filteredItems.forEach(({ itemId, imageUrl }) => {
                    try {
                        const filename = this.extractFilenameFromUrl(imageUrl);
                        const dataUrl = imageMap.get(filename);

                        if (dataUrl) {
                            // Guardar la imagen base64 en cache para uso inmediato
                            this.cacheBase64Image(itemId, dataUrl);
                        } else {
                            this.errorImages.add(itemId);
                        }
                        this.loadingImages.delete(itemId);
                    } catch (error) {
                        this.errorImages.add(itemId);
                        this.loadingImages.delete(itemId);
                    }
                });

                // Forzar re-render para mostrar las imágenes cacheadas
                this.$forceUpdate();

            } catch (error) {
                console.error('Error loading critical image batch:', error);
                // En caso de error, marcar todos como error
                itemIds.forEach(id => {
                    this.errorImages.add(id);
                    this.loadingImages.delete(id);
                });
            }
        },

        /**
         * Configurar lazy loading con Intersection Observer
         */
        setupLazyLoading() {
            if (this.imageObserver) {
                this.imageObserver.disconnect();
            }

            const options = {
                root: null,
                rootMargin: '300px',
                threshold: [0, 0.1, 0.5]
            };

            this.imageObserver = new IntersectionObserver((entries) => {
                this.handleImageIntersection(entries);
            }, options);

            // Observar todas las imágenes lazy
            this.$nextTick(() => {
                const lazyImages = document.querySelectorAll('.lazy-image[data-item-id]');
                lazyImages.forEach(img => {
                    this.imageObserver.observe(img);
                });
            });
        },

        /**
         * Manejar intersección de imágenes (lazy loading)
         */
        handleImageIntersection(entries) {
            const imagesToLoad = [];

            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const itemId = parseInt(img.dataset.itemId);

                    if (itemId && !this.cachedBase64Images.has(itemId) && !this.loadingImages.has(itemId)) {
                        const item = this.all_items.find(item => item.id === itemId);
                        if (item && item.image_url && !item.image_url.includes('imagen-no-disponible.jpg')) {
                            imagesToLoad.push({
                                img: img,
                                itemId: itemId,
                                imageUrl: item.image_url
                            });
                        }
                    }
                }
            });

            // Cargar imágenes en lote si hay alguna que cargar
            if (imagesToLoad.length > 0) {
                // Debounce para evitar múltiples llamadas rápidas
                if (this.batchTimeout) {
                    clearTimeout(this.batchTimeout);
                }

                this.batchTimeout = setTimeout(() => {
                    this.loadImageBatch(imagesToLoad);
                }, 100); // 100ms de debounce
            }
        },

        /**
         * Cargar lote de imágenes
         */
        async loadImageBatch(imagesToLoad) {
            const batchSize = 10; // Procesar 10 imágenes por lote

            for (let i = 0; i < imagesToLoad.length; i += batchSize) {
                const batch = imagesToLoad.slice(i, i + batchSize);

                // Extraer datos del lote
                const itemIds = batch.map(({ itemId }) => itemId);
                const filenames = batch.map(({ imageUrl }) => this.extractFilenameFromUrl(imageUrl));

                // Marcar como loading
                itemIds.forEach(id => this.loadingImages.add(id));

                try {
                    // Hacer una sola petición HTTP usando solo los nombres de archivo
                    const response = await this.$http.post('/pos/images-batch', {
                        filenames: filenames
                    });

                    const batchImages = response.data.images || [];
                    const imageMap = new Map(batchImages.map(img => [img.filename, img.data_url]));

                    // Cache las imágenes base64 directamente (sin peticiones HTTP adicionales)
                    batch.forEach(({ img, itemId, imageUrl }) => {
                        try {
                            const filename = this.extractFilenameFromUrl(imageUrl);
                            const dataUrl = imageMap.get(filename);

                            if (dataUrl) {
                                // Guardar la imagen base64 en cache para uso inmediato
                                this.cacheBase64Image(itemId, dataUrl);
                            } else {
                                // Si no se devolvió URL, marcar como error
                                this.errorImages.add(itemId);
                            }
                            this.loadingImages.delete(itemId);
                        } catch (error) {
                            this.errorImages.add(itemId);
                            this.loadingImages.delete(itemId);
                        }
                    });

                } catch (error) {
                    console.error('Error loading image batch:', error);
                    // En caso de error, marcar todos como error
                    itemIds.forEach(id => {
                        this.errorImages.add(id);
                        this.loadingImages.delete(id);
                    });
                }

                // Forzar re-render después de cada lote
                this.$forceUpdate();

                // Pausa de 1.5 segundos entre lotes para no saturar
                if (i + batchSize < imagesToLoad.length) {
                    await new Promise(resolve => setTimeout(resolve, 1500));
                }
            }
        },

        /**
         * Refrescar lazy loading después de cambios en all_items
         */
        refreshLazyLoading() {
            // Desconectar observer anterior
            if (this.imageObserver) {
                this.imageObserver.disconnect();
            }

            // Configurar nuevamente con un pequeño delay para que el DOM se actualice
            setTimeout(() => {
                // Usar el nuevo sistema dinámico para cargar todas las imágenes
                this.loadAllVisibleImages();

                // Activar sistema de respaldo para nuevos items
                setTimeout(() => this.checkMissingImages(), 8000); // 8 segundos después
                setTimeout(() => this.checkMissingImages(), 15000); // 15 segundos después
            }, 100);
        },

        /**
         * Verificar imágenes que no han cargado (sistema de respaldo)
         */
        checkMissingImages() {
            if (!this.all_items || this.all_items.length === 0) return;

            const missingImages = [];

            this.all_items.forEach(item => {
                if (item.image_url &&
                    !item.image_url.includes('imagen-no-disponible.jpg') &&
                    !this.cachedBase64Images.has(item.id) &&
                    !this.loadingImages.has(item.id) &&
                    !this.errorImages.has(item.id)) {

                    missingImages.push({
                        itemId: item.id,
                        imageUrl: item.image_url
                    });
                }
            });

            if (missingImages.length > 0) {
                console.log(`🔍 Encontradas ${missingImages.length} imágenes faltantes, cargando...`);
                this.loadImageBatch(missingImages);
            } else {
                console.log(`⚠️ No hay imágenes para cargar en este lote`);
            }
        },

        /**
         * Extraer nombre de archivo de URL
         */
        extractFilenameFromUrl(url) {
            if (!url) return null;
            const parts = url.split('/');
            return parts[parts.length - 1];
        },

        /**
         * Cachear imagen base64 con persistencia en localStorage
         */
        cacheBase64Image(itemId, dataUrl) {
            this.cachedBase64Images.set(itemId, dataUrl);
            this.saveImageCacheToStorage();
        },

        /**
         * Cargar cache desde localStorage
         */
        loadImageCacheFromStorage() {
            try {
                const cacheKey = this.imageCacheConfig.keyPrefix + window.location.pathname;
                const cached = localStorage.getItem(cacheKey);

                if (cached) {
                    const parsedCache = JSON.parse(cached);

                    // Verificar que no esté muy viejo
                    if (Date.now() - parsedCache.timestamp < this.imageCacheConfig.maxAge) {
                        this.cachedBase64Images = new Map(parsedCache.images);
                        console.log(`📦 Cache cargado: ${this.cachedBase64Images.size} imágenes`);
                    } else {
                        console.log(`🗑️ Cache expirado, limpiando...`);
                        localStorage.removeItem(cacheKey);
                    }
                }
            } catch (error) {
                console.warn('Error loading image cache from storage:', error);
            }
        },

        /**
         * Guardar cache en localStorage
         */
        saveImageCacheToStorage() {
            try {
                const cacheKey = this.imageCacheConfig.keyPrefix + window.location.pathname;

                // Limitar tamaño del cache
                let imagesToCache = Array.from(this.cachedBase64Images.entries());
                if (imagesToCache.length > this.imageCacheConfig.maxCacheSize) {
                    imagesToCache = imagesToCache.slice(-this.imageCacheConfig.maxCacheSize);
                    this.cachedBase64Images = new Map(imagesToCache);
                }

                const cacheData = {
                    timestamp: Date.now(),
                    images: imagesToCache
                };

                localStorage.setItem(cacheKey, JSON.stringify(cacheData));
            } catch (error) {
                console.warn('Error saving image cache to storage:', error);
                // Si el localStorage está lleno, limpiar y reintentar
                if (error.name === 'QuotaExceededError') {
                    this.clearOldCache();
                }
            }
        },

        /**
         * Configurar limpieza automática del cache
         */
        setupCacheCleanup() {
            // Limpiar cache viejo cada 5 minutos
            setInterval(() => {
                this.clearOldCache();
            }, this.imageCacheConfig.cleanupInterval);
        },

        /**
         * Limpiar cache viejo
         */
        clearOldCache() {
            try {
                const keys = Object.keys(localStorage);
                const prefix = this.imageCacheConfig.keyPrefix;
                const maxAge = this.imageCacheConfig.maxAge;

                keys.forEach(key => {
                    if (key.startsWith(prefix)) {
                        try {
                            const data = JSON.parse(localStorage.getItem(key));
                            if (Date.now() - data.timestamp > maxAge) {
                                localStorage.removeItem(key);
                                console.log(`🗑️ Cache limpiado: ${key}`);
                            }
                        } catch (e) {
                            localStorage.removeItem(key);
                        }
                    }
                });
            } catch (error) {
                console.warn('Error clearing old cache:', error);
            }
        },

        /**
         * Limpiar todo el caché de imágenes manualmente
         */
        clearAllImageCache() {
            try {
                // Limpiar cache en memoria
                this.cachedBase64Images.clear();
                this.loadingImages.clear();
                this.errorImages.clear();
                
                // Limpiar cache en localStorage
                const keys = Object.keys(localStorage);
                const prefix = this.imageCacheConfig.keyPrefix;
                
                let clearedCount = 0;
                keys.forEach(key => {
                    if (key.startsWith(prefix)) {
                        localStorage.removeItem(key);
                        clearedCount++;
                    }
                });
                
                console.log(`✅ Cache de imágenes eliminado completamente (${clearedCount} entradas)`);
                
                // Forzar actualización de la vista
                this.$forceUpdate();
                
                return { success: true, clearedCount };
            } catch (error) {
                console.error('❌ Error al limpiar el cache:', error);
                return { success: false, error };
            }
        },

        /**
         * Limpiar caché de la página actual solamente
         */
        clearCurrentPageCache() {
            try {
                // Limpiar cache en memoria
                const currentSize = this.cachedBase64Images.size;
                this.cachedBase64Images.clear();
                this.loadingImages.clear();
                this.errorImages.clear();
                
                // Limpiar cache de la página actual en localStorage
                const cacheKey = this.imageCacheConfig.keyPrefix + window.location.pathname;
                localStorage.removeItem(cacheKey);
                
                console.log(`✅ Cache de la página actual eliminado (${currentSize} imágenes)`);
                
                // Forzar actualización de la vista
                this.$forceUpdate();
                
                return { success: true, clearedCount: currentSize };
            } catch (error) {
                console.error('❌ Error al limpiar el cache de la página actual:', error);
                return { success: false, error };
            }
        },

        /**
         * Recargar imágenes después de limpiar el caché
         */
        reloadImagesAfterCacheClear() {
            try {
                // Reinicializar el sistema de carga de imágenes
                this.initImageLoading();
                console.log('🔄 Recargando imágenes...');
            } catch (error) {
                console.error('❌ Error al recargar imágenes:', error);
            }
        },

        /**
         * Obtener placeholder SVG para imágenes que están cargando
         */
        getPlaceholderSVG() {
            return `data:image/svg+xml;base64,${btoa(`
                <svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100%" height="100%" fill="#f0f0f0"/>
                    <rect x="20" y="35" width="60" height="4" fill="#ddd" rx="2">
                        <animate attributeName="opacity" values="1;0.5;1" dur="1.5s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="20" y="45" width="40" height="4" fill="#ddd" rx="2">
                        <animate attributeName="opacity" values="1;0.5;1" dur="1.5s" begin="0.2s" repeatCount="indefinite"/>
                    </rect>
                    <rect x="20" y="55" width="50" height="4" fill="#ddd" rx="2">
                        <animate attributeName="opacity" values="1;0.5;1" dur="1.5s" begin="0.4s" repeatCount="indefinite"/>
                    </rect>
                </svg>
            `)}`;
        },

        // Métodos de estado para el template
        isImageLoading(itemId) {
            return this.loadingImages.has(itemId);
        },

        isImageLoaded(itemId) {
            return this.cachedBase64Images.has(itemId);
        },

        hasImageError(itemId) {
            return this.errorImages.has(itemId);
        },

        onImageLoad(itemId) {
            // Ya no necesitamos hacer nada especial cuando una imagen se carga
            // porque ya está manejado por el sistema de cache base64
        },

        onImageError(itemId) {
            this.errorImages.add(itemId);
            this.loadingImages.delete(itemId);
        }
    }
};