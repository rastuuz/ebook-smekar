{{-- Chatbot mengambang global: selalu terlihat di kanan bawah setelah login --}}
<div id="chat-float" class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3" data-ebook-id="">
    {{-- Panel --}}
    <section id="chat-float-panel" class="chat-float-panel chat-float-closed hidden w-[min(92vw,380px)] overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-lux ring-1 ring-stone-900/5" aria-label="Tanya jawab materi">
        <header class="flex justify-end bg-gradient-to-b from-[#16294f] to-[#0e1b33] px-3 py-2.5">
            <button id="chat-float-close" type="button" class="rounded-lg border border-white/15 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-stone-300 transition hover:bg-white/10 hover:text-white">Tutup</button>
        </header>

        <div id="chat-float-messages" class="h-72 max-h-[40dvh] space-y-2.5 overflow-y-auto bg-[#faf8f3] p-4 text-sm leading-relaxed">
            <div data-chat-empty class="flex h-52 flex-col items-center justify-center text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.5 0-3-.4-4.2-1.1L3 20l1.1-4.3A8.5 8.5 0 1 1 21 11.5Z"/><path d="M9.5 9.7a2.5 2.5 0 0 1 4.9.8c0 1.7-2.4 2-2.4 3.5"/><circle cx="12" cy="16.8" r="0.7" fill="currentColor" stroke="none"/></svg>
                </span>
                <p class="mt-3 text-sm font-semibold text-stone-700">Ada materi yang mau ditanyakan?</p>
                <p class="mt-1 max-w-[250px] text-xs leading-relaxed text-stone-500">Tulis pertanyaan tentang isi e-book di kolom bawah, atau mulai dari salah satu tombol di bawah.</p>
            </div>
        </div>

        <div class="border-t border-stone-200 bg-white p-3">
            <div class="mb-2 flex flex-wrap gap-1.5">
                <button type="button" data-float-suggest="Ringkas materi e-book ini"
                    class="inline-flex items-center gap-1.5 rounded-full border border-stone-200/90 bg-[#faf8f3] px-2.5 py-1 text-[10px] font-semibold text-stone-600 transition duration-200 hover:-translate-y-px hover:border-stone-300 hover:bg-white hover:text-stone-900 hover:shadow-sm active:translate-y-0 active:border-transparent active:bg-[#0e1b33] active:text-white">
                    <svg class="h-3 w-3 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2V5Z"/><path d="M4 19a2 2 0 0 1 2-2h13"/></svg>
                    Ringkas materi
                </button>
                <button type="button" data-float-suggest="Sebutkan istilah penting dari e-book ini"
                    class="inline-flex items-center gap-1.5 rounded-full border border-stone-200/90 bg-[#faf8f3] px-2.5 py-1 text-[10px] font-semibold text-stone-600 transition duration-200 hover:-translate-y-px hover:border-stone-300 hover:bg-white hover:text-stone-900 hover:shadow-sm active:translate-y-0 active:border-transparent active:bg-[#0e1b33] active:text-white">
                    <svg class="h-3 w-3 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="m3 6 1 1 2-2M3 12l1 1 2-2M3 18l1 1 2-2"/></svg>
                    Istilah penting
                </button>
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
        aria-label="Buka tanya jawab materi" aria-expanded="false">
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
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    // Riwayat frontend-only: 5 pesan terakhir dikirim agar bot nyambung konteks.
    const history = [];
    // Teks riwayat dibatasi 500 karakter agar lolos validasi backend (history.*.text max).
    const clip = (s) => (s.length > 500 ? s.slice(0, 500) : s);

    // Konteks e-book otomatis dari halaman detail (window.__EBOOK__ diset di show.blade.php)
    const ebookId = (window.__EBOOK__ && window.__EBOOK__.id) ? String(window.__EBOOK__.id) : '';
    if (ebookId) {
        root.dataset.ebookId = ebookId;
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

    // Format ringan untuk jawaban: escape HTML dulu, lalu **tebal**, *miring*, dan baris baru.
    // Tidak dipakai untuk pesan siswa supaya apa yang diketik tampil apa adanya.
    function fmt(text) {
        return text
            .replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]))
            .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\*([^*\n]+)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }

    function bubble(text, mine) {
        messages.querySelector('[data-chat-empty]')?.remove();
        const div = document.createElement('div');
        if (mine) {
            div.className = 'ml-auto max-w-[85%] rounded-xl rounded-tr-sm bg-[#0e1b33] px-3.5 py-2.5 text-white shadow-sm';
            div.textContent = text;
        } else {
            div.className = 'max-w-[85%] rounded-xl rounded-tl-sm border border-stone-200 bg-white px-3.5 py-2.5 text-stone-700 shadow-sm';
            div.innerHTML = fmt(text);
        }
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    async function sendQuestion(q) {
        err.classList.add('hidden');
        if (q.length < 3) return;
        if (panel.classList.contains('hidden')) setOpen(true);
        bubble(q, true);
        history.push({ role: 'user', text: clip(q) });
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
            history.push({ role: 'assistant', text: clip(answer) });
            if (/coba dijawab/i.test(answer)) {
                input.placeholder = 'Tulis jawaban latihanmu di sini…';
            }
            if (history.length > 10) history.splice(0, history.length - 10);
        } catch (ex) {
            loading.remove();
            bubble('Jaringan bermasalah. Silakan coba lagi.', false);
        } finally {
            sendBtn.disabled = false;
        }
    }

    form.addEventListener('submit', (e) => { e.preventDefault(); sendQuestion(input.value.trim()); });
    root.querySelectorAll('[data-float-suggest]').forEach((b) => b.addEventListener('click', () => {
        sendQuestion(b.getAttribute('data-float-suggest') || '');
    }));
})();
</script>
