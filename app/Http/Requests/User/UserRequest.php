<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $user = $this->user ??  null;
        $activeCompany = get_active_company();
        return [
            'user_name' => 'required|string|max:255',
            'user_email' => [
                'required',
                'email',
                $user 
                    ? Rule::unique('users', 'email')
                        ->ignore($user) // Ignore this user's own email when updating
                        ->where(function ($query) use ($activeCompany) {
                            $query->whereIn('id', function ($subQuery) use ($activeCompany) {
                                $subQuery->select('user_id')
                                    ->from('company_users')
                                    ->where('company_id', $activeCompany);
                            });
                        })
                    : Rule::unique('users', 'email')
                        ->where(function ($query) use ($activeCompany) {
                            $query->whereIn('id', function ($subQuery) use ($activeCompany) {
                                $subQuery->select('user_id')
                                    ->from('company_users')
                                    ->where('company_id', $activeCompany);
                            });
                        }),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'min:8',
                'confirmed'
            ],
            'role' => [
                'required',
                Rule::exists('company_roles', 'id')->where('company_id', $activeCompany),
            ],
        ];
    }
}
