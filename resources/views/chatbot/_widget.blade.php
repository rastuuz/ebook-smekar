{{-- Widget chatbot inline (halaman detail): tipografis, tanpa ikon --}}
<div id="chatbot" class="card overflow-hidden" data-ebook-id="{{ isset($ebook) ? $ebook->id : '' }}">
    <div class="border-b border-stone-200/80 bg-gradient-to-b from-[#16294f] to-[#0e1b33] px-4 py-4 sm:px-5">
        <h2 class="font-display text-[1.05rem] font-semibold tracking-tight text-white">Asisten Belajar</h2>
        <p class="mt-0.5 truncate text-xs text-stone-300">
            @isset($ebook)
                Membahas: {{ $ebook->title }}
            @else
                Tanya materi apa saja
            @endisset
        </p>
    </div>

    <div id="chat-messages" class="max-h-72 space-y-2.5 overflow-y-auto bg-[#faf8f3] p-4 text-sm leading-relaxed">
        <div class="max-w-[85%] rounded-xl rounded-tl-sm border border-stone-200 bg-white px-3.5 py-2.5 text-stone-700 shadow-sm">
            Halo! Tulis pertanyaan soal materi di bawah.
        </div>
    </div>

    <div class="border-t border-stone-200/80 bg-white px-4 py-3 sm:px-5">
        <div class="mb-2.5 flex flex-wrap gap-1.5">
            <button type="button" data-chat-suggest="Ringkas materi e-book ini" class="chip !py-1">Ringkas materi</button>
            <button type="button" data-chat-suggest="Sebutkan istilah penting dari e-book ini" class="chip !py-1">Istilah penting</button>
            <button type="button" data-chat-suggest="Buatkan 3 pertanyaan latihan dari e-book ini" class="chip !py-1">Latihan soal</button>
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
    // Riwayat frontend-only: 5 pesan terakhir dikirim agar bot nyambung konteks.
    const history = [];
    // Teks riwayat dibatasi 500 karakter agar lolos validasi backend (history.*.text max).
    const clip = (s) => (s.length > 500 ? s.slice(0, 500) : s);
    const inputDefault = input.placeholder;
    let latihanMode = false;

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
            if (latihanMode || /coba dijawab/i.test(answer)) {
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
        const prompt = b.getAttribute('data-chat-suggest') || '';
        latihanMode = /latihan/i.test(prompt);
        if (!latihanMode) input.placeholder = inputDefault;
        sendQuestion(prompt);
    }));
})();
</script>
