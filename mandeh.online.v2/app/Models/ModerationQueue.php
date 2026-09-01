<?php
+
+namespace App\Models;
+
+use Illuminate\Database\Eloquent\Model;
+use Illuminate\Database\Eloquent\Relations\BelongsTo;
+
+class ModerationQueue extends Model
+{
+    protected $table = 'moderation_queue';
+
+    protected $fillable = [
+        'interaction_id',
+        'assigned_to',
+        'decision',
+        'reason',
+        'reviewed_at',
+    ];
+
+    protected $casts = [
+        'reviewed_at' => 'datetime',
+    ];
+
+    public function interaction(): BelongsTo
+    {
+        return $this->belongsTo(Interaction::class);
+    }
+
+    public function moderator(): BelongsTo
+    {
+        return $this->belongsTo(User::class, 'assigned_to');
+    }
+}
+
