<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory,DefaultDatetimeFormat;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'parent_id',
        'sort'
    ];

    // 允许动态属性 children
    protected $appends = ['children'];

    // 存储子分类的临时属性
    protected $children = [];

    // 子分类访问器
    public function getChildrenAttribute()
    {
        return $this->children;
    }

    // 子分类设置器
    public function setChildrenAttribute($value)
    {
        $this->children = $value;
    }

    // 父分类关联
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    // 子分类关联
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // 递归加载所有层级的子分类
    public function childrenReursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    // 获取所有分类（用于下拉选择）
    public static function getAllCategories()
    {
        return self::orderBy('sort')->pluck('name', 'id');
    }


    // 新增：生成下拉下拉选择框提供分类选项
    public static function selectOptions()
    {
        // 获取所有分类并按按ID索引
        $categories = self::select('id', 'name', 'parent_id')->get()->keyBy('id');

        // 构建带层级的选项
        $options = [];
        foreach ($categories as $category) {
            $level = 0;
            $currentParentId = $category->parent_id;

            // 计算层级
            while ($currentParentId && isset($categories[$currentParentId])) {
                $level++;
                $currentParentId = $categories[$currentParentId]->parent_id;
            }

            // 添加层级前缀
            $prefix = $level > 0 ? '|' . str_repeat('——', $level) : '';
            $options[$category->id] = $prefix . $category->name;
        }

        return $options;
    }

    // 生成完整的图片地址，如果是网络图片就不需要
    public function getImageUrlAttribute()
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if(Str::startsWith($this->attributes['icon'], ['http://', 'https://'])) {
            return $this->attributes['icon'];
        }
        return \Storage::disk('public')->url($this->attributes['icon']);
    }
}
