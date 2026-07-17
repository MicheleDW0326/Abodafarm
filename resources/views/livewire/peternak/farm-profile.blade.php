<div class="space-y-6" x-data="{
    map: null,
    marker: null,
    autocomplete: null,
    searchInput: null,
    mapInitialized: false,

    init() {
        this.$nextTick(() => {
            this.waitForGoogleMaps();
        });
    },

    waitForGoogleMaps(retryCount = 0) {
        const mapElement = document.getElementById('farm-map');

        // Check jika Google Maps sudah loaded
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            // Double check dengan memastikan document.readyState
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                this.initMap();
            } else {
                // Tunggu DOM ready
                window.addEventListener('DOMContentLoaded', () => this.initMap(), { once: true });
            }
            return;
        }

        // Retry dengan exponential backoff (max 30 detik)
        if (retryCount < 100) {
            const delay = Math.min(100 * Math.pow(1.1, retryCount), 500); // Max 500ms
            setTimeout(() => this.waitForGoogleMaps(retryCount + 1), delay);
        } else {
            console.error('Google Maps failed to load after 30 seconds');
        }
    },

    initMap() {
        if (this.mapInitialized) return;
        if (!document.getElementById('farm-map')) return;

        this.mapInitialized = true;

        const defaultLat = this.$wire.latitude || 1.5;
        const defaultLng = this.$wire.longitude || 125.0;

        this.map = new google.maps.Map(document.getElementById('farm-map'), {
            zoom: 13,
            center: { lat: defaultLat, lng: defaultLng },
            mapTypeId: 'roadmap',
            mapTypeControl: true,
            zoomControl: true,
            streetViewControl: true,
            fullscreenControl: true,
        });

        // Add marker if coordinates exist
        if (this.$wire.latitude && this.$wire.longitude) {
            this.placeMarker({ lat: this.$wire.latitude, lng: this.$wire.longitude });
        }

        // Click listener
        this.map.addListener('click', (e) => {
            this.placeMarker(e.latLng);
            this.reverseGeocode(e.latLng);
        });

        // Init autocomplete
        this.initAutocomplete();
    },

    initAutocomplete() {
        this.searchInput = document.getElementById('farm-search');
        if (!this.searchInput) return;

        this.autocomplete = new google.maps.places.Autocomplete(this.searchInput, {
            componentRestrictions: { country: 'id' },
            fields: ['address_components', 'geometry', 'name', 'place_id'],
            types: ['address'],
        });

        this.autocomplete.addListener('place_changed', () => {
            const place = this.autocomplete.getPlace();
            if (!place.geometry) return;

            this.map.setCenter(place.geometry.location);
            this.map.setZoom(16);
            this.placeMarker(place.geometry.location);
            this.fillAddressFields(place);
        });
    },

    placeMarker(location) {
        if (this.marker) {
            this.marker.setPosition(location);
        } else {
            this.marker = new google.maps.Marker({
                position: location,
                map: this.map,
                draggable: true,
                animation: google.maps.Animation.DROP,
            });

            this.marker.addListener('dragend', () => {
                const pos = this.marker.getPosition();
                this.reverseGeocode(pos);
            });
        }

        this.$wire.latitude = location.lat();
        this.$wire.longitude = location.lng();
    },

    reverseGeocode(location) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: location }, (results, status) => {
            if (status === 'OK' && results[0]) {
                this.$wire.googlePlaceId = results[0].place_id;
                this.fillAddressFields({ address_components: results[0].address_components, name: results[0].formatted_address });
                this.$wire.latitude = location.lat();
                this.$wire.longitude = location.lng();
            }
        });
    },

    fillAddressFields(place) {
        const components = place.address_components || [];

        components.forEach(component => {
            const types = component.types;

            if (types.includes('sublocality_level_1')) {
                if (!this.$wire.district) this.$wire.district = component.long_name;
            }
            if (types.includes('administrative_area_level_3')) {
                if (!this.$wire.district) this.$wire.district = component.long_name;
            }
            if (types.includes('locality') || types.includes('administrative_area_level_2')) {
                if (!this.$wire.regency) this.$wire.regency = component.long_name;
            }
            if (types.includes('administrative_area_level_1')) {
                if (!this.$wire.province) this.$wire.province = component.long_name;
            }
            if (types.includes('postal_code')) {
                if (!this.$wire.postalCode) this.$wire.postalCode = component.long_name;
            }
        });
    },

    getCurrentLocation() {
        if (!navigator.geolocation) return;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const pos = { lat: position.coords.latitude, lng: position.coords.longitude };
                this.map.setCenter(pos);
                this.map.setZoom(16);
                this.placeMarker(pos);
                this.reverseGeocode(pos);
            },
            (error) => console.log('Geolocation error:', error)
        );
    }
}">
    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-slate-900">Profil Peternakan</h2>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi peternakan Anda</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Form Section --}}
        <div class="space-y-6">
            {{-- Progress Card --}}
            <div class="rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-50 to-teal-50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Kelengkapan Profil</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Semakin lengkap, semakin terpercaya</p>
                    </div>
                    <div class="text-right">
                        <span class="text-3xl font-bold text-emerald-600">{{ $profileCompletion ?? 0 }}%</span>
                    </div>
                </div>
                <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500"
                        style="width: {{ $profileCompletion ?? 0 }}%"></div>
                </div>
                @if (!empty($missingFields))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($missingFields as $field)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $field }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 flex items-center gap-2 text-emerald-600">
                        <i class="fa-solid fa-circle-check"></i>
                        <span class="text-sm font-medium">Profil lengkap!</span>
                    </div>
                @endif
            </div>

            {{-- Basic Info Card --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Informasi Dasar</h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Peternakan *</label>
                        <input type="text" wire:model.live.debounce.350ms="farmName" required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                            placeholder="Contoh: Peternakan Maju Sejahtera">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Deskripsi</label>
                        <textarea wire:model.live.debounce.350ms="description" rows="3" placeholder="Deskripsikan peternakan Anda..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor HP</label>
                        <input type="text" wire:model.live.debounce.350ms="phone" placeholder="08xxxxxxxxxx"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Desa</label>
                            <input type="text" wire:model.live.debounce.350ms="village"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kecamatan</label>
                            <input type="text" wire:model.live.debounce.350ms="district"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kabupaten/Kota</label>
                            <input type="text" wire:model.live.debounce.350ms="regency"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Provinsi</label>
                            <input type="text" wire:model.live.debounce.350ms="province"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Alamat Lengkap</label>
                        <textarea wire:model.live.debounce.350ms="address" rows="2" placeholder="Alamat lengkap peta..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fa-solid fa-save mr-2"></i>Simpan Profil
                        </button>
                    </div>
                </form>
            </div>

            {{-- Photos Card --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Foto & Media</h3>

                <div class="space-y-4">
                    {{-- Cover Image --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Foto Cover (Banner)</label>
                        <div class="relative">
                            <input type="file" wire:model.live.debounce.500ms="coverImage" accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div
                                class="flex items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-4 transition-all hover:border-emerald-400 hover:bg-emerald-50/50">
                                <div class="text-center">
                                    @if ($coverImage)
                                        <div class="flex items-center justify-center gap-2 text-emerald-600">
                                            <i class="fa-solid fa-check"></i>
                                            <span class="text-sm font-medium">Foto cover dipilih</span>
                                        </div>
                                    @else
                                        <div class="text-slate-400">
                                            <i class="fa-solid fa-image text-2xl mb-1"></i>
                                            <p class="text-xs">Klik atau drag foto cover</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($coverImage)
                            <div
                                class="mt-3 relative h-32 rounded-lg overflow-hidden border border-emerald-200 bg-emerald-50">
                                <img src="{{ $coverImage->temporaryUrl() }}" alt="Cover Preview"
                                    class="w-full h-full object-cover">
                            </div>
                        @elseif ($existingCoverImage)
                            <div
                                class="mt-3 relative h-32 rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                <img src="{{ Storage::url($existingCoverImage) }}" alt="Current Cover"
                                    class="w-full h-full object-cover">
                                <button type="button" wire:click="deleteImage('cover')"
                                    class="absolute right-2 top-2 rounded-full bg-red-500 p-1.5 text-white hover:bg-red-600">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Logo --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Logo Peternakan</label>
                        <div class="relative">
                            <input type="file" wire:model.live.debounce.500ms="logo" accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div
                                class="flex items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-4 transition-all hover:border-emerald-400 hover:bg-emerald-50/50">
                                <div class="text-center">
                                    @if ($logo)
                                        <div class="flex items-center justify-center gap-2 text-emerald-600">
                                            <i class="fa-solid fa-check"></i>
                                            <span class="text-sm font-medium">Logo dipilih</span>
                                        </div>
                                    @else
                                        <div class="text-slate-400">
                                            <i class="fa-solid fa-image text-2xl mb-1"></i>
                                            <p class="text-xs">Klik atau drag logo</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($logo)
                            <div
                                class="mt-3 relative w-20 h-20 rounded-lg overflow-hidden border border-emerald-200 bg-emerald-50">
                                <img src="{{ $logo->temporaryUrl() }}" alt="Logo Preview"
                                    class="w-full h-full object-cover">
                            </div>
                        @elseif ($existingLogo)
                            <div
                                class="mt-3 relative w-20 h-20 rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                <img src="{{ Storage::url($existingLogo) }}" alt="Current Logo"
                                    class="w-full h-full object-cover">
                                <button type="button" wire:click="deleteImage('logo')"
                                    class="absolute right-0 top-0 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 -translate-y-1 translate-x-1">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Main Photo (existing field) --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Foto Utama Peternakan</label>
                        <div class="relative">
                            <input type="file" wire:model.live.debounce.500ms="image" accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div
                                class="flex items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-4 transition-all hover:border-emerald-400 hover:bg-emerald-50/50">
                                <div class="text-center">
                                    @if ($image)
                                        <div class="flex items-center justify-center gap-2 text-emerald-600">
                                            <i class="fa-solid fa-check"></i>
                                            <span class="text-sm font-medium">Foto utama dipilih</span>
                                        </div>
                                    @else
                                        <div class="text-slate-400">
                                            <i class="fa-solid fa-camera text-2xl mb-1"></i>
                                            <p class="text-xs">Klik atau drag foto</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($image)
                            <div
                                class="mt-3 relative w-24 h-24 rounded-lg overflow-hidden border border-emerald-200 bg-emerald-50">
                                <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                                    class="w-full h-full object-cover">
                            </div>
                        @elseif ($existingImage)
                            <div
                                class="mt-3 relative w-24 h-24 rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                <img src="{{ Storage::url($existingImage) }}" alt="Current"
                                    class="w-full h-full object-cover">
                                <button type="button" wire:click="$set('existingImage', null)"
                                    class="absolute right-0 top-0 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 -translate-y-1 translate-x-1">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Gallery Card --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Galeri Foto</h3>
                    <span class="text-xs text-slate-400">{{ count($galleries) }}/10 foto</span>
                </div>

                {{-- Existing Gallery --}}
                @if (count($galleries) > 0)
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach ($galleries as $gallery)
                            <div
                                class="relative group aspect-square rounded-lg overflow-hidden border border-slate-200">
                                <img src="{{ Storage::url($gallery['image']) }}" alt="Gallery"
                                    class="w-full h-full object-cover">
                                <button type="button" wire:click="deleteGallery({{ $gallery['id'] }})"
                                    class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fa-solid fa-trash text-white text-lg"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add New Photos --}}
                @if (count($galleries) < 10)
                    <div>
                        <div class="relative">
                            <input type="file" wire:model.live.debounce.500ms="newGalleryImages" accept="image/*"
                                multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                            <div
                                class="flex items-center justify-center gap-3 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 transition-all hover:border-emerald-400 hover:bg-emerald-50/50">
                                <div class="text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-1"></i>
                                    <p class="text-sm text-slate-500">Tambah foto galeri</p>
                                    <p class="text-xs text-slate-400 mt-1">JPG, PNG, maks 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview New Images --}}
                    @if ($newGalleryImages)
                        <div class="mt-3 grid grid-cols-4 gap-2">
                            @foreach ($newGalleryImages as $key => $img)
                                <div
                                    class="relative aspect-square rounded-lg overflow-hidden border border-emerald-200 bg-emerald-50">
                                    <img src="{{ $img->temporaryUrl() }}" alt="Preview"
                                        class="w-full h-full object-cover">
                                    <button type="button"
                                        wire:click="$remove('newGalleryImages', {{ $key }})"
                                        class="absolute top-1 right-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Location Card --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">Lokasi Peternakan</h3>

                <div class="space-y-4">
                    {{-- Search Box - menggunakan Autocomplete dari Alpine x-data --}}
                    <div class="relative" wire:ignore>
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
                        <input type="text" id="farm-search" placeholder="Cari lokasi peternakan..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                    </div>
                    {{-- Map --}}
                    <div class="relative overflow-hidden rounded-xl border border-slate-200">
                        <div id="farm-map" class="h-[250px] w-full"></div>

                        {{-- Map Controls --}}
                        <div class="absolute bottom-3 left-3 z-10">
                            <button type="button" @click="getCurrentLocation()"
                                class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-lg transition hover:bg-slate-50">
                                <i class="fa-solid fa-location-crosshairs text-emerald-500"></i>
                                Lokasi Saya
                            </button>
                        </div>

                        {{-- Clear Location --}}
                        @if ($latitude && $longitude)
                            <div class="absolute top-3 right-3 z-10">
                                <button type="button" wire:click="clearLocation"
                                    class="flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-lg transition hover:bg-slate-50">
                                    <i class="fa-solid fa-times text-red-500"></i>
                                    Hapus Lokasi
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Coordinates Display --}}
                    <div
                        class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fa-solid fa-globe-americas text-slate-400"></i>
                            <span>Koordinat:</span>
                            @if ($latitude && $longitude)
                                <span class="font-mono font-medium">{{ number_format($latitude, 6) }},
                                    {{ number_format($longitude, 6) }}</span>
                            @else
                                <span class="text-slate-400">Belum dipilih</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400">
                            <i class="fa-solid fa-hand-pointer mr-1"></i>Klik peta atau cari
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview Section --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden">
                {{-- Cover Preview --}}
                <div class="relative h-40 bg-gradient-to-br from-emerald-600 to-emerald-700">
                    @if ($coverImage)
                        <img src="{{ $coverImage->temporaryUrl() }}" alt="Cover"
                            class="h-full w-full object-cover">
                    @elseif($existingCoverImage)
                        <img src="{{ Storage::url($existingCoverImage) }}" alt="Cover"
                            class="h-full w-full object-cover">
                    @endif

                    {{-- Logo Preview --}}
                    <div class="absolute -bottom-8 left-6">
                        <div class="relative">
                            <div class="size-16 rounded-xl border-4 border-white bg-white shadow-lg overflow-hidden">
                                @if ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo"
                                        class="h-full w-full object-cover">
                                @elseif($existingLogo)
                                    <img src="{{ Storage::url($existingLogo) }}" alt="Logo"
                                        class="h-full w-full object-cover">
                                @elseif($image || $existingImage)
                                    <img src="{{ $image?->temporaryUrl() ?? Storage::url($existingImage ?? '') }}"
                                        alt="Farm" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center bg-emerald-100">
                                        <i class="fa-solid fa-farm text-xl text-emerald-600"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Profile Content --}}
                <div class="pt-10 pb-6 px-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-slate-900">{{ $farmName ?: 'Nama Peternakan' }}</h4>
                            @if ($regency || $province)
                                <p class="mt-1 text-sm text-slate-500">
                                    <i class="fa-solid fa-location-dot mr-1"></i>
                                    {{ implode(', ', array_filter([$village, $district, $regency, $province])) }}
                                </p>
                            @endif
                        </div>
                        @if ($isVerified)
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                <i class="fa-solid fa-circle-check"></i>
                                Terverifikasi
                            </span>
                        @endif
                    </div>

                    @if ($phone)
                        <p class="mt-2 text-sm text-slate-600">
                            <i class="fa-solid fa-phone mr-2 text-slate-400"></i>
                            {{ $phone }}
                        </p>
                    @endif

                    @if ($description)
                        <p class="mt-4 text-sm text-slate-600 line-clamp-3">{{ $description }}</p>
                    @endif

                    @if ($latitude && $longitude)
                        <a href="https://www.google.com/maps?q={{ $latitude }},{{ $longitude }}"
                            target="_blank"
                            class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-100">
                            <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                            Lihat di Google Maps
                        </a>
                    @endif

                    {{-- Stats Preview --}}
                    <div class="mt-4 flex items-center gap-4 border-t border-slate-100 pt-4">
                        <div class="text-center">
                            <p class="text-lg font-bold text-slate-900">
                                {{ $stats['active_products'] ?? 0 }}</p>
                            <p class="text-xs text-slate-500">Produk Aktif</p>
                        </div>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-slate-900">
                                {{ $stats['completed_bookings'] ?? 0 }}</p>
                            <p class="text-xs text-slate-500">Booking Selesai</p>
                        </div>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <div class="text-center">
                            <div class="flex items-center gap-1">
                                <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                                <p class="text-lg font-bold text-slate-900">
                                    {{ number_format($stats['rating'] ?? 0, 1) }}</p>
                            </div>
                            <p class="text-xs text-slate-500">{{ $stats['total_reviews'] ?? 0 }}
                                Review</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5 text-blue-500"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium">Tips Profil Menarik</p>
                        <ul class="mt-2 space-y-1 text-blue-600">
                            <li>• Gunakan foto cover dengan resolusi tinggi</li>
                            <li>• Logo membantu brand recognition</li>
                            <li>• Lokasi yang akurat membantu pelanggan</li>
                            <li>• Deskripsi yang menarik meningkatkan kepercayaan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Success Toast --}}
    @if (session('success'))
        <div
            class="fixed bottom-4 right-4 z-50 flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg">
            <i class="fa-solid fa-check-circle"></i>{{ session('success') }}
        </div>
    @endif
</div>
