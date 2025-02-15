<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        dd("here");
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->route('id'),
            'phone' => 'nullable|string|max:20',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => strtolower($this->email), // Convert email to lowercase
        ]);
    }
}
