<?php

namespace App\Http\Controllers\User;
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
}
