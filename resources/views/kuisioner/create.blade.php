@extends('layouts.app')

@section('title', $questionnaire ? 'Edit Kuisioner' : 'Buat Kuisioner Baru')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4 mb-6">
            <p class="font-semibold mb-1">Periksa kembali isian Anda:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="kuisioner-form" method="POST"
          action="{{ $questionnaire ? route('admin.kuisioner.update', $questionnaire->id) : route('admin.kuisioner.store') }}">
        @csrf
        @if ($questionnaire) @method('PUT') @endif
        <input type="hidden" name="kategoris_json" id="kategoris_json">

    {{-- ── Header Card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $questionnaire ? 'Edit Kuisioner' : 'Buat Kuisioner Baru' }}</h1>
                <p class="text-sm text-brand-600 mt-1">Kelola kategori dan pertanyaan kuisioner</p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.index', ['tab' => 'kuisioner']) }}"
                   class="flex items-center gap-2 px-4 h-10 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="button" onclick="simpanKuisioner()"
                        class="flex items-center gap-2 px-5 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Kuisioner
                </button>
            </div>
        </div>

        {{-- ── Form Fields ──────────────────────────────────────────────── --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Judul Kuisioner --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Judul Kuisioner <span class="text-brand-600">*</span>
                </label>
                <input
                    type="text"
                    id="judul"
                    name="judul"
                    value="{{ old('judul', $initialData['judul'] ?? '') }}"
                    placeholder="Kuisioner Penelitian 2025"
                    class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                >
            </div>

            {{-- Tahun --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Tahun <span class="text-brand-600">*</span>
                </label>
                <input
                    type="number"
                    id="tahun"
                    name="tahun"
                    value="{{ old('tahun', $initialData['tahun'] ?? date('Y')) }}"
                    min="2000"
                    max="2099"
                    class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                >
            </div>
        </div>

        {{-- Deskripsi --}}
        <div class="mt-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="3"
                placeholder="Deskripsi kuisioner..."
                class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition resize-none"
            >{{ old('deskripsi', $initialData['deskripsi'] ?? '') }}</textarea>
        </div>
    </div>

    {{-- ── Kategori & Pertanyaan Card ───────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-900">Kategori &amp; Pertanyaan</h2>
            <button type="button" onclick="tambahKategori()"
                    class="flex items-center gap-2 px-4 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori
            </button>
        </div>

        {{-- ── Category List (dynamic) ──────────────────────────────────── --}}
        <div id="kategori-container">

            {{-- Empty state (shown when no categories) --}}
            <div id="empty-state" class="border border-dashed border-gray-300 rounded-xl py-12 text-center">
                <p class="text-sm text-gray-500">
                    Belum ada kategori. Klik "Tambah Kategori" untuk memulai.
                </p>
            </div>

            {{-- Kategori items will be injected here by JS --}}
            <div id="kategori-list" class="space-y-4 hidden"></div>
        </div>
    </div>

    </form>
</div>

{{-- ── Modal: Tambah/Edit Kategori ──────────────────────────────────────── --}}
<div id="modal-kategori"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900" id="modal-title">Tambah Kategori</h3>
            <button onclick="tutupModal()" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
            </button>
        </div>

        <div class="space-y-4">
            {{-- Nama Kategori --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nama Kategori <span class="text-brand-600">*</span>
                </label>
                <input
                    type="text"
                    id="modal-nama"
                    placeholder="cth. Persepsi terhadap Kebijakan WFO/WFA"
                    class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                >
            </div>

            {{-- Deskripsi Kategori --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi Kategori</label>
                <input
                    type="text"
                    id="modal-deskripsi"
                    placeholder="Deskripsi singkat kategori ini..."
                    class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                >
            </div>

            {{-- Tipe Kategori --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Kategori</label>
                <select id="modal-tipe"
                        class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800
                               focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    <option value="likert">Dimensi Likert (skala 1-5 &amp; esai)</option>
                    <option value="demografi">Data Demografi (field pilihan)</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <button onclick="tutupModal()"
                    class="px-4 h-10 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                Batal
            </button>
            <button onclick="simpanKategori()"
                    class="flex items-center gap-2 px-5 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                <i data-lucide="check" class="w-4 h-4"></i> Simpan Kategori
            </button>
        </div>
    </div>
</div>

{{-- ── Modal: Tambah Item (pertanyaan / field demografi) ───────────────────── --}}
<div id="modal-pertanyaan"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900" id="modal-pertanyaan-title">Tambah Pertanyaan</h3>
            <button onclick="tutupModalPertanyaan()" class="p-1.5 rounded-lg hover:bg-gray-100 transition">
                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
            </button>
        </div>

        <input type="hidden" id="pertanyaan-kategori-idx">

        {{-- Form: kategori tipe likert (pertanyaan likert / esai) --}}
        <div id="form-item-likert" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Teks Pertanyaan <span class="text-brand-600">*</span>
                </label>
                <textarea
                    id="modal-pertanyaan-teks"
                    rows="3"
                    placeholder="Tuliskan pertanyaan di sini..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition resize-none"
                ></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Pertanyaan</label>
                <select id="modal-pertanyaan-tipe"
                        class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800
                               focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    <option value="likert">Likert 1–5</option>
                    <option value="esai">Esai / Teks Bebas</option>
                </select>
            </div>
        </div>

        {{-- Form: kategori tipe demografi (field pilihan / teks bebas) --}}
        <div id="form-item-demografi" class="space-y-4 hidden">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Label Field <span class="text-brand-600">*</span>
                </label>
                <input
                    type="text"
                    id="modal-field-label"
                    placeholder="cth. Pola Kehadiran"
                    class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tipe Field</label>
                <select id="modal-field-tipe" onchange="toggleOpsiField()"
                        class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800
                               focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    <option value="choice">Pilihan (dropdown)</option>
                    <option value="text">Teks Bebas</option>
                </select>
            </div>
            <div id="modal-field-opsi-wrap">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Pilihan Jawaban <span class="text-brand-600">*</span>
                    <span class="font-normal text-gray-400">(satu per baris)</span>
                </label>
                <textarea
                    id="modal-field-opsi"
                    rows="4"
                    placeholder="WFA penuh&#10;WFO 2x/minggu&#10;WFO 3x/minggu"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm text-gray-800 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition resize-none"
                ></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <button onclick="tutupModalPertanyaan()"
                    class="px-4 h-10 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                Batal
            </button>
            <button onclick="simpanPertanyaan()"
                    class="flex items-center gap-2 px-5 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                <i data-lucide="check" class="w-4 h-4"></i> Tambah
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* ── State ───────────────────────────────────────────────────────────────── */
// kategoris: [{nama, deskripsi, tipe: 'likert'|'demografi', items: []}]
//   - tipe likert:    items = [{jenis: 'likert'|'esai', teks}]
//   - tipe demografi: items = [{jenis: 'choice'|'text', label, options: []}]
let kategoris = @json($initialData['kategoris'] ?? []);
let editIdx   = null; // index kategori yang sedang diedit

/* ── Helpers ─────────────────────────────────────────────────────────────── */
function tipeLabel(tipe) {
    return { likert: 'Dimensi Likert', demografi: 'Demografi' }[tipe] || tipe;
}
function tipeBadgeColor(tipe) {
    return {
        likert:    'bg-blue-100 text-blue-700',
        demografi: 'bg-purple-100 text-purple-700',
    }[tipe] || 'bg-gray-100 text-gray-600';
}
function itemJenisLabel(kat, item) {
    if (kat.tipe === 'demografi') {
        return item.jenis === 'text' ? 'Teks Bebas' : 'Pilihan';
    }
    return item.jenis === 'esai' ? 'Esai' : 'Likert 1–5';
}
function itemText(kat, item) {
    return kat.tipe === 'demografi' ? item.label : item.teks;
}

/* ── Render ──────────────────────────────────────────────────────────────── */
function render() {
    const list       = document.getElementById('kategori-list');
    const emptyState = document.getElementById('empty-state');

    if (kategoris.length === 0) {
        emptyState.classList.remove('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';
        return;
    }

    emptyState.classList.add('hidden');
    list.classList.remove('hidden');

    list.innerHTML = kategoris.map((kat, idx) => `
        <div class="border border-gray-200 rounded-xl overflow-hidden">

            {{-- Kategori Header --}}
            <div class="flex items-center justify-between px-5 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-brand-600 text-white text-xs font-bold
                                 flex items-center justify-center">${idx + 1}</span>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">${kat.nama}</p>
                        ${kat.deskripsi ? `<p class="text-xs text-gray-500 truncate mt-0.5">${kat.deskripsi}</p>` : ''}
                    </div>
                    <span class="flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold ${tipeBadgeColor(kat.tipe)}">
                        ${tipeLabel(kat.tipe)}
                    </span>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0 ml-3">
                    <button type="button" onclick="editKategori(${idx})"
                            class="p-2 rounded-lg text-gray-500 hover:bg-white hover:text-brand-600 transition"
                            title="Edit kategori">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                    <button type="button" onclick="hapusKategori(${idx})"
                            class="p-2 rounded-lg text-gray-500 hover:bg-white hover:text-red-600 transition"
                            title="Hapus kategori">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            {{-- Item List (pertanyaan / field demografi) --}}
            <div class="px-5 py-3 space-y-2">
                ${(kat.items || []).length === 0
                    ? `<p class="text-xs text-gray-400 py-1">Belum ada ${kat.tipe === 'demografi' ? 'field' : 'pertanyaan'}.</p>`
                    : kat.items.map((item, pIdx) => `
                        <div class="flex items-start gap-3 py-2 border-b border-gray-100 last:border-0">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-gray-200 text-gray-600 text-xs font-semibold
                                         flex-shrink-0 flex items-center justify-center">${pIdx + 1}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700">${itemText(kat, item)}</p>
                                ${kat.tipe === 'demografi' && item.jenis !== 'text' && (item.options || []).length
                                    ? `<p class="text-xs text-gray-400 mt-0.5 truncate">${item.options.join(', ')}</p>`
                                    : ''}
                            </div>
                            <span class="flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                ${itemJenisLabel(kat, item)}
                            </span>
                            <button type="button" onclick="hapusPertanyaan(${idx}, ${pIdx})"
                                    class="flex-shrink-0 p-1 rounded text-gray-400 hover:text-red-500 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    `).join('')
                }

                {{-- Add Item Button --}}
                <button type="button" onclick="bukaModalPertanyaan(${idx})"
                        class="flex items-center gap-2 mt-2 px-3 py-1.5 rounded-lg border border-dashed border-gray-300
                               text-xs font-semibold text-gray-500 hover:border-brand-400 hover:text-brand-600
                               hover:bg-brand-50 transition w-full justify-center">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah ${kat.tipe === 'demografi' ? 'Field' : 'Pertanyaan'}
                </button>
            </div>
        </div>
    `).join('');

    // Re-init lucide icons for dynamically inserted elements
    if (window.lucide) lucide.createIcons();
}

/* ── Modal: Kategori ─────────────────────────────────────────────────────── */
function tambahKategori() {
    editIdx = null;
    document.getElementById('modal-title').textContent   = 'Tambah Kategori';
    document.getElementById('modal-nama').value          = '';
    document.getElementById('modal-deskripsi').value     = '';
    document.getElementById('modal-tipe').value          = 'likert';
    document.getElementById('modal-kategori').classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
}

function editKategori(idx) {
    editIdx = idx;
    const kat = kategoris[idx];
    document.getElementById('modal-title').textContent   = 'Edit Kategori';
    document.getElementById('modal-nama').value          = kat.nama;
    document.getElementById('modal-deskripsi').value     = kat.deskripsi || '';
    document.getElementById('modal-tipe').value          = kat.tipe;
    document.getElementById('modal-kategori').classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
}

function tutupModal() {
    document.getElementById('modal-kategori').classList.add('hidden');
}

function simpanKategori() {
    const nama = document.getElementById('modal-nama').value.trim();
    if (!nama) {
        document.getElementById('modal-nama').focus();
        return;
    }

    const obj = {
        nama:      nama,
        deskripsi: document.getElementById('modal-deskripsi').value.trim(),
        tipe:      document.getElementById('modal-tipe').value,
        items:     editIdx !== null ? kategoris[editIdx].items : [],
    };

    if (editIdx !== null) {
        kategoris[editIdx] = obj;
    } else {
        kategoris.push(obj);
    }

    tutupModal();
    render();
}

function hapusKategori(idx) {
    if (!confirm('Hapus kategori ini beserta semua isinya?')) return;
    kategoris.splice(idx, 1);
    render();
}

/* ── Modal: Item (pertanyaan likert/esai ATAU field demografi) ───────────── */
function toggleOpsiField() {
    const isChoice = document.getElementById('modal-field-tipe').value === 'choice';
    document.getElementById('modal-field-opsi-wrap').classList.toggle('hidden', !isChoice);
}

function bukaModalPertanyaan(katIdx) {
    const kat = kategoris[katIdx];
    document.getElementById('pertanyaan-kategori-idx').value = katIdx;

    const isDemografi = kat.tipe === 'demografi';
    document.getElementById('modal-pertanyaan-title').textContent = isDemografi ? 'Tambah Field Demografi' : 'Tambah Pertanyaan';
    document.getElementById('form-item-likert').classList.toggle('hidden', isDemografi);
    document.getElementById('form-item-demografi').classList.toggle('hidden', !isDemografi);

    if (isDemografi) {
        document.getElementById('modal-field-label').value = '';
        document.getElementById('modal-field-tipe').value  = 'choice';
        document.getElementById('modal-field-opsi').value  = '';
        toggleOpsiField();
    } else {
        document.getElementById('modal-pertanyaan-teks').value = '';
        document.getElementById('modal-pertanyaan-tipe').value = 'likert';
    }

    document.getElementById('modal-pertanyaan').classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
}

function tutupModalPertanyaan() {
    document.getElementById('modal-pertanyaan').classList.add('hidden');
}

function simpanPertanyaan() {
    const katIdx = parseInt(document.getElementById('pertanyaan-kategori-idx').value);
    const kat    = kategoris[katIdx];

    if (kat.tipe === 'demografi') {
        const label = document.getElementById('modal-field-label').value.trim();
        if (!label) {
            document.getElementById('modal-field-label').focus();
            return;
        }
        const jenis = document.getElementById('modal-field-tipe').value;
        const options = jenis === 'choice'
            ? document.getElementById('modal-field-opsi').value.split('\n').map(o => o.trim()).filter(Boolean)
            : [];

        if (jenis === 'choice' && options.length < 2) {
            alert('Field pilihan minimal harus punya 2 opsi jawaban.');
            document.getElementById('modal-field-opsi').focus();
            return;
        }

        kategoris[katIdx].items.push({ jenis, label, options });
    } else {
        const teks = document.getElementById('modal-pertanyaan-teks').value.trim();
        if (!teks) {
            document.getElementById('modal-pertanyaan-teks').focus();
            return;
        }
        const jenis = document.getElementById('modal-pertanyaan-tipe').value;
        kategoris[katIdx].items.push({ jenis, teks });
    }

    tutupModalPertanyaan();
    render();
}

function hapusPertanyaan(katIdx, pIdx) {
    kategoris[katIdx].items.splice(pIdx, 1);
    render();
}

/* ── Aksi utama ──────────────────────────────────────────────────────────── */
function simpanKuisioner() {
    const judul = document.getElementById('judul').value.trim();
    const tahun = document.getElementById('tahun').value.trim();

    if (!judul) {
        document.getElementById('judul').focus();
        alert('Judul kuisioner wajib diisi.');
        return;
    }
    if (!tahun) {
        document.getElementById('tahun').focus();
        alert('Tahun wajib diisi.');
        return;
    }
    if (kategoris.length === 0) {
        alert('Tambahkan minimal 1 kategori sebelum menyimpan.');
        return;
    }

    document.getElementById('kategoris_json').value = JSON.stringify(kategoris);
    document.getElementById('kuisioner-form').submit();
}

/* ── Close modal on backdrop click ──────────────────────────────────────── */
document.getElementById('modal-kategori').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});
document.getElementById('modal-pertanyaan').addEventListener('click', function(e) {
    if (e.target === this) tutupModalPertanyaan();
});

/* ── Initial render ──────────────────────────────────────────────────────── */
render();
</script>
@endpush
