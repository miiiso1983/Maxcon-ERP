<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

class Report extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'report_type',
        'category',
        'query_config',
        'chart_config',
        'filters',
        'is_public',
        'is_scheduled',
        'schedule_config',
        'created_by',
        'last_run_at',
        'run_count',
        'meta_data',
    ];

    protected $casts = [
        'query_config' => 'array',
        'chart_config' => 'array',
        'filters' => 'array',
        'is_public' => 'boolean',
        'is_scheduled' => 'boolean',
        'schedule_config' => 'array',
        'last_run_at' => 'datetime',
        'meta_data' => 'array',
    ];

    public $translatable = ['name', 'description'];

    // Report Types
    const TYPE_SALES = 'sales';
    const TYPE_INVENTORY = 'inventory';
    const TYPE_FINANCIAL = 'financial';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_SUPPLIER = 'supplier';
    const TYPE_CUSTOM = 'custom';

    // Report Categories
    const CATEGORY_OPERATIONAL = 'operational';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_ANALYTICAL = 'analytical';
    const CATEGORY_COMPLIANCE = 'compliance';

    // Chart Types
    const CHART_LINE = 'line';
    const CHART_BAR = 'bar';
    const CHART_PIE = 'pie';
    const CHART_AREA = 'area';
    const CHART_TABLE = 'table';

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executions()
    {
        return $this->hasMany(ReportExecution::class);
    }

    public function schedules()
    {
        return $this->hasMany(ReportSchedule::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    // Accessors
    public function getTypeColorAttribute(): string
    {
        return match($this->report_type) {
            self::TYPE_SALES => 'success',
            self::TYPE_INVENTORY => 'info',
            self::TYPE_FINANCIAL => 'primary',
            self::TYPE_CUSTOMER => 'warning',
            self::TYPE_SUPPLIER => 'secondary',
            self::TYPE_CUSTOM => 'dark',
            default => 'light',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_OPERATIONAL => 'info',
            self::CATEGORY_FINANCIAL => 'success',
            self::CATEGORY_ANALYTICAL => 'warning',
            self::CATEGORY_COMPLIANCE => 'danger',
            default => 'secondary',
        };
    }

    public function getLastRunTextAttribute(): string
    {
        if (!$this->last_run_at) {
            return 'Never';
        }

        return $this->last_run_at->diffForHumans();
    }

    // Methods
    public function execute(array $parameters = []): ReportExecution
    {
        $execution = $this->executions()->create([
            'parameters' => $parameters,
            'status' => ReportExecution::STATUS_RUNNING,
            'started_at' => now(),
            'executed_by' => auth()->id(),
        ]);

        try {
            $data = $this->generateData($parameters);
            
            $execution->update([
                'status' => ReportExecution::STATUS_COMPLETED,
                'completed_at' => now(),
                'result_data' => $data,
                'row_count' => is_array($data) ? count($data) : 0,
            ]);

            // Update report statistics
            $this->increment('run_count');
            $this->update(['last_run_at' => now()]);

        } catch (\Exception $e) {
            $execution->update([
                'status' => ReportExecution::STATUS_FAILED,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
        }

        return $execution;
    }

    public function generateData(array $parameters = []): array
    {
        $queryConfig = $this->query_config;
        $baseQuery = $this->buildBaseQuery($queryConfig);
        
        // Apply filters
        $query = $this->applyFilters($baseQuery, $parameters);
        
        // Apply sorting
        if (isset($queryConfig['order_by'])) {
            $query->orderBy($queryConfig['order_by'], $queryConfig['order_direction'] ?? 'asc');
        }
        
        // Apply limits
        if (isset($queryConfig['limit'])) {
            $query->limit($queryConfig['limit']);
        }
        
        return $query->get()->toArray();
    }

    private function buildBaseQuery($config)
    {
        $modelClass = $config['model'] ?? null;
        
        if (!$modelClass || !class_exists($modelClass)) {
            throw new \Exception('Invalid model specified in query configuration');
        }
        
        $query = $modelClass::query();
        
        // Apply relationships
        if (isset($config['with'])) {
            $query->with($config['with']);
        }
        
        // Apply select fields
        if (isset($config['select'])) {
            $query->select($config['select']);
        }
        
        // Apply joins
        if (isset($config['joins'])) {
            foreach ($config['joins'] as $join) {
                $query->join($join['table'], $join['first'], $join['operator'], $join['second']);
            }
        }
        
        return $query;
    }

    private function applyFilters($query, array $parameters)
    {
        $filters = $this->filters ?? [];
        
        foreach ($filters as $filter) {
            $paramKey = $filter['parameter'] ?? $filter['field'];
            
            if (!isset($parameters[$paramKey])) {
                continue;
            }
            
            $value = $parameters[$paramKey];
            $field = $filter['field'];
            $operator = $filter['operator'] ?? '=';
            
            switch ($operator) {
                case 'like':
                    $query->where($field, 'like', "%{$value}%");
                    break;
                case 'between':
                    if (is_array($value) && count($value) === 2) {
                        $query->whereBetween($field, $value);
                    }
                    break;
                case 'in':
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    }
                    break;
                case 'date_range':
                    if (isset($parameters[$paramKey . '_start']) && isset($parameters[$paramKey . '_end'])) {
                        $query->whereBetween($field, [
                            $parameters[$paramKey . '_start'],
                            $parameters[$paramKey . '_end']
                        ]);
                    }
                    break;
                default:
                    $query->where($field, $operator, $value);
            }
        }
        
        return $query;
    }

    public function getChartData(array $data): array
    {
        $chartConfig = $this->chart_config;
        
        if (!$chartConfig || $this->chart_config['type'] === self::CHART_TABLE) {
            return $data;
        }
        
        $xField = $chartConfig['x_field'] ?? 'label';
        $yField = $chartConfig['y_field'] ?? 'value';
        
        $chartData = [
            'type' => $chartConfig['type'],
            'labels' => [],
            'datasets' => [
                [
                    'label' => $this->name,
                    'data' => [],
                    'backgroundColor' => $chartConfig['colors'] ?? ['#007bff'],
                ]
            ]
        ];
        
        foreach ($data as $item) {
            $chartData['labels'][] = $item[$xField] ?? '';
            $chartData['datasets'][0]['data'][] = $item[$yField] ?? 0;
        }
        
        return $chartData;
    }

    public function canBeEdited(): bool
    {
        return $this->created_by === auth()->id() || auth()->user()->hasRole('admin');
    }

    public function canBeDeleted(): bool
    {
        return $this->created_by === auth()->id() || auth()->user()->hasRole('admin');
    }

    public static function getDefaultReports(): array
    {
        return [
            [
                'name' => ['en' => 'Sales Summary', 'ar' => 'ملخص المبيعات', 'ku' => 'کورتەی فرۆشتن'],
                'description' => ['en' => 'Daily sales performance overview', 'ar' => 'نظرة عامة على أداء المبيعات اليومية', 'ku' => 'تێڕوانینی گشتی کارایی فرۆشتنی ڕۆژانە'],
                'report_type' => self::TYPE_SALES,
                'category' => self::CATEGORY_OPERATIONAL,
                'query_config' => [
                    'model' => 'App\\Modules\\Sales\\Models\\Sale',
                    'select' => ['sale_date', 'total_amount', 'payment_status'],
                    'with' => ['customer'],
                ],
                'chart_config' => [
                    'type' => self::CHART_LINE,
                    'x_field' => 'sale_date',
                    'y_field' => 'total_amount',
                ],
                'filters' => [
                    ['field' => 'sale_date', 'operator' => 'date_range', 'parameter' => 'date_range'],
                    ['field' => 'payment_status', 'operator' => '=', 'parameter' => 'payment_status'],
                ],
                'is_public' => true,
            ],
            [
                'name' => ['en' => 'Inventory Levels', 'ar' => 'مستويات المخزون', 'ku' => 'ئاستی کۆگا'],
                'description' => ['en' => 'Current stock levels by product', 'ar' => 'مستويات المخزون الحالية حسب المنتج', 'ku' => 'ئاستی کۆگای ئێستا بەپێی بەرهەم'],
                'report_type' => self::TYPE_INVENTORY,
                'category' => self::CATEGORY_OPERATIONAL,
                'query_config' => [
                    'model' => 'App\\Modules\\Inventory\\Models\\Product',
                    'select' => ['name', 'sku', 'current_stock', 'minimum_stock'],
                ],
                'chart_config' => [
                    'type' => self::CHART_BAR,
                    'x_field' => 'name',
                    'y_field' => 'current_stock',
                ],
                'filters' => [
                    ['field' => 'current_stock', 'operator' => '<=', 'parameter' => 'low_stock_threshold'],
                ],
                'is_public' => true,
            ],
        ];
    }
}
