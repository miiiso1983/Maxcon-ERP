<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Department extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'code',
        'parent_id',
        'manager_id',
        'budget',
        'location',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'description'];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootDepartments($query)
    {
        return $query->whereNull('parent_id');
    }

    // Methods
    public function getAllEmployees()
    {
        $employees = $this->employees;
        
        foreach ($this->children as $child) {
            $employees = $employees->merge($child->getAllEmployees());
        }

        return $employees;
    }

    public function getTotalBudget(): float
    {
        $total = $this->budget ?? 0;
        
        foreach ($this->children as $child) {
            $total += $child->getTotalBudget();
        }

        return $total;
    }

    public function getHierarchyPath(): string
    {
        $path = $this->name;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->name . ' > ' . $path;
            $parent = $parent->parent;
        }

        return $path;
    }
}
