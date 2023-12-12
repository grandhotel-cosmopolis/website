<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(path: '/api/user/list', operationId: 'listUsers', description: 'List Users', tags: ['User'])]
    #[OA\Response(response: 200, description: 'bla', content: new OA\JsonContent(ref: ListUserDto::class))]
    #[OA\Response(response: 401, description: 'unauthorized')]
    public function listUser(): JsonResponse {
        $users = User::all();
        $userDtos = $users->map(function (User $user) {
            return new UserDto($user->name, $user->email);
        });
        return new JsonResponse(new ListUserDto($userDtos->toArray()));
    }
}
