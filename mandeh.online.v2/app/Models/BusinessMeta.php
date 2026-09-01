<?php
+
+namespace App\Models;
+
+use Illuminate\Database\Eloquent\Model;
+use Illuminate\Database\Eloquent\Relations\BelongsTo;
+
+class BusinessMeta extends Model
+{
+    protected $table = 'business_meta';
+
+    protected $fillable = [
+        'entity_id',
+        'menu_json',
+        'promotions_json',
+        'transaction_summary',
+    ];
+
+    protected $casts = [
+        'menu_json' => 'array',
+        'promotions_json' => 'array',
+        'transaction_summary' => 'array',
+    ];
+
+    public function entity(): BelongsTo
+    {
+        return $this->belongsTo(Entity::class);
+    }
+}
+
