{{-- Chatbot mengambang global: selalu terlihat di kanan bawah setelah login --}}
<div id="chat-float" class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3" data-ebook-id="">
    {{-- Panel --}}
    <section id="chat-float-panel" class="chat-float-panel chat-float-closed hidden w-[min(92vw,380px)] overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-lux ring-1 ring-stone-900/5" aria-label="Asisten Belajar">
        <header class="bg-gradient-to-b from-[#16294f] to-[#0e1b33] px-4 py-4">
            <div class="flex items-baseline justify-between gap-3">
                <p class="font-display text-[1rem] font-semibold leading-tight tracking-tight text-white">Asisten Belajar</p>
                <button id="chat-float-close" type="button" class="shrink-0 rounded-lg border border-white/15 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-stone-300 transition hover:bg-white/10 hover:text-white">Tutup</button>
            </div>
            <p id="chat-float-context" class="mt-0.5 truncate text-[11px] text-stone-300">Tanya materi apa saja</p>
        </header>

        <div id="chat-float-messages" class="h-72 max-h-[40dvh] space-y-2.5 overflow-y-auto bg-[#faf8f3] p-4 text-sm leading-relaxed">
            <div class="max-w-[85%] rounded-xl rounded-tl-sm border border-stone-200 bg-white px-3.5 py-2.5 text-stone-700 shadow-sm">
                Halo. Ada yang bisa saya bantu tentang materi e-book?
            </div>
        </div>

        <div class="border-t border-stone-200 bg-white p-3">
            <div class="mb-2 flex flex-wrap gap-1.5">
                <button type="button" data-float-suggest="Ringkas materi e-book ini" class="chip !px-2.5 !py-1 !text-[11px]">Ringkas materi</button>
                <button type="button" data-float-suggest="Jelaskan dengan bahasa sederhana" class="chip !px-2.5 !py-1 !text-[11px]">Jelaskan sederhana</button>
            </div>
            <form id="chat-float-form" class="flex gap-2">
                <input id="chat-float-input" type="text" maxlength="500" required autocomplete="off" aria-label="Tulis pertanyaan"
                    placeholder="Tulis pertanyaan&hellip;"
                    class="input min-w-0 flex-1 !mt-0 !py-2">
                <button id="chat-float-send" type="submit" class="btn-primary shrink-0 !px-3.5">Kirim</button>
            </form>
            <p id="chat-float-error" class="mt-1 hidden text-[11px] text-red-600"></p>
        </div>
    </section>

    {{-- Tombol buka --}}
    <button id="chat-float-toggle" type="button"
        class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-b from-[#16294f] to-[#0a1430] shadow-lux ring-1 ring-[#c9a227]/40 transition duration-200 hover:scale-[1.04] hover:ring-[#c9a227]/70 focus:outline-none focus:ring-2 focus:ring-[#c9a227]/60"
        aria-label="Buka asisten belajar" aria-expanded="false">
        <span id="chat-float-icon-open" aria-hidden="true">
            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true"><path fill="#d9c27a" d="M4 2h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H9l-5 4V3a1 1 0 0 1 0-1Z"/><circle cx="9" cy="9" r="1.3" fill="#0e1b33"/><circle cx="12.5" cy="9" r="1.3" fill="#0e1b33"/><circle cx="16" cy="9" r="1.3" fill="#0e1b33"/></svg>
        </span>
        <span id="chat-float-icon-close" class="hidden text-[10px] font-sans font-bold uppercase tracking-[0.14em] text-[#d9c27a]" aria-hidden="true">Tutup</span>
    </button>
</div>

<style>
.chat-float-panel {
    transition: opacity 0.18s ease, transform 0.18s ease;
    transform-origin: bottom right;
}
.chat-float-panel.chat-float-closed {
    opacity: 0;
    transform: translateY(12px) scale(0.98);
    pointer-events: none;
}
@media (prefers-reduced-motion: reduce) {
    .chat-float-panel { transition: none; }
}
</style>
<script>
(function () {
    const root = document.getElementById('chat-float');
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';
    const panel = document.getElementById('chat-float-panel');
    const toggle = document.getElementById('chat-float-toggle');
    const iconOpen = document.getElementById('chat-float-icon-open');
    const iconClose = document.getElementById('chat-float-icon-close');
    const closeBtn = document.getElementById('chat-float-close');
    const form = document.getElementById('chat-float-form');
    const input = document.getElementById('chat-float-input');
    const messages = document.getElementById('chat-float-messages');
    const sendBtn = document.getElementById('chat-float-send');
    const err = document.getElementById('chat-float-error');
    const ctxLabel = document.getElementById('chat-float-context');
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    // Riwayat frontend-only: 5 pesan terakhir dikirim agar bot nyambung konteks.
    const history = [];

    // Konteks e-book otomatis dari halaman detail (window.__EBOOK__ diset di show.blade.php)
    const ebookId = (window.__EBOOK__ && window.__EBOOK__.id) ? String(window.__EBOOK__.id) : '';
    const ebookTitle = (window.__EBOOK__ && window.__EBOOK__.title) ? String(window.__EBOOK__.title) : '';
    if (ebookId) {
        root.dataset.ebookId = ebookId;
        if (ctxLabel && ebookTitle) ctxLabel.textContent = 'Membahas: ' + ebookTitle;
    }

    function setOpen(open) {
        if (open) {
            panel.classList.remove('hidden');
            // Bingkai berikutnya: mulai animasi masuk.
            requestAnimationFrame(() => requestAnimationFrame(() => panel.classList.remove('chat-float-closed')));
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            setTimeout(() => input.focus(), 120);
        } else {
            panel.classList.add('chat-float-closed');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            // Sembunyikan setelah animasi keluar selesai.
            setTimeout(() => { if (panel.classList.contains('chat-float-closed')) panel.classList.add('hidden'); }, 180);
        }
    }

    toggle.addEventListener('click', () => setOpen(panel.classList.contains('hidden') || panel.classList.contains('chat-float-closed')));
    closeBtn.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });

    function bubble(text, mine) {
        const div = document.createElement('div');
        div.className = mine
            ? 'ml-auto max-w-[85%] rounded-xl rounded-tr-sm bg-[#0e1b33] px-3.5 py-2.5 text-white shadow-sm'
            : 'max-w-[85%] rounded-xl rounded-tl-sm border border-stone-200 bg-white px-3.5 py-2.5 text-stone-700 shadow-sm whitespace-pre-line';
        div.textContent = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    async function sendQuestion(q) {
        err.classList.add('hidden');
        if (q.length < 3) return;
        if (panel.classList.contains('hidden')) setOpen(true);
        bubble(q, true);
        history.push({ role: 'user', text: q });
        if (history.length > 10) history.splice(0, history.length - 10);
        input.value = '';
        sendBtn.disabled = true;
        const loading = bubble('Menyusun jawaban…', false);
        try {
            const res = await fetch("{{ route('chatbot.ask') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ question: q, ebook_id: root.dataset.ebookId || null, history: history.slice(-5) }),
            });
            const data = await res.json();
            loading.remove();
            if (!res.ok) {
                err.textContent = data.message || data.errors?.question?.[0] || 'Gagal menjawab. Coba lagi.';
                err.classList.remove('hidden');
                bubble('Maaf, saya belum bisa menjawab. Silakan ulangi pertanyaan Anda.', false);
                return;
            }
            const answer = data.answer || 'Maaf, belum ada jawaban.';
            bubble(answer, false);
            history.push({ role: 'assistant', text: answer });
            if (history.length > 10) history.splice(0, history.length - 10);
        } catch (ex) {
            loading.remove();
            bubble('Jaringan bermasalah. Silakan coba lagi.', false);
        } finally {
            sendBtn.disabled = false;
        }
    }

    form.addEventListener('submit', (e) => { e.preventDefault(); sendQuestion(input.value.trim()); });
    root.querySelectorAll('[data-float-suggest]').forEach((b) => b.addEventListener('click', () => sendQuestion(b.getAttribute('data-float-suggest') || '')));
})();
</script>
