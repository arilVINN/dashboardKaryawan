@php
    $daftarDivisi = $daftarDivisi ?? collect();
    $divisiTerkunci = $divisiTerkunci ?? null;
    $judulTambah = $judulTambah ?? 'Tambah Karyawan';
@endphp

<input type="hidden" name="_token" value="{{ csrf_token() }}" data-staff-csrf>

<div id="modalTambahKaryawan" data-staff-modal="create">
@livewire('hrd.karyawan-form', [
    'divisiTerkunci' => $divisiTerkunci,
    'judulTambah' => $judulTambah,
])
</div>

<script>
    // Thin bridge: keeps the existing trigger buttons working while the form
    // itself is a single shared Livewire component.
    function bukaModalKaryawan(button) {
        window.__karyawanTrigger = button || document.activeElement;
        Livewire.dispatch('buka-karyawan-form', { mode: 'create' });
    }

    function bukaEditKaryawan(button) {
        if (!button) return;
        if (button.dataset.tanggalKeluar) {
            alert('Karyawan yang sudah keluar tidak dapat diedit kembali.');
            return;
        }
        window.__karyawanTrigger = button;
        Livewire.dispatch('buka-karyawan-form', { mode: 'edit', id: button.dataset.id });
    }

    function tutupModalKaryawan() {
        if (typeof window.kfTutupDenganKonfirmasi === 'function') {
            window.kfTutupDenganKonfirmasi();
        }
    }

    function tutupEditKaryawan() {
        tutupModalKaryawan();
    }

    function karyawanToken() {
        const input = document.querySelector('[data-staff-csrf]');
        return input ? input.value : '';
    }

    async function hapusKaryawan(button) {
        const id = button.dataset.id;
        if (!confirm(`Hapus ${button.dataset.nama} beserta akun loginnya?`)) return;

        try {
            const response = await fetch('{{ url('/hrd/staff') }}/' + encodeURIComponent(id), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': karyawanToken(),
                },
            });
            const text = await response.text();
            let result = {};
            try { result = text ? JSON.parse(text) : {}; } catch (e) {}
            if (!response.ok) {
                alert((result && result.message) || 'Gagal menghapus karyawan.');
                return;
            }
            Livewire.dispatch('karyawan-disimpan');
            showKaryawanToast((result && result.message) || 'Karyawan dihapus.');
        } catch (error) {
            alert('Gagal menghapus karyawan. Periksa koneksi lalu coba lagi.');
        }
    }

    function showKaryawanToast(pesan) {
        const el = document.getElementById('karyawanToast');
        const label = document.getElementById('karyawanToastPesan');
        if (!el || !label) return;
        label.textContent = pesan;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3500);
    }
</script>
