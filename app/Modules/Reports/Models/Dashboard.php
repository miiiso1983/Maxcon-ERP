<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

class Dashboard extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'layout_config',
        'widgets',
        'is_public',
        'is_default',
        'created_by',
        'refresh_interval',
        'meta_data',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'widgets' => 'array',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'description'];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function widgets()
    {
        return $this->hasMany(DashboardWidget::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Methods
    public function addWidget(array $widgetConfig): DashboardWidget
    {
        return $this->widgets()->create($widgetConfig);
    }

    public function removeWidget(int $widgetId): bool
    {
        return $this->widgets()->where('id', $widgetId)->delete();
    }

    public function updateLayout(array $layout): bool
    {
        return $this->update(['layout_config' => $layout]);
    }

    public function canBeEdited(): bool
    {
        return $this->created_by === auth()->id() || auth()->user()->hasRole('admin');
    }

    public static function getDefaultDashboard(): array
    {
        return [
            'name' => ['en' => 'Executive Dashboard', 'ar' => 'لوحة القيادة التنفيذية', 'ku' => 'داشبۆردی جێبەجێکار'],
            'description' => ['en' => 'Key business metrics overview', 'ar' => 'نظرة عامة على مقاييس الأعمال الرئيسية', 'ku' => 'تێڕوانینی گشتی پێوەرە سەرەکییەکانی بازرگانی'],
            'layout_config' => [
                'columns' => 12,
                'rows' => 6,
                'gap' => 16,
            ],
            'widgets' => [
                [
                    'type' => 'metric',
                    'title' => ['en' => 'Total Sales', 'ar' => 'إجمالي المبيعات', 'ku' => 'کۆی فرۆشتن'],
                    'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 2],
                    'config' => [
                        'metric' => 'total_sales',
                        'format' => 'currency',
                        'color' => 'success',
                    ],
                ],
                [
                    'type' => 'chart',
                    'title' => ['en' => 'Sales Trend', 'ar' => 'اتجاه المبيعات', 'ku' => 'ڕەوتی فرۆشتن'],
                    'position' => ['x' => 3, 'y' => 0, 'w' => 6, 'h' => 4],
                    'config' => [
                        'chart_type' => 'line',
                        'data_source' => 'sales_trend',
                    ],
                ],
                [
                    'type' => 'metric',
                    'title' => ['en' => 'Active Customers', 'ar' => 'العملاء النشطون', 'ku' => 'کڕیارە چالاکەکان'],
                    'position' => ['x' => 9, 'y' => 0, 'w' => 3, 'h' => 2],
                    'config' => [
                        'metric' => 'active_customers',
                        'format' => 'number',
                        'color' => 'info',
                    ],
                ],
            ],
            'is_public' => true,
            'is_default' => true,
            'refresh_interval' => 300, // 5 minutes
        ];
    }
}
