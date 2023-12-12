<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    #[OA\Post(path: '/api/login', operationId: 'login', description: 'Login', tags: ['Login'])]
    #[OA\RequestBody(content: new OA\MediaType(
        mediaType: 'multipart/form-data',
        schema: new OA\Schema(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string')]
        )
    ))]
    #[OA\Response(response: 200, description: 'logged in')]
    #[OA\Response(response: 401, description: 'unauthorized')]
    public function authenticate(Request $request): Response {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response('', 200);
        }

        return response('unauthorized', 401);
    }

}
