<?php
$dir = __DIR__ . '/app/Models';
$models = glob($dir . '/*.php');
foreach ($models as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'LogsActivity') !== false) continue;
    
    if (strpos($content, "use Illuminate\Database\Eloquent\Model;") !== false) {
        $importString = "use Illuminate\Database\Eloquent\Model;\nuse Spatie\Activitylog\Traits\LogsActivity;\nuse Spatie\Activitylog\LogOptions;";
        $content = str_replace("use Illuminate\Database\Eloquent\Model;", $importString, $content);
    } elseif (strpos($content, "use Illuminate\Foundation\Auth\User as Authenticatable;") !== false) {
        $importStringUser = "use Illuminate\Foundation\Auth\User as Authenticatable;\nuse Spatie\Activitylog\Traits\LogsActivity;\nuse Spatie\Activitylog\LogOptions;";
        $content = str_replace("use Illuminate\Foundation\Auth\User as Authenticatable;", $importStringUser, $content);
    }
    
    $content = preg_replace('/(class [a-zA-Z0-9_]+ extends [a-zA-Z0-9_]+\s*\{)/', "$1\n    use LogsActivity;\n", $content);
    
    $method = <<<EOT

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
EOT;
    
    $content = preg_replace('/\}\s*$/', $method . "\n}\n", $content);
    
    file_put_contents($file, $content);
    echo "Updated " . basename($file) . "\n";
}
