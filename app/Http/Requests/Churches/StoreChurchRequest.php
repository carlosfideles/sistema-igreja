<?php

namespace App\Http\Requests\Churches;

use App\Models\Church;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChurchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Church::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:churches,code'],
            'cnpj' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'responsible_name' => ['nullable', 'string', 'max:255'],
            'foundation_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da congregação é obrigatório.',
            'code.unique' => 'Este código identificador já está em uso por outra congregação.',
            'city.required' => 'A cidade/região administrativa é obrigatória.',
            'state.required' => 'A UF é obrigatória.',
            'state.size' => 'A UF deve conter exatamente 2 caracteres (ex: DF, GO).',
            'status.required' => 'Selecione a situação da congregação.',
            'logo.image' => 'O arquivo enviado para o logotipo deve ser uma imagem válida.',
            'logo.mimes' => 'O logotipo deve estar no formato: JPEG, PNG, JPG ou WEBP.',
            'logo.max' => 'O logotipo não pode ultrapassar o tamanho de 2MB.',
        ];
    }

    /**
     * Validação adicional: verifica o conteúdo real do arquivo via finfo (não apenas a extensão).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('logo') && $this->file('logo')->isValid()) {
                $file = $this->file('logo');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $realMime = finfo_file($finfo, $file->getRealPath());
                    finfo_close($finfo);

                    if (!in_array($realMime, $allowedMimes)) {
                        $validator->errors()->add(
                            'logo',
                            'O conteúdo real do arquivo enviado para o logotipo não é uma imagem válida. Upload rejeitado por segurança.'
                        );
                    }
                }
            }
        });
    }
}

