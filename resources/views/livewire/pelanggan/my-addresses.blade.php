<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 text-xl text-white">
                <i class="fa-solid fa-map-marker-alt"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Alamat Pengiriman</h2>
                <p class="mt-0.5 text-emerald-100">Kelola alamat pengiriman Anda</p>
            </div>
        </div>
        <button
            wire:click="openAddModal"
            class="flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
        >
            <i class="fa-solid fa-plus"></i>
            Tambah Alamat
        </button>
    </div>

    {{-- Filters --}}
    @if(count($addresses) > 0)
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-xs">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchFilter"
                    placeholder="Cari alamat..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                >
            </div>

            {{-- Label Filter --}}
            <select
                wire:model.live="labelFilter"
                class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
            >
                <option value="all">Semua Label</option>
                <option value="Rumah">Rumah</option>
                <option value="Kantor">Kantor</option>
                <option value="Lainnya">Lainnya</option>
            </select>

            {{-- Clear Filters --}}
            @if($searchFilter || $labelFilter !== 'all')
                <button
                    wire:click="clearFilters"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-500 transition hover:bg-slate-50"
                >
                    <i class="fa-solid fa-times mr-1"></i> Reset
                </button>
            @endif
        </div>
    @endif

    {{-- Addresses Grid --}}
    @if(count($addresses) > 0)
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($addresses as $address)
                <div class="group relative rounded-2xl border border-slate-100 bg-white p-5 shadow-xs transition hover:shadow-md @if($address['is_default']) ring-2 ring-emerald-500 @endif">
                    {{-- Default Badge --}}
                    @if($address['is_default'])
                        <span class="absolute -top-2 right-4 rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                            <i class="fa-solid fa-star mr-1"></i> Utama
                        </span>
                    @endif

                    {{-- Label --}}
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <i class="fa-solid fa-{{ $address['label'] === 'Rumah' ? 'home' : ($address['label'] === 'Kantor' ? 'building' : 'map-pin') }}"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $address['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $address['recipient_name'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Address Details --}}
                    <div class="space-y-2">
                        <p class="text-sm text-slate-600">
                            <i class="fa-solid fa-user mr-2 w-4 text-slate-400"></i>{{ $address['recipient_name'] }}
                        </p>
                        <p class="text-sm text-slate-600">
                            <i class="fa-solid fa-phone mr-2 w-4 text-slate-400"></i>{{ $address['phone'] }}
                        </p>
                        <p class="text-sm text-slate-600 line-clamp-2">
                            <i class="fa-solid fa-map-pin mr-2 w-4 text-slate-400"></i>
                            {{ $address['address'] }}, {{ $address['district'] }}, {{ $address['city'] }}, {{ $address['province'] }}
                            @if($address['postal_code'])
                                {{ $address['postal_code'] }}
                            @endif
                        </p>
                        @if($address['courier_notes'])
                            <p class="text-xs text-slate-500 italic">
                                <i class="fa-solid fa-sticky-note mr-2 w-4 text-slate-400"></i>{{ Str::limit($address['courier_notes'], 50) }}
                            </p>
                        @endif
                        {{-- Coordinates Badge --}}
                        @if($address['latitude'] && $address['longitude'])
                            <div class="flex items-center gap-1 text-xs text-slate-400">
                                <i class="fa-solid fa-globe"></i>
                                <span>GPS: {{ number_format($address['latitude'], 4) }}, {{ number_format($address['longitude'], 4) }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                        <div class="flex flex-wrap gap-1">
                            <button
                                wire:click="openEditModal({{ $address['id'] }})"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100"
                            >
                                <i class="fa-solid fa-edit mr-1"></i> Edit
                            </button>
                            <button
                                wire:click="copyAddress({{ $address['id'] }})"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-emerald-600 transition hover:bg-emerald-50 {{ $copiedAddressId == $address['id'] ? 'bg-emerald-50' : '' }}"
                            >
                                <i class="fa-solid fa-{{ $copiedAddressId == $address['id'] ? 'check' : 'copy' }} mr-1"></i>
                                {{ $copiedAddressId == $address['id'] ? 'Tersalin' : 'Salin' }}
                            </button>
                            @if(!$address['is_default'])
                                <button
                                    wire:click="setAsDefault({{ $address['id'] }})"
                                    class="rounded-lg px-3 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-50"
                                >
                                    <i class="fa-solid fa-star mr-1"></i> Utama
                                </button>
                            @endif
                        </div>
                        <button
                            wire:click="confirmDelete({{ $address['id'] }})"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50"
                        >
                            <i class="fa-solid fa-trash mr-1"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 py-16 text-center">
            <div class="relative">
                <div class="flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-map-location-dot text-4xl"></i>
                </div>
                <div class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <i class="fa-solid fa-plus text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-700">Belum memiliki alamat</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-md">
                    Tambahkan alamat pertama Anda agar proses booking dan checkout menjadi lebih mudah.
                </p>
            </div>
            <button
                wire:click="openAddModal"
                class="mt-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
                <i class="fa-solid fa-plus mr-2"></i>Tambah Alamat
            </button>
        </div>
    @endif

    {{-- Add/Edit Modal with 2-Step Wizard --}}
    @if($showModal)
        <div
            x-data="{
                open: @entangle('showModal'),
                map: null,
                marker: null,
                autocomplete: null,
                searchInput: null,
                init() {
                    // Wait for modal to be visible
                    this.$nextTick(() => {
                        if (document.getElementById('address-map')) {
                            this.initMap();
                        }
                    });
                },
                initMap() {
                    const defaultLat = this.$wire.latitude || 1.5;
                    const defaultLng = this.$wire.longitude || 125.0;

                    // Initialize map
                    this.map = new google.maps.Map(document.getElementById('address-map'), {
                        zoom: 13,
                        center: { lat: defaultLat, lng: defaultLng },
                        mapTypeId: 'roadmap',
                        mapTypeControl: true,
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                            position: google.maps.ControlPosition.TOP_CENTER,
                        },
                        zoomControl: true,
                        zoomControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_CENTER,
                        },
                        scaleControl: true,
                        streetViewControl: true,
                        streetViewControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_BOTTOM,
                        },
                        fullscreenControl: true,
                        fullscreenControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_TOP,
                        },
                    });

                    // Initialize marker
                    if (this.$wire.latitude && this.$wire.longitude) {
                        this.placeMarker({ lat: this.$wire.latitude, lng: this.$wire.longitude });
                    }

                    // Initialize search autocomplete
                    this.initAutocomplete();

                    // Click listener on map
                    this.map.addListener('click', (e) => {
                        this.placeMarker(e.latLng);
                        this.reverseGeocode(e.latLng);
                    });

                    // Try to get current location
                    this.getCurrentLocation();
                },
                initAutocomplete() {
                    this.searchInput = document.getElementById('address-search');
                    if (!this.searchInput) return;

                    this.autocomplete = new google.maps.places.Autocomplete(this.searchInput, {
                        componentRestrictions: { country: 'id' },
                        fields: ['address_components', 'geometry', 'name', 'place_id'],
                        types: ['address'],
                    });

                    this.autocomplete.addListener('place_changed', () => {
                        const place = this.autocomplete.getPlace();
                        if (!place.geometry) return;

                        // Move map
                        this.map.setCenter(place.geometry.location);
                        this.map.setZoom(16);

                        // Place marker
                        this.placeMarker(place.geometry.location);

                        // Fill form fields
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

                        // Add drag listener
                        this.marker.addListener('dragend', () => {
                            const pos = this.marker.getPosition();
                            this.reverseGeocode(pos);
                        });
                    }

                    // Update Livewire
                    this.$wire.latitude = location.lat();
                    this.$wire.longitude = location.lng();
                },
                reverseGeocode(location) {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ location: location }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            const place = results[0];
                            this.$wire.google_place_id = place.place_id;
                            this.fillAddressFields({ address_components: place.address_components, name: place.formatted_address });

                            // Update Livewire coordinates
                            this.$wire.latitude = location.lat();
                            this.$wire.longitude = location.lng();
                        }
                    });
                },
                fillAddressFields(place) {
                    const components = place.address_components || [];
                    let streetAddress = '';
                    let route = '';
                    let streetNumber = '';

                    components.forEach(component => {
                        const types = component.types;

                        if (types.includes('street_number')) {
                            streetNumber = component.long_name;
                        }
                        if (types.includes('route')) {
                            route = component.long_name;
                        }
                        if (types.includes('sublocality_level_1') || types.includes('sublocality')) {
                            this.$wire.district = component.long_name;
                        }
                        if (types.includes('administrative_area_level_3')) {
                            if (!this.$wire.district) this.$wire.district = component.long_name;
                        }
                        if (types.includes('locality') || types.includes('administrative_area_level_2')) {
                            this.$wire.city = component.long_name;
                        }
                        if (types.includes('administrative_area_level_1')) {
                            this.$wire.province = component.long_name;
                        }
                        if (types.includes('postal_code')) {
                            this.$wire.postal_code = component.long_name;
                        }
                    });

                    streetAddress = [streetNumber, route].filter(Boolean).join(' ');

                    // Also update the address textarea
                    if (place.name && !streetAddress) {
                        streetAddress = place.name;
                    }
                    this.$wire.address = streetAddress || place.name || '';
                },
                getCurrentLocation() {
                    if (!navigator.geolocation) return;

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            };

                            this.map.setCenter(pos);
                            this.map.setZoom(16);
                            this.placeMarker(pos);
                            this.reverseGeocode(pos);
                        },
                        (error) => {
                            console.log('Geolocation error:', error);
                        }
                    );
                }
            }"
            x-show="$wire.showModal"
            x-on:keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
        >
            <div
                x-show="$wire.showModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                class="max-h-[95vh] w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl"
            >
                {{-- Modal Header --}}
                <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            {{ $isEditing ? 'Edit Alamat' : 'Tambah Alamat Baru' }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            Langkah {{ $currentStep }} dari {{ $totalSteps }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Progress Steps --}}
                        <div class="hidden items-center gap-2 sm:flex">
                            <div class="flex items-center gap-1">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold {{ $currentStep >= 1 ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }}">1</span>
                                <span class="text-xs {{ $currentStep >= 1 ? 'text-emerald-600' : 'text-slate-400' }}">Peta</span>
                            </div>
                            <div class="h-px w-8 bg-slate-200"></div>
                            <div class="flex items-center gap-1">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold {{ $currentStep >= 2 ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }}">2</span>
                                <span class="text-xs {{ $currentStep >= 2 ? 'text-emerald-600' : 'text-slate-400' }}">Detail</span>
                            </div>
                        </div>
                        <button
                            wire:click="closeModal"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        >
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>

                {{-- Modal Content --}}
                <div class="overflow-y-auto" style="max-height: calc(95vh - 180px);">
                    <form wire:submit="save">
                        {{-- Step 1: Map --}}
                        @if($currentStep === 1)
                            <div class="space-y-4 p-6">
                                {{-- Search Box --}}
                                <div class="relative">
                                    <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input
                                        type="text"
                                        id="address-search"
                                        x-model="$wire.searchQuery"
                                        placeholder="Cari alamat atau lokasi..."
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    >
                                </div>

                                {{-- Map Container --}}
                                <div class="relative overflow-hidden rounded-xl border border-slate-200">
                                    <div id="address-map" class="h-[350px] w-full"></div>

                                    {{-- Map Controls Overlay --}}
                                    <div class="absolute bottom-4 left-4 z-10 flex flex-col gap-2">
                                        <button
                                            type="button"
                                            @click="getCurrentLocation()"
                                            class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-lg transition hover:bg-slate-50"
                                        >
                                            <i class="fa-solid fa-location-crosshairs text-emerald-500"></i>
                                            Lokasi Saya
                                        </button>
                                    </div>

                                    {{-- Service Area Status --}}
                                    @if($latitude && $longitude)
                                        <div class="absolute top-4 left-4 z-10">
                                            @if($isInServiceArea)
                                                <span class="flex items-center gap-1.5 rounded-full bg-emerald-500/90 px-3 py-1.5 text-xs font-medium text-white backdrop-blur-sm">
                                                    <i class="fa-solid fa-check-circle"></i>
                                                    {{ $serviceAreaMessage ?? 'Dalam area layanan' }}
                                                </span>
                                            @else
                                                <span class="flex items-center gap-1.5 rounded-full bg-amber-500/90 px-3 py-1.5 text-xs font-medium text-white backdrop-blur-sm">
                                                    <i class="fa-solid fa-exclamation-triangle"></i>
                                                    {{ $serviceAreaMessage ?? 'Di luar area layanan' }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Coordinates Display --}}
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <i class="fa-solid fa-globe-americas text-slate-400"></i>
                                        <span>Koordinat:</span>
                                        @if($latitude && $longitude)
                                            <span class="font-mono">{{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}</span>
                                        @else
                                            <span class="text-slate-400">Belum dipilih</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-400">
                                        <i class="fa-solid fa-hand-pointer mr-1"></i>Klik peta atau geser marker
                                    </span>
                                </div>

                                {{-- Location Helper --}}
                                <div class="rounded-lg border border-blue-100 bg-blue-50 p-3">
                                    <div class="flex items-start gap-2">
                                        <i class="fa-solid fa-info-circle mt-0.5 text-blue-500"></i>
                                        <p class="text-xs text-blue-700">
                                            <strong>Tips:</strong> Klik pada peta atau cari alamat untuk memilih lokasi. Anda juga bisa menggeser marker untuk menyesuaikan posisi.
                                        </p>
                                    </div>
                                </div>

                                {{-- Error for location --}}
                                @error('location')
                                    <p class="text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        {{-- Step 2: Address Details --}}
                        @if($currentStep === 2)
                            <div class="space-y-4 p-6">
                                {{-- Selected Location Summary --}}
                                @if($latitude && $longitude)
                                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-map-marker-alt text-emerald-500"></i>
                                                <span class="text-sm text-emerald-700">Lokasi telah dipilih</span>
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="previousStep"
                                                class="text-xs text-emerald-600 hover:text-emerald-800"
                                            >
                                                <i class="fa-solid fa-pen mr-1"></i>Ubah
                                            </button>
                                        </div>
                                        <p class="mt-1 text-xs text-emerald-600">
                                            {{ number_format($latitude, 6) }}, {{ number_format($longitude, 6) }}
                                        </p>
                                    </div>
                                @endif

                                {{-- Label Selection --}}
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Label Alamat</label>
                                    <div class="mt-2 flex gap-2">
                                        @foreach(['Rumah', 'Kantor', 'Lainnya'] as $labelOption)
                                            <button
                                                type="button"
                                                wire:click="$set('label', '{{ $labelOption }}')"
                                                class="flex flex-1 items-center justify-center gap-2 rounded-lg border px-3 py-2.5 text-sm font-medium transition @if($label === $labelOption) border-emerald-500 bg-emerald-50 text-emerald-700 @else border-slate-200 text-slate-600 hover:border-slate-300 @endif"
                                            >
                                                <i class="fa-solid fa-{{ $labelOption === 'Rumah' ? 'home' : ($labelOption === 'Kantor' ? 'building' : 'map-pin') }}"></i>
                                                {{ $labelOption }}
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('label')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Recipient Name --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="recipient_name" class="block text-sm font-medium text-slate-700">Nama Penerima</label>
                                        <input
                                            type="text"
                                            id="recipient_name"
                                            wire:model="recipient_name"
                                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="Nama lengkap penerima"
                                        >
                                        @error('recipient_name')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-slate-700">Nomor Telepon</label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            wire:model="phone"
                                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="08xxxxxxxxxx"
                                        >
                                        @error('phone')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Full Address --}}
                                <div>
                                    <label for="address" class="block text-sm font-medium text-slate-700">Alamat Lengkap</label>
                                    <textarea
                                        id="address"
                                        wire:model="address"
                                        rows="2"
                                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                        placeholder="Jl. Nama Jalan, No. Rumah, RT/RW, Kel."
                                    ></textarea>
                                    @error('address')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Location Fields --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="province" class="block text-sm font-medium text-slate-700">Provinsi</label>
                                        <input
                                            type="text"
                                            id="province"
                                            wire:model="province"
                                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="Contoh: Sulawesi Utara"
                                        >
                                        @error('province')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-slate-700">Kabupaten/Kota</label>
                                        <input
                                            type="text"
                                            id="city"
                                            wire:model="city"
                                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="Contoh: Manado"
                                        >
                                        @error('city')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="district" class="block text-sm font-medium text-slate-700">Kecamatan</label>
                                        <input
                                            type="text"
                                            id="district"
                                            wire:model="district"
                                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="Contoh: Tuminting"
                                        >
                                        @error('district')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-slate-700">Kode Pos</label>
                                        <input
                                            type="text"
                                            id="postal_code"
                                            wire:model="postal_code"
                                            class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                            placeholder="Contoh: 95359"
                                        >
                                        @error('postal_code')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Courier Notes --}}
                                <div>
                                    <label for="courier_notes" class="block text-sm font-medium text-slate-700">
                                        Catatan Kurir <span class="text-slate-400 font-normal">(opsional)</span>
                                    </label>
                                    <textarea
                                        id="courier_notes"
                                        wire:model="courier_notes"
                                        rows="2"
                                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                        placeholder="Contoh: PATOKAN TOKO BANGUNAN MERAH, SEBELAH POM BENSIN"
                                    ></textarea>
                                    <p class="mt-1 text-xs text-slate-400">Catatan ini akan ditunjukkan ke kurir untuk membantu pengiriman.</p>
                                    @error('courier_notes')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Set as Default --}}
                                <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-4">
                                    <input
                                        type="checkbox"
                                        id="is_default"
                                        wire:model="is_default"
                                        class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    >
                                    <label for="is_default" class="text-sm font-medium text-slate-700">
                                        Jadikan sebagai alamat utama
                                    </label>
                                </div>

                                {{-- Service Area Warning --}}
                                @if(!$isInServiceArea)
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <i class="fa-solid fa-exclamation-triangle mt-0.5 text-amber-500"></i>
                                            <div>
                                                <p class="text-sm font-medium text-amber-800">Lokasi di luar area layanan</p>
                                                <p class="mt-1 text-xs text-amber-700">{{ $serviceAreaMessage }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Modal Footer --}}
                        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">
                            <div>
                                @if($currentStep === 2)
                                    <button
                                        type="button"
                                        wire:click="previousStep"
                                        class="flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
                                    >
                                        <i class="fa-solid fa-arrow-left"></i>
                                        Kembali
                                    </button>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    wire:click="closeModal"
                                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
                                >
                                    Batal
                                </button>
                                @if($currentStep === 1)
                                    <button
                                        type="button"
                                        wire:click="nextStep"
                                        class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        Lanjut
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                @else
                                    <button
                                        type="submit"
                                        class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                    >
                                        <i class="fa-solid fa-save"></i>
                                        {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Alamat' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirm && $deletingId)
        <div
            x-data="{ show: @entangle('showDeleteConfirm') }"
            x-show="$wire.showDeleteConfirm"
            x-on:keydown.escape.window="$wire.cancelDelete()"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4"
        >
            <div
                x-show="$wire.showDeleteConfirm"
                x-transition
                class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl"
            >
                <div class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-500">
                        <i class="fa-solid fa-trash text-xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Hapus Alamat?</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Alamat ini akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="mt-6 flex gap-3">
                    <button
                        wire:click="cancelDelete"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="delete({{ $deletingId }})"
                        class="flex-1 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- JavaScript for clipboard copy --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                navigator.clipboard.writeText(event.text).then(() => {
                    console.log('Address copied to clipboard');
                }).catch((err) => {
                    console.error('Failed to copy:', err);
                });
            });

            Livewire.on('reset-copy-status', () => {
                setTimeout(() => {
                    @this.resetCopyStatus();
                }, 2000);
            });
        });
    </script>
    @endpush
</div>
