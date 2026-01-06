<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Http\Resources\UserAddressesResource;
use App\Http\Requests\Api\UserAddressRequest;

class UserAddressesController extends Controller
{
    /**
     * 获取当前用户的所有地址列表
     */
    public function index(Request $request)
    {
        // 获取当前用户的地址列表
        $addresses = $request->user()->addresses()->orderBy('is_default','desc')->orderBy('created_at','desc')->get();
        return UserAddressesResource::collection($addresses);
    }

    public function store(UserAddressRequest $request)
    {
        $data = $request->validated();
        if($data['is_default']) {
            $data['is_default'] = 1;
        } else {
            $data['is_default'] = 0;
        }
        // 如果设为默认地址，先将该用户其他地址设为非默认
        if ($data['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }
        $adresses = $request->user()->addresses()->create($data);

        return response()->json([
            'code' => 201,
            'message' => '创建成功'
        ], 201);
    }

    public function update(UserAddressRequest $request, UserAddress $address)
    {
        $this->authorize('update', $address);

        $addressData = $request->validated();
        $addressData['is_default'] = $addressData['is_default'] ? 1 : 0;

        $address->update($addressData);

        // 如果设为默认地址，取消其他地址的默认状态
        if ($addressData['is_default'])
        {
            $address->user->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => 0]);
        }

        return response()->json([
            'code' => 200,
            'message' => '修改成功'
        ], 200);
    }

    public function destroy(UserAddress $address)
    {
        $this->authorize('delete', $address);
        $address->delete();
        return response()->json([
            'code' => 204,
            'message' => '修改成功'
        ], 204);
    }

    public function isDefault(Request $request, $id)
    {

        // 从 URL 参数获取 ID 并验证
        $address = auth()->user()->addresses()->findOrFail($id);

        // 确保用户有权限修改该地址
        $this->authorize('update', $address);

        // 使用事务确保数据一致性
        \DB::transaction(function () use ($address) {
            // 设置默认地址
            auth()->user()->addresses()->update(['is_default' => 0]);
            $address->update(['is_default' => 1]);

        });


        return response()->json([
            'code' => 201,
            'message' => '设置成功'
        ], 201);
    }
}
