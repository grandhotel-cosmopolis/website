<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Dtos\User\ListUserDto;
use App\Http\Dtos\User\UserDto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /** @noinspection PhpUnused */
    #[OA\Get(
        path: '/api/user/list',
        operationId: 'listUsers',
        description: 'List Users',
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'list of users', content: new OA\JsonContent(ref: ListUserDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated')
        ]
    )]
    public function listUser(): JsonResponse {
        $users = User::all();
        $userDtos = $users->map(function (User $user) {
            return UserDto::create($user);
        });
        return new JsonResponse(new ListUserDto($userDtos->toArray()));
    }

    /** @noinspection PhpUnused */
    #[OA\Get(
        path: '/api/user',
        operationId: 'getUser',
        description: 'Get authenticated user',
        tags: ['User'],
        responses: [
            new OA\Response(response: 200, description: 'Authenticated User', content: new OA\JsonContent(ref: UserDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated')
        ])]
    public function getUser(): JsonResponse {
        /** @var User $user */
        $user = auth()->user();

        return new JsonResponse(UserDto::create($user));
    }
}
