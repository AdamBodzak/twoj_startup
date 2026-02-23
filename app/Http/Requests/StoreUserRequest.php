<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreUserRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => ['required', 'string', 'max:30'],

            'emails' => ['required', 'array', 'min:1'],

            'emails.*.email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
            ],

            'emails.*.is_primary' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $emails = collect($this->input('emails', []));

            // duplikatów w payloadzie
            $uniqueEmails = $emails
                ->pluck('email')
                ->map(fn($e) => strtolower(trim($e)))
                ->unique();

            if ($uniqueEmails->count() !== $emails->count()) {
                $validator->errors()->add(
                    'emails',
                    'Email addresses must be unique within the request.'
                );
            }

            // Tylko jeden może być primary
            $primaryCount = $emails
                ->where('is_primary', true)
                ->count();

            if ($primaryCount > 1) {
                $validator->errors()->add(
                    'emails',
                    'Only one email can be marked as primary.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('emails')) {
            $this->merge([
                'emails' => collect($this->emails)->map(function ($email) {
                    $email['email'] = strtolower(trim($email['email']));
                    return $email;
                })->toArray()
            ]);
        }
    }
}
