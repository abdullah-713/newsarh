<?php

/**
 * Security Patch Script
 * Updates all Filament Resources to use SecureResource base class
 */

$resources = [
    'AttendanceResource' => 'attendance',
    'BranchResource' => 'branches',
    'BadgeResource' => 'gamification',
    'RewardResource' => 'rewards',
    'ChallengeResource' => 'gamification',
    'GeofenceResource' => 'attendance',
    'AnnouncementResource' => 'settings',
    'WorkShiftResource' => 'shifts',
    'ShiftTemplateResource' => 'shifts',
    'UserShiftAssignmentResource' => 'shifts',
    'OfficialHolidayResource' => 'settings',
    'TrapConfigurationResource' => 'traps',
    'TrapLogResource' => 'traps',
    'IntegrityReportResource' => 'integrity',
];

foreach ($resources as $resourceName => $permission) {
    $filePath = __DIR__ . "/../../app/Filament/Resources/{$resourceName}.php";
    
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: {$resourceName}\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Replace base class
    $content = str_replace(
        'use Filament\Resources\Resource;',
        'use App\Filament\Resources\SecureResource;',
        $content
    );
    
    $content = str_replace(
        'class ' . $resourceName . ' extends Resource',
        'class ' . $resourceName . ' extends SecureResource',
        $content
    );
    
    // Add permission prefix after model declaration
    $modelPattern = '/protected static \?string \$model = [^;]+;/';
    if (preg_match($modelPattern, $content, $matches)) {
        $replacement = $matches[0] . "\n    \n    protected static ?string \$permissionPrefix = '{$permission}';";
        $content = preg_replace($modelPattern, $replacement, $content, 1);
    }
    
    // Add Model import if not exists
    if (!str_contains($content, 'use Illuminate\Database\Eloquent\Model;')) {
        $content = str_replace(
            'use Illuminate\Database\Eloquent\Builder;',
            "use Illuminate\Database\Eloquent\Builder;\nuse Illuminate\Database\Eloquent\Model;",
            $content
        );
    }
    
    file_put_contents($filePath, $content);
    echo "✅ Updated: {$resourceName} (permission: {$permission})\n";
}

echo "\n🎉 All resources secured!\n";
