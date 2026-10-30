<?php

namespace App\Http\Requests;

use App\Enums\ResourceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreStudyResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum(ResourceType::class)],
            // mimes checks the file's real content, not its name. max is in KB (10 MB).
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['required', 'string', 'max:50', 'alpha_dash'],
        ];
    }
}
