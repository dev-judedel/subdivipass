const DB_NAME = 'guard_scanner_cache';
const DB_VERSION = 1;
const PASS_STORE = 'passes';

export function isIndexedDbSupported() {
    return typeof indexedDB !== 'undefined';
}

function openDatabase() {
    return new Promise((resolve, reject) => {
        if (!isIndexedDbSupported()) {
            return reject(new Error('IndexedDB not supported'));
        }

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains(PASS_STORE)) {
                const store = db.createObjectStore(PASS_STORE, { keyPath: 'key' });
                store.createIndex('updated_at', 'updated_at', { unique: false });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

export async function cachePass(pass, identifiers = []) {
    if (!isIndexedDbSupported() || !pass) {
        return;
    }

    try {
        const db = await openDatabase();
        const tx = db.transaction(PASS_STORE, 'readwrite');
        const store = tx.objectStore(PASS_STORE);
        const keys = new Set(
            identifiers.filter(Boolean).map((value) => String(value).trim())
        );

        keys.add(String(pass.pass_number ?? '').trim());
        keys.add(String(pass.pin ?? '').trim());
        keys.add(String(pass.uuid ?? '').trim());

        const payload = {
            visitor_name: pass.visitor_name,
            pass_number: pass.pass_number,
            status: pass.status,
            valid_to: pass.valid_to,
            subdivision_id: pass.subdivision_id,
        };

        keys.forEach((key) => {
            if (!key) {
                return;
            }

            store.put({
                key,
                data: payload,
                updated_at: Date.now(),
            });
        });

        await tx.complete;
        db.close();
    } catch (error) {
        console.warn('Unable to cache pass locally', error);
    }
}

export async function getCachedPass(identifier) {
    if (!isIndexedDbSupported() || !identifier) {
        return null;
    }

    try {
        const db = await openDatabase();
        const tx = db.transaction(PASS_STORE, 'readonly');
        const store = tx.objectStore(PASS_STORE);

        const result = await new Promise((resolve) => {
            const request = store.get(String(identifier).trim());
            request.onsuccess = () => resolve(request.result?.data ?? null);
            request.onerror = () => resolve(null);
        });

        db.close();
        return result;
    } catch (error) {
        console.warn('Unable to read pass cache', error);
        return null;
    }
}

export async function trimCachedPasses(limit = 100) {
    if (!isIndexedDbSupported()) {
        return;
    }

    try {
        const db = await openDatabase();
        const tx = db.transaction(PASS_STORE, 'readwrite');
        const store = tx.objectStore(PASS_STORE);
        const request = store.index('updated_at').openCursor(null, 'prev');

        const entries = [];
        request.onsuccess = async () => {
            const cursor = request.result;
            if (cursor) {
                entries.push(cursor.primaryKey);
                cursor.continue();
            } else {
                const keysToDelete = entries.slice(limit);
                await Promise.all(
                    keysToDelete.map((key) => {
                        return new Promise((resolve) => {
                            const deleteRequest = store.delete(key);
                            deleteRequest.onsuccess = () => resolve();
                            deleteRequest.onerror = () => resolve();
                        });
                    })
                );
                db.close();
            }
        };
    } catch (error) {
        console.warn('Unable to trim pass cache', error);
    }
}
