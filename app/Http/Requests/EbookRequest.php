<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class EbookRequest extends FormRequest
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
        // Guru dibatasi file 5 MB, admin tetap 30 MB.
        $fileMax = ($this->user() instanceof User && $this->user()->isGuru()) ? 5120 : 30720;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:'.$fileMax,
            ],
        ];
    }

    public function messages(): array
    {
        $fileMaxLabel = ($this->user() instanceof User && $this->user()->isGuru()) ? '5 MB' : '30 MB';

        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'title.required' => 'Judul wajib diisi.',
            'author.required' => 'Penulis wajib diisi.',
            'cover.image' => 'Cover harus berupa gambar.',
            'cover.mimes' => 'Cover harus jpg, jpeg, png, atau webp.',
            'cover.max' => 'Ukuran cover maksimal 2 MB.',
            'file.mimes' => 'File e-book harus PDF.',
            'file.max' => 'Ukuran PDF maksimal '.$fileMaxLabel.'.',
        ];
    }
}
