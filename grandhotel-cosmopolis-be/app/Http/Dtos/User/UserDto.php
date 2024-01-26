<?php

namespace App\Http\Dtos\User;
use App\Models\User;
use OpenApi\Attributes as OA;

#[OA\Schema]
class UserDto {
    #[OA\Property]
    public string $name;
    #[OA\Property]
    public string $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public static function create(User $user): UserDto {
        return new UserDto(
            $user->name,
            $user->email
        );
    }
}
