<?php

namespace App\Http\Requests\Members;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->route('member');

        return $this->user()->can('update', $member);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentUser = $this->user();

        return [
            'church_id' => [
                'required_without:congregation_id',
                'nullable',
                'exists:churches,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($value && !$currentUser->canAccessChurch((int) $value)) {
                        $fail('Você não possui autorização para vincular o membro a esta congregação.');
                    }
                },
            ],
            'congregation_id' => [
                'required_without:church_id',
                'nullable',
                'exists:churches,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($value && !$currentUser->canAccessChurch((int) $value)) {
                        $fail('Você não possui autorização para vincular o membro a esta congregação.');
                    }
                },
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'social_name' => ['nullable', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'rg' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['M', 'F'])],
            'marital_status' => ['nullable', Rule::in(['solteiro', 'casado', 'viuvo', 'divorciado', 'uniao_estavel', 'outro'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'entry_date' => ['nullable', 'date'],
            'ecclesiastical_position' => ['nullable', 'string', Rule::in([
                'Membro', 'Cooperador', 'Diácono', 'Diaconisa', 'Missionário', 'Missionária', 'Presbítero', 'Evangelista', 'Pastor',
            ])],
            'ecclesiastical_record' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['ativo', 'inativo', 'transferido', 'disciplina', 'falecido'])],
            'functions' => ['nullable', 'array'],
            'functions.*' => ['exists:functions,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'church_id.required' => 'Selecione a congregação à qual o membro pertence.',
            'church_id.exists' => 'A congregação selecionada é inválida.',
            'full_name.required' => 'O nome completo do membro é obrigatório.',
            'birth_date.before' => 'A data de nascimento deve ser anterior à data atual.',
            'status.required' => 'Selecione a situação do membro.',
            'photo.image' => 'O arquivo enviado para a foto deve ser uma imagem válida.',
            'photo.mimes' => 'A foto deve estar no formato: JPEG, PNG, JPG ou WEBP.',
            'photo.max' => 'A foto não pode ultrapassar o tamanho de 2MB.',
        ];
    }

    /**
     * Validação adicional: verifica o conteúdo real do arquivo via finfo (não apenas a extensão).
     * Protege contra upload de arquivos maliciosos disfarçados como imagens.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('photo') && $this->file('photo')->isValid()) {
                $file = $this->file('photo');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $realMime = finfo_file($finfo, $file->getRealPath());
                    finfo_close($finfo);

                    if (!in_array($realMime, $allowedMimes)) {
                        $validator->errors()->add(
                            'photo',
                            'O conteúdo real do arquivo enviado não é uma imagem válida. Upload rejeitado por segurança.'
                        );
                    }
                }
            }
        });
    }
}

