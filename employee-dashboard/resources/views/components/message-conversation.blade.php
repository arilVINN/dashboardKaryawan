@props([
    'topic' => '',
    'typeLabel' => 'Pesan',
    'meta' => '',
    'thread' => [],
    'canReply' => false,
    'replyEndpoint' => '',
    'messageId' => '',
    'listUrl' => '#',
    'token' => null,
    'csrf' => null,
    'currentUserId' => null,
    'currentName' => null,
    'currentFoto' => null,
])

<div class="mx-auto w-full max-w-[760px] px-6 pb-8 pt-6"
     data-message-conversation
     data-endpoint="{{ $replyEndpoint }}"
     data-message-id="{{ $messageId }}"
     data-can-reply="{{ $canReply ? '1' : '0' }}"
     data-token="{{ $token ?? '' }}"
     data-csrf="{{ $csrf ?? '' }}"
     data-current-user="{{ $currentUserId ?? '' }}"
     data-current-name="{{ $currentName ?? '' }}"
     data-current-foto="{{ $currentFoto ?? '' }}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-[13px] text-[#4B5563]" aria-label="Breadcrumb"
         data-breadcrumb-parent="Daftar Pesan">
        <a href="{{ $listUrl }}"
           class="inline-flex items-center gap-1.5 rounded text-[#146C94] outline-none transition hover:underline focus-visible:ring-2 focus-visible:ring-[#146C94]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Daftar Pesan
        </a>
        <span aria-hidden="true">›</span>
        <span class="font-medium text-[#172B36]" data-breadcrumb-current="{{ $topic }}">{{ $topic }}</span>
    </nav>

    {{-- Header --}}
    <header class="mt-6">
        <h1 class="text-[23px] font-semibold leading-snug text-[#172B36]">
            {{ $topic }}
            <span class="ml-1 inline-flex h-[22px] items-center rounded-md border border-[#E2E8F0] bg-[#F1F5F9] px-2 align-middle text-[11px] font-normal text-[#475569]">
                {{ $typeLabel }}
            </span>
        </h1>
        <p class="mt-2 text-[14px] text-[#4B5563]">{{ $meta }}</p>
    </header>

    {{-- Thread --}}
    <section class="mt-6 border-t border-[#E2E8EC]" data-thread aria-label="Percakapan">
        @if (count($thread) > 10)
            <div class="border-b border-[#E2E8EC] py-3">
                <button type="button" data-show-older
                        class="rounded text-[13px] font-medium text-[#146C94] outline-none transition hover:underline focus-visible:ring-2 focus-visible:ring-[#146C94]">
                    Tampilkan pesan sebelumnya
                </button>
            </div>
        @endif

        @foreach ($thread as $index => $item)
            <article class="flex gap-3 border-b border-[#E2E8EC] py-4{{ $index >= 10 ? ' hidden' : '' }}"
                     data-message-item
                     data-own="{{ ! empty($item['is_mine']) ? '1' : '0' }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#AFD3E2] text-[12px] font-semibold text-[#0B4A66]">
                    @if (! empty($item['foto_url']))
                        <img src="{{ $item['foto_url'] }}" alt="" class="h-8 w-8 rounded-full object-cover">
                    @else
                        {{ $item['initials'] ?? '?' }}
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[14px] font-semibold text-[#172B36]">{{ $item['nama'] }}</span>
                            @if (! empty($item['is_mine']))
                                <span class="inline-flex rounded-full bg-[#E3F1F7] px-2 py-0.5 text-[11px] text-[#0B4A66]">Anda</span>
                            @endif
                        </div>
                        <time class="text-[12px] text-[#4B5563]">{{ $item['time_label'] }}</time>
                    </div>
                    <p class="mt-1 whitespace-pre-wrap break-words text-[14px] leading-[1.6] text-[#172B36]">{{ $item['body'] }}</p>
                </div>
            </article>
        @endforeach

        <div data-appended-messages></div>
    </section>

    {{-- Reply --}}
    @if ($canReply)
        <div class="sticky bottom-0 bg-white pb-8 pt-4" data-reply-panel>
            <label for="messageReplyInput" class="sr-only">Tulis balasan</label>
            <textarea id="messageReplyInput" rows="3" maxlength="2000" data-reply-input
                      placeholder="Tulis balasan..."
                      class="block w-full resize-none overflow-hidden rounded-lg border border-[#D5DEE3] bg-white px-3 py-2 text-[14px] leading-[22px] text-[#172B36] outline-none placeholder:text-[#6B7680] focus:border-[#146C94] focus:ring-2 focus:ring-[#146C94]"></textarea>
            <p class="mt-1 hidden text-[12px] text-[#B91C1C]" data-reply-error role="alert"></p>
            <div class="mt-2 flex items-center justify-between gap-3">
                <span class="text-[12px] text-[#4B5563]">Ctrl + Enter untuk mengirim</span>
                <button type="button" data-reply-submit disabled
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-[#146C94] px-4 text-[13px] font-medium text-white transition hover:bg-[#0F5678] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#146C94] disabled:cursor-not-allowed disabled:bg-[#A9C7D6]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    <span data-reply-label>Kirim balasan</span>
                </button>
            </div>
        </div>
    @else
        <p class="py-8 text-[14px] text-[#4B5563]">Percakapan ini tidak dapat dibalas.</p>
    @endif
</div>

@include('components.message-conversation-script')
