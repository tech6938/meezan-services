<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Services\AdminPermissionDiscoveryService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof Role;
    }

    public function rules(): array
    {
        $moduleKeys = app(AdminPermissionDiscoveryService::class)->moduleKeys();

        return [
            'modules' => ['required', 'array'],
            'modules.*' => ['required', Rule::in(['view_only', 'view_edit'])],
            'modules' => function ($attribute, $value, $fail) use ($moduleKeys) {
                foreach (array_keys($value ?? []) as $moduleKey) {
                    if (! in_array($moduleKey, $moduleKeys, true)) {
                        $fail("The selected module [{$moduleKey}] is invalid.");
                    }
                }
            },
        ];
    }
}
