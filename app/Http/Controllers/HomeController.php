<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTFactory;
use Auth;

class HomeController extends Controller
{
    //

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     description="Login into system",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(required={"username", "password"},
     *                  @OA\Property(property="username", description="Username login", example="admin", type="string", minLength=3),
     *                  @OA\Property(property="password", description="Password login", example="admin", type="string", minLength=5),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="code",example="200"),
     *                  @OA\Property(property="message",example="Login Success"),
     *                  @OA\Property(type="object",property="data",
     *                      @OA\Property(property="token",description="Token JWT",example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvL2xvZ2luIiwiaWF0IjoxNjU2NzYxMzU1LCJleHAiOjE2NTY3NjQ5NTUsIm5iZiI6MTY1Njc2MTM1NSwianRpIjoiSUF1TU1aT3g4ZDJ1clRkRSJ9.ehdl9Y4yYOO9nLn8LJsdHw8W9yisa05ZG3SxaJS5P3k",type="string"),
     *                  ),
     *              )
     *          )
     *     ),
     *      @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="code",example="400", type="number"),
     *                  @OA\Property(property="message",example="The given data was invalid.", type="string"),
     *                  @OA\Property(type="object",property="errors",
     *                      @OA\Property(property="username", type="array",
     *                          @OA\Items(type="string", example="The username field is required."),
     *                      ),
     *                      @OA\Property(property="password", type="array",
     *                          @OA\Items(type="string", example="The password is required."),
     *                      ),
     *                  )
     *              )
     *          )
     *     ),
     * )
     *
     *
     */

    public function login(Request $request)
    {
        try {

            $this->validate($request, [
                'username' => 'required|min:3',
                'password' => 'required|min:5'
            ]);

            $validUsername = 'admin';
            $validPassword = app('hash')->make('admin');

            $dataLogin = $request->all();
            $isValidPassword = Hash::check($dataLogin['password'], $validPassword);

            $token = $this->generateToken($dataLogin);

            // store session in token
            $request->session()->put('token', $token);

            $data = ['token' => $token];

            if ($dataLogin['username'] == $validUsername && $isValidPassword) {
                return ApiResponse::success('Login Success', $data);
            } else {
                return ApiResponse::badRequest('Username or password invalid');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::badRequest($e->getMessage(), $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::internalServerError($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     description="Logout from system",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="code",example="200"),
     *                  @OA\Property(property="message",example="Logout Success"),
     *              )
     *          )
     *     ),
     *      @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {token}",
     *         @OA\Schema(
     *              type="bearerAuth"
     *         )
     *      ),
     *      @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="code",example="400", type="number"),
     *                      @OA\Property(property="message",example="Token is Invalid", type="string"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="code",example="400", type="number"),
     *                      @OA\Property(property="message",example="Token is Expired", type="string"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="code",example="400", type="number"),
     *                      @OA\Property(property="message",example="Authorization Token not found", type="string"),
     *                  )
     *              }
     *          )
     *
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="code",example="401", type="number"),
     *                  @OA\Property(property="message",example="Unauthorized. Please login again", type="string"),
     *              )
     *          )
     *     ),
     * )
     *
     *
     */
    public function logout(Request $request)
    {
        $request->session()->put('token', null);
        return ApiResponse::success('Logout Success');
    }

    public function generateToken($dataLogin)
    {
        $customClaims = [
            'name' => $dataLogin['username'],
        ];
        $factory = JWTFactory::customClaims($customClaims);
        $payload = $factory->make();
        $token = JWTAuth::encode($payload);
        return (string)$token;
    }

    /**
     * @OA\Get(
     *     path="/checkLogin",
     *     tags={"Auth"},
     *     summary="Checklogin",
     *     description="Checklogin",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="code",example="200"),
     *                  @OA\Property(property="message",example="Authorization"),
     *              )
     *          )
     *     ),
     *      @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer {token}",
     *         @OA\Schema(
     *              type="bearerAuth"
     *         )
     *      ),
     *      @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(
     *                      @OA\Property(property="code",example="400", type="number"),
     *                      @OA\Property(property="message",example="Token is Invalid", type="string"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="code",example="400", type="number"),
     *                      @OA\Property(property="message",example="Token is Expired", type="string"),
     *                  ),
     *                  @OA\Schema(
     *                      @OA\Property(property="code",example="400", type="number"),
     *                      @OA\Property(property="message",example="Authorization Token not found", type="string"),
     *                  )
     *              }
     *          )
     *
     *     ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="code",example="401", type="number"),
     *                  @OA\Property(property="message",example="Unauthorized. Please login again", type="string"),
     *              )
     *          )
     *     ),
     * )
     *
     *
     */
    public function checkLogin()
    {
        return ApiResponse::success('Authorization');
    }
}
