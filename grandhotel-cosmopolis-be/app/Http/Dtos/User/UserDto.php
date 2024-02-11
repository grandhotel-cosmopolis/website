<?php

namespace App\Http\Dtos\User;
use App\Models\Permissions;
use App\Models\User;
use OpenApi\Attributes as OA;
use Spatie\Permission\Models\Permission;

#[OA\Schema]
class UserDto {
    #[OA\Property]
    public string $name;
    #[OA\Property]
    public string $email;
    #[OA\Property(items: new OA\Items(ref: Permissions::class))]
    /** @var Permissions[] $permission */
    public array $permissions;

    public function __construct(string $name, string $email, array $permissions)
    {
        $this->name = $name;
        $this->email = $email;
        $this->permissions = $permissions;
    }

    public static function create(User $user): UserDto {
        return new UserDto(
            $user->name,
            $user->email,
            $user->permissions()->get()->map(function (Permission $permission) {
                return Permissions::from($permission->name);
            })->toArray()
        );
    }
}
