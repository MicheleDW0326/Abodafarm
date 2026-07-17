<div class="min-h-screen bg-gradient-to-br from-slate-50 to-emerald-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Import Produk</h1>
                    <p class="text-slate-600 mt-1">Import produk secara massal dari file Excel</p>
                </div>
                <a href="{{ route('admin.products') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Error Alert -->
        @if($hasError)
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <i class="fa-solid fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-red-800">Terjadi Kesalahan</h4>
                        <p class="text-red-600 mt-1">{{ $errorMessage }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 1: Upload -->
        @if($step === 'upload')
            <div class="space-y-6">
                <!-- Download Template Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <i class="fa-solid fa-file-excel text-xl text-emerald-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-800">Download Template</h3>
                            <p class="text-sm text-slate-500">Pilih template sesuai jenis produk yang akan diimport</p>
                        </div>
                    </div>

                    <!-- Template Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Livestock Template -->
                        <a href="{{ route('admin.templates.download', 'livestock') }}"
                            class="group relative overflow-hidden rounded-xl border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-5 transition-all hover:border-amber-400 hover:shadow-md">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-200/30 rounded-bl-full"></div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-cow text-2xl text-amber-600"></i>
                                </div>
                                <h4 class="font-semibold text-slate-800 mb-1">Template Produk Ternak</h4>
                                <p class="text-sm text-slate-500 mb-3">Sapi, kambing, ayam, dll</p>
                                <div class="flex items-center gap-2 text-amber-600 font-medium text-sm">
                                    <i class="fa-solid fa-download"></i>
                                    Download
                                </div>
                            </div>
                        </a>

                        <!-- Fresh Meat Template -->
                        <a href="{{ route('admin.templates.download', 'fresh') }}"
                            class="group relative overflow-hidden rounded-xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-cyan-50 p-5 transition-all hover:border-blue-400 hover:shadow-md">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-blue-200/30 rounded-bl-full"></div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-drumstick-bite text-2xl text-blue-600"></i>
                                </div>
                                <h4 class="font-semibold text-slate-800 mb-1">Template Produk Daging</h4>
                                <p class="text-sm text-slate-500 mb-3">Daging sapi, ayam, dll</p>
                                <div class="flex items-center gap-2 text-blue-600 font-medium text-sm">
                                    <i class="fa-solid fa-download"></i>
                                    Download
                                </div>
                            </div>
                        </a>

                        <!-- Supply Template -->
                        <a href="{{ route('admin.templates.download', 'supply') }}"
                            class="group relative overflow-hidden rounded-xl border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-5 transition-all hover:border-emerald-400 hover:shadow-md">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-200/30 rounded-bl-full"></div>
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-box-open text-2xl text-emerald-600"></i>
                                </div>
                                <h4 class="font-semibold text-slate-800 mb-1">Template Pakan & Suplimen</h4>
                                <p class="text-sm text-slate-500 mb-3">Pakan, vitamin, obat-obatan</p>
                                <div class="flex items-center gap-2 text-emerald-600 font-medium text-sm">
                                    <i class="fa-solid fa-download"></i>
                                    Download
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Workflow Steps -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-500"></i>
                        Alur Kerja Import
                    </h3>

                    <div class="flex flex-col md:flex-row md:items-center justify-center gap-3 md:gap-0">
                        <!-- Step 1 -->
                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center font-semibold text-sm shadow-sm">
                                    1
                                </div>
                                <span class="text-xs font-medium text-slate-600 mt-1.5 whitespace-nowrap">Download Template</span>
                            </div>
                            <div class="hidden md:block w-8 h-0.5 bg-slate-200 mx-1"></div>
                            <div class="md:hidden w-0.5 h-6 bg-slate-200 mx-auto"></div>
                        </div>

                        <!-- Arrow -->
                        <div class="hidden md:flex items-center">
                            <i class="fa-solid fa-arrow-right text-slate-300 text-sm mx-1"></i>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-semibold text-sm border-2 border-dashed border-slate-300">
                                    2
                                </div>
                                <span class="text-xs font-medium text-slate-500 mt-1.5 whitespace-nowrap">Fill the Excel</span>
                            </div>
                            <div class="hidden md:block w-8 h-0.5 bg-slate-200 mx-1"></div>
                            <div class="md:hidden w-0.5 h-6 bg-slate-200 mx-auto"></div>
                        </div>

                        <!-- Arrow -->
                        <div class="hidden md:flex items-center">
                            <i class="fa-solid fa-arrow-right text-slate-300 text-sm mx-1"></i>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-semibold text-sm border-2 border-dashed border-slate-300">
                                    3
                                </div>
                                <span class="text-xs font-medium text-slate-500 mt-1.5 whitespace-nowrap">Upload Excel</span>
                            </div>
                            <div class="hidden md:block w-8 h-0.5 bg-slate-200 mx-1"></div>
                            <div class="md:hidden w-0.5 h-6 bg-slate-200 mx-auto"></div>
                        </div>

                        <!-- Arrow -->
                        <div class="hidden md:flex items-center">
                            <i class="fa-solid fa-arrow-right text-slate-300 text-sm mx-1"></i>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-semibold text-sm border-2 border-dashed border-slate-300">
                                    4
                                </div>
                                <span class="text-xs font-medium text-slate-500 mt-1.5 whitespace-nowrap">Validate Data</span>
                            </div>
                            <div class="hidden md:block w-8 h-0.5 bg-slate-200 mx-1"></div>
                            <div class="md:hidden w-0.5 h-6 bg-slate-200 mx-auto"></div>
                        </div>

                        <!-- Arrow -->
                        <div class="hidden md:flex items-center">
                            <i class="fa-solid fa-arrow-right text-slate-300 text-sm mx-1"></i>
                        </div>

                        <!-- Step 5 -->
                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-semibold text-sm border-2 border-dashed border-slate-300">
                                    5
                                </div>
                                <span class="text-xs font-medium text-slate-500 mt-1.5 whitespace-nowrap">Import Products</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="bg-blue-50 rounded-2xl border border-blue-200 p-5">
                    <h4 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info"></i>
                        Informasi Penting
                    </h4>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-500 mt-0.5"></i>
                            <span>Gunakan template resmi yang telah disediakan</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-500 mt-0.5"></i>
                            <span>Jangan mengubah atau menghapus nama kolom header</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check-circle text-blue-500 mt-0.5"></i>
                            <span>Kolom wajib (Nama Produk, Kategori, Harga, Stok) tidak boleh kosong</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-xmark-circle text-blue-500 mt-0.5"></i>
                            <span>Images cannot be imported from Excel</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-xmark-circle text-blue-500 mt-0.5"></i>
                            <span>Products with invalid data will not be imported</span>
                        </li>
                    </ul>
                </div>

                <!-- Upload Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-cloud-upload-alt text-emerald-500"></i>
                        Upload File Excel
                    </h3>

                    <div x-data="{ isDragging: false }" class="relative">
                        <div x-show="isDragging"
                            class="absolute inset-0 z-10 bg-emerald-50/90 rounded-xl border-2 border-dashed border-emerald-400 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fa-solid fa-cloud-upload-alt text-4xl text-emerald-500 mb-2"></i>
                                <p class="text-emerald-700 font-medium">Lepaskan file di sini</p>
                            </div>
                        </div>

                        <div x-on:dragover.prevent="isDragging = true"
                            x-on:dragleave.prevent="isDragging = false"
                            x-on:drop.prevent="isDragging = false"
                            class="border-2 border-dashed border-slate-300 rounded-xl p-12 text-center transition"
                            :class="{ 'border-emerald-400 bg-emerald-50': isDragging }">

                            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                                <i class="fa-solid fa-file-import text-3xl text-slate-400"></i>
                            </div>

                            <h3 class="text-lg font-semibold text-slate-700 mb-2">Drag & Drop File Excel</h3>
                            <p class="text-slate-500 mb-6">atau klik untuk memilih file</p>

                            <input type="file"
                                wire:model="file"
                                accept=".csv,.xls,.xlsx"
                                class="hidden"
                                id="file-upload">

                            <label for="file-upload"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition cursor-pointer">
                                <i class="fa-solid fa-folder-open"></i>
                                Pilih File
                            </label>

                            <p class="text-xs text-slate-400 mt-4">Format: .csv, .xls, .xlsx | Maksimal: 10MB</p>
                        </div>

                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Preview -->
                    @if($file)
                        <div class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700">{{ $file->getClientOriginalName() }}</p>
                                        <p class="text-sm text-slate-500">{{ number_format($file->getSize() / 1024, 1) }} KB</p>
                                    </div>
                                </div>
                                <button wire:click="$set('file', null)"
                                    class="p-2 text-slate-400 hover:text-red-500 transition">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button wire:click="processFile"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition shadow-sm">
                                <i class="fa-solid fa-eye"></i>
                                Preview & Validasi
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Step 2: Preview -->
        @if($step === 'preview')
            <div class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fa-solid fa-list text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500">Total Baris</p>
                                <p class="text-xl font-bold text-slate-800">{{ $totalRows }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <i class="fa-solid fa-check-circle text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500">Valid</p>
                                <p class="text-xl font-bold text-emerald-600">{{ $validCount }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                                <i class="fa-solid fa-times-circle text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500">Invalid</p>
                                <p class="text-xl font-bold text-red-600">{{ $invalidCount }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                <i class="fa-solid fa-hourglass-half text-amber-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-500">Siap Import</p>
                                <p class="text-xl font-bold text-amber-600">{{ $validCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Valid Rows Table -->
                @if(count($validRows) > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-emerald-50">
                            <h3 class="font-semibold text-emerald-800 flex items-center gap-2">
                                <i class="fa-solid fa-check-circle"></i>
                                Produk yang akan diimport ({{ count($validRows) }})
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">#</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Nama Produk</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Kategori</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Tipe</th>
                                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Harga</th>
                                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Stok</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-600">Unggulan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($validRows as $row)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-4 py-3 text-slate-500">{{ $row['row_number'] }}</td>
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $row['data']['name'] }}</td>
                                            <td class="px-4 py-3 text-slate-600">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ $row['data']['category_name'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $typeColors = [
                                                        'live' => 'bg-amber-100 text-amber-700',
                                                        'fresh' => 'bg-blue-100 text-blue-700',
                                                        'supply' => 'bg-emerald-100 text-emerald-700',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$row['data']['type']] ?? 'bg-slate-100 text-slate-600' }}">
                                                    {{ ucfirst($row['data']['type']) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-slate-800">
                                                Rp {{ number_format($row['data']['price'], 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-slate-600">{{ $row['data']['stock'] }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($row['data']['is_featured'])
                                                    <i class="fa-solid fa-star text-amber-500"></i>
                                                @else
                                                    <i class="fa-regular fa-star text-slate-300"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Invalid Rows Table -->
                @if(count($invalidRows) > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                            <h3 class="font-semibold text-red-800 flex items-center gap-2">
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                Baris dengan Error ({{ count($invalidRows) }})
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-red-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-red-700">Baris</th>
                                        <th class="px-4 py-3 text-left font-semibold text-red-700">Nama Produk</th>
                                        <th class="px-4 py-3 text-left font-semibold text-red-700">Error</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-red-100">
                                    @foreach($invalidRows as $row)
                                        <tr class="bg-red-50/50">
                                            <td class="px-4 py-3 font-medium text-red-700">{{ $row['row_number'] }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $row['data'][0] ?? '-' }}</td>
                                            <td class="px-4 py-3">
                                                <ul class="space-y-1">
                                                    @foreach($row['errors'] as $error)
                                                        <li class="text-red-600 flex items-start gap-2">
                                                            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                                                            {{ $error }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center justify-between">
                    <button wire:click="cancelImport"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-slate-600 bg-white border border-slate-200 font-medium rounded-xl hover:bg-slate-50 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        Batal & Upload Ulang
                    </button>

                    @if($validCount > 0)
                        <button wire:click="confirmImport"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition shadow-sm">
                            <i class="fa-solid fa-upload"></i>
                            Import {{ $validCount }} Produk
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Step 3: Importing -->
        @if($step === 'importing')
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12">
                <div class="max-w-md mx-auto text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-6">
                        <i class="fa-solid fa-spinner fa-spin text-4xl text-emerald-600"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 mb-2">Sedang Mengimport...</h2>
                    <p class="text-slate-600 mb-8">{{ $progressMessage }}</p>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                            style="width: {{ $progress }}%">
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">{{ $progress }}%</p>
                </div>
            </div>
        @endif

        <!-- Step 4: Complete -->
        @if($step === 'complete')
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="max-w-md mx-auto text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-6">
                        <i class="fa-solid fa-check text-4xl text-emerald-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">Import Selesai!</h2>
                    <p class="text-slate-600 mb-8">Produk berhasil diimport ke database</p>

                    <!-- Summary -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                            <p class="text-sm text-emerald-600">Berhasil Import</p>
                            <p class="text-2xl font-bold text-emerald-700">{{ $importedCount }}</p>
                        </div>
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                            <p class="text-sm text-amber-600">Dilewati</p>
                            <p class="text-2xl font-bold text-amber-700">{{ $skippedCount }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('admin.products') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition shadow-sm">
                            <i class="fa-solid fa-box"></i>
                            Lihat Produk
                        </a>
                        <button wire:click="resetForm"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-slate-700 font-medium rounded-xl border border-slate-200 hover:bg-slate-50 transition">
                            <i class="fa-solid fa-plus"></i>
                            Import Lagi
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
