{{-- Widget chatbot inline (halaman detail): polos tanpa branding, siswa langsung tulis pertanyaan --}}
<div id="chatbot" class="card overflow-hidden" data-ebook-id="{{ isset($ebook) ? $ebook->id : '' }}">
    <div id="chat-messages" class="max-h-72 space-y-2.5 overflow-y-auto bg-[#faf8f3] p-4 text-sm leading-relaxed">
        <div data-chat-empty class="flex h-40 flex-col items-center justify-center text-center sm:h-44">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-stone-200 bg-white shadow-sm">
                <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.5 0-3-.4-4.2-1.1L3 20l1.1-4.3A8.5 8.5 0 1 1 21 11.5Z"/><path d="M9.5 9.7a2.5 2.5 0 0 1 4.9.8c0 1.7-2.4 2-2.4 3.5"/><circle cx="12" cy="16.8" r="0.7" fill="currentColor" stroke="none"/></svg>
            </span>
            <p class="mt-3 text-sm font-semibold text-stone-700">Ada materi yang mau ditanyakan?</p>
            <p class="mt-1 max-w-[250px] text-xs leading-relaxed text-stone-500">Tulis pertanyaan tentang isi e-book di kolom bawah, atau mulai dari salah satu tombol di bawah.</p>
        </div>
    </div>

    <div class="border-t border-stone-200/80 bg-white px-4 py-3 sm:px-5">
        <div class="mb-2.5 flex flex-wrap gap-1.5">
            <button type="button" data-chat-suggest="Ringkas materi e-book ini"
                class="inline-flex items-center gap-1.5 rounded-full border border-stone-200/90 bg-[#faf8f3] px-3 py-1.5 text-[11px] font-semibold text-stone-600 transition duration-200 hover:-translate-y-px hover:border-stone-300 hover:bg-white hover:text-stone-900 hover:shadow-sm active:translate-y-0 active:border-transparent active:bg-[#0e1b33] active:text-white">
                <svg class="h-3 w-3 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2V5Z"/><path d="M4 19a2 2 0 0 1 2-2h13"/></svg>
                Ringkas materi
            </button>
            <button type="button" data-chat-suggest="Sebutkan istilah penting dari e-book ini"
                class="inline-flex items-center gap-1.5 rounded-full border border-stone-200/90 bg-[#faf8f3] px-3 py-1.5 text-[11px] font-semibold text-stone-600 transition duration-200 hover:-translate-y-px hover:border-stone-300 hover:bg-white hover:text-stone-900 hover:shadow-sm active:translate-y-0 active:border-transparent active:bg-[#0e1b33] active:text-white">
                <svg class="h-3 w-3 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="m3 6 1 1 2-2M3 12l1 1 2-2M3 18l1 1 2-2"/></svg>
                Istilah penting
            </button>
        </div>
        <form id="chat-form" class="flex gap-2">
            @csrf
            <input id="chat-input" type="text" maxlength="500" required autocomplete="off" aria-label="Tulis pertanyaan materi"
                placeholder="Tulis pertanyaan materi&hellip;"
                class="input min-w-0 flex-1 !mt-0">
            <button id="chat-send" type="submit" class="btn-primary shrink-0 !px-4">Kirim</button>
        </form>
        <p id="chat-error" class="mt-1.5 hidden text-xs text-red-600"></p>
    </div>
</div>

<script>
(function () {
    const box = document.getElementById('chatbot');
    if (!box || box.dataset.bound === '1') return;
    box.dataset.bound = '1';
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const messages = document.getElementById('chat-messages');
    const sendBtn = document.getElementById('chat-send');
    const err = document.getElementById('chat-error');
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const history = [];
    // Teks riwayat dibatasi 500 karakter agar lolos validasi backend (history.*.text max).
    const clip = (s) => (s.length > 500 ? s.slice(0, 500) : s);

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
                body: JSON.stringify({ question: q, ebook_id: box.dataset.ebookId || null, history: history.slice(-5) }),
            });
            const data = await res.json();
            loading.remove();
            if (!res.ok) {
                const msg = data.message || data.errors?.question?.[0] || 'Gagal menjawab. Coba lagi.';
                err.textContent = msg;
                err.classList.remove('hidden');
                bubble('Maaf, saya belum bisa menjawab. Silakan ulangi pertanyaan Anda.', false);
                return;
            }
            const answer = data.answer || 'Maaf, belum ada jawaban.';
            bubble(answer, false);
            history.push({ role: 'assistant', text: clip(answer) });
            // Kalau jawaban mengajak siswa mencoba menjawab, ubah petunjuk isian.
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

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sendQuestion(input.value.trim());
    });
    box.querySelectorAll('[data-chat-suggest]').forEach((b) => b.addEventListener('click', () => {
        sendQuestion(b.getAttribute('data-chat-suggest') || '');
    }));
})();
</script>
