<?php

namespace App\Http\Dtos\User;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ListUserDto
{
    #[OA\Property(items: new OA\Items(ref: UserDto::class))]
    /** @var $users UserDto[] */
    public array $users;

    public function __construct(array $users) {
        $this->users = $users;
    }
}
