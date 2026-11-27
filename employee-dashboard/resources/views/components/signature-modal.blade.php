@props([
    'id',
    'title',
    'description',
    'action',
    'previewUrl',
    'employee',
    'hasSignature' => false,
    'submitLabel' => 'Simpan Tanda Tangan',
])

@php
    $previewSrc = $previewUrl.(str_contains($previewUrl, '?') ? '&' : '?')
        .'v='.($employee->updated_at?->timestamp ?? time());

    $uploadId = $id.'Upload';
    $canvasId = $id.'Canvas';
    $dataId = $id.'Data';
    $uploadPreviewId = $id.'UploadPreview';
    $uploadPreviewWrapId = $id.'UploadPreviewWrap';
    $drawPreviewId = $id.'DrawPreview';
    $drawPreviewWrapId = $id.'DrawPreviewWrap';
    $drawErrorId = $id.'DrawError';
    $clearId = $id.'Clear';
@endphp

<div id="{{ $id }}"
     class="opacity-0 pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4
            bg-[rgba(15,40,55,.45)] transition-opacity duration-150 ease-in-out"
     role="presentation">

    <form data-modal-panel
          method="POST"
          action="{{ $action }}"
          enctype="multipart/form-data"
          role="dialog"
          aria-modal="true"
          aria-labelledby="{{ $id }}Title"
          class="flex max-h-[92vh] w-full max-w-[560px] flex-col overflow-hidden rounded-xl bg-white touch-manipulation
                 shadow-[0_8px_16px_rgba(0,0,0,0.12)] scale-95 translate-y-2 transition-all duration-150 ease-in-out">
        @csrf
        <input type="hidden" name="tanda_tangan_data" id="{{ $dataId }}">

        <div class="flex items-start justify-between gap-4 border-b border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4">
            <div class="min-w-0">
                <h3 id="{{ $id }}Title" class="text-[15px] font-semibold leading-6 text-[#172B36]">
                    {{ $title }}
                </h3>
                <p class="mt-0.5 text-[12px] leading-5 text-[#4B5563]">
                    {{ $description }}
                </p>
            </div>
            <button type="button" data-modal-close="{{ $id }}" aria-label="Tutup"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#565E74] transition hover:bg-[#F1F5F9] hover:text-[#172B36]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
            @if ($hasSignature)
                <div class="rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] px-4 py-4">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#4B5563]">Tanda tangan saat ini</p>
                    <img src="{{ $previewSrc }}"
                         alt="Tanda tangan {{ $employee->nama }}"
                         class="pointer-events-none mx-auto h-auto w-[55mm] max-w-full select-none rounded-md bg-white object-contain p-1" draggable="false">
                    <p class="mt-2 text-center text-[10px] text-[#94A3B8]">Pratinjau ukuran cetak ± 55 mm</p>
                </div>
            @else
                <div class="rounded-lg border border-dashed border-[#CBD5E1] bg-[#F8FAFC] px-4 py-6 text-center">
                    <p class="text-[13px] font-medium text-[#334155]">Belum ada tanda tangan</p>
                    <p class="mt-1 text-[11px] text-[#64748B]">Unggah gambar yang jelas atau gambar tanda tangan langsung.</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-1 rounded-lg bg-[#F1F5F9] p-1" role="tablist" aria-label="Metode tanda tangan">
                <button type="button" data-sig-tab="upload"
                        class="h-8 rounded-md bg-white text-[12px] font-semibold text-[#0F5878] shadow-sm">
                    Unggah
                </button>
                <button type="button" data-sig-tab="draw"
                        class="h-8 rounded-md text-[12px] font-medium text-[#4B5563]">
                    Gambar
                </button>
            </div>

            <div data-sig-panel="upload">
                <label for="{{ $uploadId }}" class="mb-1.5 block text-[12px] font-medium text-[#334155]">
                    File tanda tangan (PNG/JPG)
                </label>
                <input id="{{ $uploadId }}" type="file" name="tanda_tangan"
                       accept="image/png,image/jpeg" required
                       class="block w-full cursor-pointer rounded-lg border border-[#D5DEE3] bg-white text-[12px] text-[#4B5563]
                              file:mr-3 file:cursor-pointer file:border-0 file:bg-[#EAF6FA] file:px-3 file:py-2
                              file:text-[12px] file:font-medium file:text-[#146C94]">
                <p class="mt-1 text-[11px] text-[#64748B]">
                    Maksimal 5 MB. Gunakan gambar dengan latar putih atau transparan. Sistem akan memangkas ruang kosong dan menghapus latar putih.
                </p>

                <div id="{{ $uploadPreviewWrapId }}" class="mt-3 hidden rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] px-4 py-3">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#4B5563]">Pratinjau file (± 55 mm)</p>
                    <img id="{{ $uploadPreviewId }}" alt="Pratinjau tanda tangan"
                         class="pointer-events-none mx-auto h-auto w-[55mm] max-w-full select-none rounded-md bg-white object-contain p-1" draggable="false">
                    <p class="mt-2 text-center text-[10px] text-[#94A3B8]">Latar putih dan ruang kosong akan diproses otomatis saat disimpan.</p>
                </div>
            </div>

            <div data-sig-panel="draw" class="hidden">
                <label class="mb-1.5 block text-[12px] font-medium text-[#334155]">Gambar tanda tangan</label>
                <div class="rounded-lg border border-[#D5DEE3] bg-white p-1">
                    <canvas id="{{ $canvasId }}" width="900" height="300"
                            class="block w-full touch-none rounded-md"
                            style="aspect-ratio: 3 / 1; height: auto;"></canvas>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-[11px] text-[#64748B]">Gunakan mouse atau sentuhan untuk menggambar.</p>
                    <button type="button" id="{{ $clearId }}"
                            class="inline-flex h-8 items-center rounded-lg border border-[#D5DEE3] bg-white px-3 text-[11px] font-medium text-[#172B36] transition hover:bg-[#F1F5F9]">
                        Hapus
                    </button>
                </div>

                <div id="{{ $drawPreviewWrapId }}" class="mt-3 hidden rounded-lg border border-[#E2E8EC] bg-[#F8FAFC] px-4 py-3">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.08em] text-[#4B5563]">Pratinjau gambar (± 55 mm)</p>
                    <img id="{{ $drawPreviewId }}" alt="Pratinjau tanda tangan"
                         class="pointer-events-none mx-auto h-auto w-[55mm] max-w-full select-none rounded-md bg-white object-contain p-1" draggable="false">
                    <p class="mt-2 text-center text-[10px] text-[#94A3B8]">Pratinjau ukuran cetak ± 55 mm</p>
                </div>

                <p id="{{ $drawErrorId }}" class="mt-2 hidden text-[11px] text-red-600">
                    Gambarkan tanda tangan terlebih dahulu sebelum menyimpan.
                </p>
            </div>

            @error('tanda_tangan')
                <p class="text-[11px] text-red-600">{{ $message }}</p>
            @enderror
            @error('tanda_tangan_data')
                <p class="text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-[#E2E8F0] bg-[#F8FAFC] px-6 py-4">
            <button type="button" data-modal-close="{{ $id }}"
                    class="inline-flex h-9 items-center justify-center rounded-lg border border-[#D5DEE3] bg-white px-4 text-[13px] font-medium text-[#172B36] transition hover:bg-[#F1F5F9]">
                Batal
            </button>
            <button type="submit"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-[#146C94] px-4 text-[13px] font-semibold text-white transition hover:bg-[#0f5878]">
                {{ $submitLabel }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </form>
</div>

@include('components.modal-scripts')

<script>
(function () {
    const modal = document.getElementById(@json($id));
    if (!modal) return;

    const form = modal.querySelector('form');
    const fileInput = document.getElementById(@json($uploadId));
    const dataInput = document.getElementById(@json($dataId));
    const canvas = document.getElementById(@json($canvasId));
    const uploadPreview = document.getElementById(@json($uploadPreviewId));
    const uploadPreviewWrap = document.getElementById(@json($uploadPreviewWrapId));
    const drawPreview = document.getElementById(@json($drawPreviewId));
    const drawPreviewWrap = document.getElementById(@json($drawPreviewWrapId));
    const drawError = document.getElementById(@json($drawErrorId));
    const clearButton = document.getElementById(@json($clearId));
    const tabs = modal.querySelectorAll('[data-sig-tab]');
    const panels = modal.querySelectorAll('[data-sig-panel]');

    let mode = 'upload';
    let drawing = false;
    let hasInk = false;
    let objectUrl = null;
    let context = null;

    function setActiveTab(target) {
        tabs.forEach(function (tab) {
            const active = tab.dataset.sigTab === target;
            tab.classList.toggle('bg-white', active);
            tab.classList.toggle('shadow-sm', active);
            tab.classList.toggle('font-semibold', active);
            tab.classList.toggle('text-[#0F5878]', active);
            tab.classList.toggle('font-medium', !active);
            tab.classList.toggle('text-[#4B5563]', !active);
        });

        panels.forEach(function (panel) {
            panel.classList.toggle('hidden', panel.dataset.sigPanel !== target);
        });
    }

    function clearUploadPreview() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        if (fileInput) fileInput.value = '';
        if (uploadPreview) uploadPreview.removeAttribute('src');
        if (uploadPreviewWrap) uploadPreviewWrap.classList.add('hidden');
    }

    function setMode(nextMode) {
        mode = nextMode;
        dataInput.value = '';
        setActiveTab(mode);

        if (mode === 'draw') {
            fileInput.required = false;
            clearUploadPreview();
        } else {
            fileInput.required = true;
        }
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            setMode(tab.dataset.sigTab);
        });
    });

    fileInput?.addEventListener('change', function () {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        const file = this.files?.[0];

        if (!file) {
            uploadPreview?.removeAttribute('src');
            uploadPreviewWrap?.classList.add('hidden');

            return;
        }

        objectUrl = URL.createObjectURL(file);
        if (uploadPreview) uploadPreview.src = objectUrl;
        uploadPreviewWrap?.classList.remove('hidden');
        dataInput.value = '';
    });

    if (canvas) {
        context = canvas.getContext('2d');
        context.lineWidth = 4;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.strokeStyle = '#111827';

        const point = function (event) {
            const rect = canvas.getBoundingClientRect();

            return {
                x: (event.clientX - rect.left) * (canvas.width / rect.width),
                y: (event.clientY - rect.top) * (canvas.height / rect.height),
            };
        };

        const start = function (event) {
            event.preventDefault();
            drawing = true;
            hasInk = true;
            const position = point(event);
            context.beginPath();
            context.moveTo(position.x, position.y);

            try {
                canvas.setPointerCapture(event.pointerId);
            } catch (e) {}
        };

        const move = function (event) {
            if (!drawing) return;
            event.preventDefault();
            const position = point(event);
            context.lineTo(position.x, position.y);
            context.stroke();
        };

        const finish = function (event) {
            if (!drawing) return;
            drawing = false;
            context.closePath();

            try {
                canvas.releasePointerCapture(event.pointerId);
            } catch (e) {}

            updateDrawPreview();
        };

        canvas.addEventListener('pointerdown', start);
        canvas.addEventListener('pointermove', move);
        canvas.addEventListener('pointerup', finish);
        canvas.addEventListener('pointercancel', finish);
        canvas.addEventListener('pointerleave', finish);
    }

    function updateDrawPreview() {
        if (!canvas || !drawPreview) return;

        if (!hasInk) {
            drawPreview.removeAttribute('src');
            drawPreviewWrap?.classList.add('hidden');

            return;
        }

        drawPreview.src = canvas.toDataURL('image/png');
        drawPreviewWrap?.classList.remove('hidden');
        drawError?.classList.add('hidden');
    }

    clearButton?.addEventListener('click', function () {
        if (!canvas || !context) return;

        context.clearRect(0, 0, canvas.width, canvas.height);
        hasInk = false;
        dataInput.value = '';
        drawPreview?.removeAttribute('src');
        drawPreviewWrap?.classList.add('hidden');
        drawError?.classList.add('hidden');
    });

    form?.addEventListener('submit', function (event) {
        if (mode === 'draw') {
            if (!hasInk) {
                event.preventDefault();
                drawError?.classList.remove('hidden');

                return;
            }

            dataInput.value = canvas.toDataURL('image/png');
        } else {
            dataInput.value = '';
        }
    });

    setMode('upload');

    @if ($errors->has('tanda_tangan') || $errors->has('tanda_tangan_data'))
        document.addEventListener('DOMContentLoaded', function () {
            window.setModal?.(@json($id), true);
        });
    @endif
})();
</script>
