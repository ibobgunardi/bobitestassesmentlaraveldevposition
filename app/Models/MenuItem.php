<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'route_name',
        'url',
        'icon',
        'description',
        'order',
        'level',
        'is_active',
        'is_visible',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * Get the parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('order');
    }

    /**
     * Get all descendants (recursive children).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the permissions for the menu item.
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'menu_item_permission',
            'menu_item_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Scope a query to only include root menu items.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include active menu items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include visible menu items.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}