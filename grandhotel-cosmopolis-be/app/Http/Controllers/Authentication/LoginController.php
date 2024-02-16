<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'login',
        description: 'Login',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'email', type: 'string'),
                        new OA\Property(property: 'password', type: 'string')]
                )
            )
        ),
        tags: ['Login'],
        responses: [
            new OA\Response(response: 200, description: 'logged in'),
            new OA\Response(response: 401, description: 'unauthenticated')
        ]
    )]
    public function authenticate(Request $request): Response {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials,true)) {
            $request->session()->regenerate();
            return response('', 200);
        }

        return response('unauthenticated', 401);
    }

    #[OA\Post(
        path: '/api/login/rememberMe',
        operationId: 'rememberMe',
        description: 'tries to Login via remember token',
        tags: ['Login'],
        responses: [
            new OA\Response(response: 200, description: 'logged in'),
            new OA\Response(response: 401, description: 'unauthenticated')
        ]
    )]
    public function rememberMe(Request $request): Response {
        if (Auth::viaRemember()) {
            $request->session()->regenerate();
            return response();
        }
        return response('unauthenticated', 401);
    }
}
