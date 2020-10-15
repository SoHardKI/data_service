<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * добавление данных
     */
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['method']) || ($data['method'] != __FUNCTION__)) {
            return $this->createResponse(__FUNCTION__, ['error' => 'Undefined method']);
        }
        $rules = [
            'method' => [__FUNCTION__],
            'params' => ['array'],
            'first_name' => ['required', 'string'],
            'second_name' => ['required', 'string'],
            'email' => ['required', 'string', 'unique:users'],
        ];
        $validator = Validator::make($data['params'][0], $rules);

        if ($validator->passes()) {
            DB::transaction(function () use ($data) {
                $user = new User();
                $user->first_name = $data['params'][0]['first_name'];
                $user->second_name = $data['params'][0]['second_name'];
                $user->email = $data['params'][0]['email'];
                $user->page_uid = Str::uuid()->toString();
                $user->save();
            });
        } else {
            $errors = [];
            for ($i = 0; $i < count($validator->errors()->all()); ++$i) {
                $errors["error-$i"] = $validator->errors()->all()[$i];
            }
            return $this->createResponse(__FUNCTION__, $errors);
        }

        return $this->createResponse(__FUNCTION__, ['result' => 'Success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * получить данные по page_uid
     */
    public function getData(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['method']) || ($data['method'] != __FUNCTION__)) {
            return $this->createResponse(__FUNCTION__, ['error' => 'Undefined method']);
        }

        $validator = Validator::make($data['params'][0], ['page_uid' => ['string']]);

        if ($validator->passes()) {
            if($data['params'][0]['page_uid']) {
                $user = User::where(['page_uid' => $data['params'][0]['page_uid']])->first();

                return $this->createResponse(__FUNCTION__, [$user->getAttributes()]);
            } else {
                $users = DB::table('users')->select('first_name', 'second_name', 'email', 'page_uid', 'created_at')->get()->toArray();

                return $this->createResponse(__FUNCTION__, $users);
            }
        } else {
            $errors = [];
            for ($i = 0; $i < count($validator->errors()->all()); ++$i) {
                $errors["error-$i"] = $validator->errors()->all()[$i];
            }

            return $this->createResponse(__FUNCTION__, $errors);
        }
    }

    /**
     * @param string $method
     * @param array $params
     * @return JsonResponse
     */
    public function createResponse(string $method, array $params): JsonResponse
    {
        $response = new JsonResponse();
        $response->setJson(json_encode(['method' => $method, 'params' => $params]));

        return $response;
    }
}
