<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateStudyResourceRequest extends FormRequest
{
    /**
     * Only the owner may edit. This runs before validation, so a
     * non-owner gets 403 rather than validation errors.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('resource'));
    }

    /**
     * Trim and lowercase tag names and drop duplicates, so "CS301 " and
     * "cs301" become the same tag.
     */
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('tags'))) {
            $this->merge([
                'tags' => collect($this->input('tags'))
                    ->map(fn ($tag) => is_string($tag) ? Str::lower(trim($tag)) : $tag)
                    ->unique()
                    ->values()
                    ->all(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * "sometimes" means a field is only validated (and updated) if it is sent.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'type' => ['sometimes', 'required', Rule::enum(ResourceType::class)],
            // The file can't be replaced here; delete and re-upload instead.
            'file' => ['prohibited'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['required', 'string', 'max:50', 'alpha_dash'],
        ];
    }
}
