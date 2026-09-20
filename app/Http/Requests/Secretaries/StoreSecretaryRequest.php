<?php

namespace App\Http\Requests\Secretaries;

use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecretaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
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
            'member_id' => ['nullable', 'exists:members,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => [
                'required',
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if (!$currentUser->isFirstSecretaryRegional()) {
                        $targetRole = Role::find($value);
                        $currentRoleLevel = $currentUser->role?->level ?? 99;

                        // Um secretário não pode atribuir um cargo de nível hierárquico superior ao seu
                        if ($targetRole && $targetRole->level < $currentRoleLevel) {
                            $fail('Você não possui permissão hierárquica para conceder este cargo.');
                        }
                    }
                },
            ],
            'scope' => [
                'required',
                Rule::in(['REGIONAL', 'LOCAL']),
                function ($attribute, $value, $fail) use ($currentUser) {
                    // Secretário local nunca pode criar usuário regional
                    if ($currentUser->isLocal() && $value === 'REGIONAL') {
                        $fail('Usuários com abrangência Local não podem criar usuários com abrangência Regional.');
                    }
                },
            ],
            'status' => ['required', Rule::in(['active', 'inactive', 'blocked'])],
            'churches' => [
                Rule::requiredIf(fn () => $this->input('scope') === 'LOCAL'),
                'array',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($this->input('scope') === 'LOCAL') {
                        if (empty($value)) {
                            $fail('Para secretários com abrangência Local, é obrigatório selecionar ao menos uma congregação.');
                            return;
                        }

                        // Se o usuário logado for Local, só pode autorizar igrejas que ele próprio possui acesso
                        if ($currentUser->isLocal()) {
                            $allowedChurchIds = $currentUser->churches()->pluck('churches.id')->toArray();
                            foreach ($value as $churchId) {
                                if (!in_array($churchId, $allowedChurchIds)) {
                                    $fail('Você não pode autorizar congregações às quais você não possui acesso.');
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
            'password.required' => 'A senha de acesso é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            'role_id.required' => 'Selecione a classificação / papel do secretário.',
            'scope.required' => 'Selecione o tipo de abrangência (Regional ou Local).',
            'status.required' => 'Selecione o status da conta.',
        ];
    }
}
