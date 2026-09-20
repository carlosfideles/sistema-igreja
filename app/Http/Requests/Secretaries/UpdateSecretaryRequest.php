<?php

namespace App\Http\Requests\Secretaries;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecretaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('secretary');

        return $this->user()->can('update', $targetUser);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentUser = $this->user();
        $targetUser = $this->route('secretary');
        $targetId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        return [
            'member_id' => ['nullable', 'exists:members,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($targetId)],
            'password' => ['nullable', 'string', 'min:8'],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('users', 'cpf')->ignore($targetId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => [
                'required',
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($currentUser, $targetUser) {
                    if ($targetUser instanceof User && $targetUser->isFirstSecretaryRegional()) {
                        // Não permite trocar o papel do 1º Secretário Regional
                        $targetRole = Role::find($value);
                        if (!$targetRole || ($targetRole->slug !== 'primeiro_secretario' && $targetRole->level !== 1)) {
                            $fail('O cargo do 1º Secretário Regional não pode ser alterado.');
                        }
                    }

                    if (!$currentUser->isFirstSecretaryRegional()) {
                        $targetRole = Role::find($value);
                        $currentRoleLevel = $currentUser->role?->level ?? 99;

                        if ($targetRole && $targetRole->level < $currentRoleLevel) {
                            $fail('Você não possui permissão hierárquica para conceder este cargo.');
                        }
                    }
                },
            ],
            'scope' => [
                'required',
                Rule::in(['REGIONAL', 'LOCAL']),
                function ($attribute, $value, $fail) use ($currentUser, $targetUser) {
                    if ($targetUser instanceof User && $targetUser->isFirstSecretaryRegional() && $value !== 'REGIONAL') {
                        $fail('O 1º Secretário Regional deve obrigatoriamente manter a abrangência REGIONAL.');
                    }

                    if ($currentUser->isLocal() && $value === 'REGIONAL') {
                        $fail('Usuários com abrangência Local não podem definir abrangência Regional.');
                    }
                },
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive', 'blocked']),
                function ($attribute, $value, $fail) use ($targetUser) {
                    if ($targetUser instanceof User && $targetUser->isFirstSecretaryRegional() && $value !== 'active') {
                        $fail('O 1º Secretário Regional não pode ser desativado ou bloqueado.');
                    }
                },
            ],
            'churches' => [
                Rule::requiredIf(fn () => $this->input('scope') === 'LOCAL'),
                'array',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($this->input('scope') === 'LOCAL') {
                        if (empty($value)) {
                            $fail('Para secretários com abrangência Local, selecione ao menos uma congregação.');
                            return;
                        }

                        if ($currentUser->isLocal()) {
                            $allowedChurchIds = $currentUser->churches()->pluck('churches.id')->toArray();
                            foreach ($value as $churchId) {
                                if (!in_array($churchId, $allowedChurchIds)) {
                                    $fail('Você não pode autorizar congregações às quais não possui acesso.');
                                    return;
                                }
                            }
                        }
                    }
                },
            ],
            'churches.*' => ['exists:churches,id'],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do secretário é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso por outro usuário.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            'role_id.required' => 'Selecione a classificação / papel do secretário.',
            'scope.required' => 'Selecione o tipo de abrangência (Regional ou Local).',
            'status.required' => 'Selecione o status da conta.',
        ];
    }
}
