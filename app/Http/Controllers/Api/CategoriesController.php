<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;

class CategoriesController extends Controller
{
    public function index()
    {
        // 返回所有分类信息
        CategoryResource::wrap('data');
        return CategoryResource::collection(Category::all());
    }
}
