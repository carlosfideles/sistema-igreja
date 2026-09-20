<?php

namespace App\Http\Requests\Transfers;

use App\Models\Church;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->route('member') ?? Member::find($this->input('member_id'));

        return $member ? $this->user()->can('transfer', $member) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $member = $this->route('member') ?? Member::find($this->input('member_id'));
        $isTransfer = $this->input('type', 'transfer') === 'transfer';

        return [
            'member_id' => [$this->route('member') ? 'nullable' : 'required', 'exists:members,id'],
            'type' => ['nullable', 'in:transfer,inactivation'],
            'to_church_id' => [
                $isTransfer ? 'required' : 'nullable',
                $isTransfer ? 'exists:churches,id' : '',
                function ($attribute, $value, $fail) use ($member, $isTransfer) {
                    if (!$isTransfer || !$member) {
                        return;
                    }

                    if ((int) $value === (int) $member->church_id) {
                        $fail('A congregação de destino deve ser diferente da congregação atual do membro.');
                    }

                    $targetChurch = Church::find($value);
                    if (!$targetChurch || $targetChurch->status !== 'active') {
                        $fail('A congregação de destino precisa estar ativa.');
                    }
                },
            ],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Selecione o membro que deseja transferir ou inativar.',
            'member_id.exists' => 'O membro selecionado é inválido.',
            'type.required' => 'O tipo de movimentação é obrigatório.',
            'type.in' => 'Tipo de movimentação inválido.',
            'to_church_id.required' => 'Selecione a nova congregação de destino do membro.',
            'to_church_id.exists' => 'A congregação de destino selecionada é inválida.',
            'reason.required' => 'Informe o motivo da movimentação.',
            'reason.max' => 'O motivo não pode exceder 255 caracteres.',
        ];
    }
}
