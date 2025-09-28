<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'thing_to_remember' => ['sometimes', 'required', 'string'],
            'document_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'project_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
        ];
    }

    /**
     * Ensure at least one field is provided.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fields = ['title', 'thing_to_remember', 'document_type', 'project_name', 'tags'];

            $hasChanges = collect($fields)->contains(fn ($field) => $this->has($field));

            if (! $hasChanges) {
                $validator->errors()->add('data', 'At least one field must be provided for update.');
            }
        });
    }
}
