<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
            'ebook_id' => ['nullable', 'exists:ebooks,id'],
            'history' => ['nullable', 'array', 'max:5'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:500'],
        ], [
            'question.required' => 'Tulis pertanyaan dulu ya.',
            'question.min' => 'Pertanyaan terlalu pendek.',
            'question.max' => 'Pertanyaan maksimal 500 karakter.',
            'history.max' => 'Riwayat percakapan maksimal 5 pesan, mulai topik baru ya.',
        ]);

        $ebook = null;
        if (! empty($validated['ebook_id'])) {
            // Validasi exists bisa balapan dengan hapus admin di tengah jalan.
            $ebook = Ebook::with(['category', 'videos'])->find($validated['ebook_id']);
            if (! $ebook) {
                return response()->json(['message' => 'E-book tidak ditemukan.'], 404);
            }
        }

        $result = $chatbot->ask($validated['question'], $ebook, $validated['history'] ?? []);

        return response()->json([
            'answer' => $result['answer'],
            'provider' => $result['provider'],
            'fallback' => $result['fallback'],
        ]);
    }
}
