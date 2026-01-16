<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportUsage extends Model
{
    protected $table = 'export_usage';

    protected $fillable = [
        'user_id',
        'year',
        'export_count',
        'last_export_at',
    ];

    protected $casts = [
        'last_export_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getRemainingExports(int $userId): int
    {
        $currentYear = now()->year;
        $usage = self::where('user_id', $userId)
            ->where('year', $currentYear)
            ->first();

        $used = $usage ? $usage->export_count : 0;
        return max(0, 5 - $used);
    }

    public static function incrementUsage(int $userId): bool
    {
        $currentYear = now()->year;

        $usage = self::firstOrCreate(
            ['user_id' => $userId, 'year' => $currentYear],
            ['export_count' => 0]
        );

        if ($usage->export_count >= 5) {
            return false;
        }

        $usage->increment('export_count');
        $usage->update(['last_export_at' => now()]);

        return true;
    }

    public static function canExport(int $userId): bool
    {
        return self::getRemainingExports($userId) > 0;
    }
}
