<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvatarUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048', // 2MB
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => 'Pilih berkas gambar avatar terlebih dahulu.',
            'avatar.image' => 'Berkas yang diunggah harus berupa gambar yang valid.',
            'avatar.mimes' => 'Format gambar yang didukung hanya JPG, PNG, atau WebP.',
            'avatar.max' => 'Ukuran berkas gambar maksimal adalah 2 MB.',
        ];
    }
}
