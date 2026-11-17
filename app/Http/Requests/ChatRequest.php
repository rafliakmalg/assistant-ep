<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'conversation_id' => __('index.chat.conversation_id'),
            'decisions' => __('index.chat.decisions'),
            'message' => __('index.chat.message'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'conversation_id' => ['nullable', 'uuid', 'required_with:decisions'],
            'decisions' => ['array', 'min:1', 'required_without:message'],
            'decisions.*' => ['boolean'],
            'message' => ['string', 'max:2000', 'required_without:decisions'],
        ];
    }
}
