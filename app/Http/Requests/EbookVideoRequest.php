<?php

namespace App\Http\Requests;

use App\Models\EbookVideo;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class EbookVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Tambah/edit: staf (admin & guru). Hapus/edit data orang lain
        // tetap dijaga di level route (admin-only) & controller.
        return $user instanceof User && $user->isStaff();
    }

    public function rules(): array
    {
        return [
            'ebook_id' => ['required', 'exists:ebooks,id'],
            'title' => ['required', 'string', 'max:255'],
            // max:255 disamakan dengan kolom DB (string default) agar tidak overflow.
            'youtube_url' => ['required', 'url', 'max:255', function ($attribute, $value, $fail) {
                if (! EbookVideo::extractYoutubeId($value)) {
                    $fail('URL YouTube tidak valid. Gunakan link watch, youtu.be, embed, atau shorts.');
                }
            }],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ebook_id.required' => 'E-book wajib dipilih.',
            'title.required' => 'Judul video wajib diisi.',
            'youtube_url.required' => 'URL YouTube wajib diisi.',
            'youtube_url.url' => 'Format URL tidak valid.',
        ];
    }
}
