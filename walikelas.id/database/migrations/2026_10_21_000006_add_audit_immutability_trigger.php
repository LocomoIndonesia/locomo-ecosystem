<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION prevent_audit_events_mutation()
            RETURNS TRIGGER AS $$
            BEGIN
                RAISE EXCEPTION 'audit_events is append-only: updates and deletes are prohibited.';
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trigger_prevent_audit_events_mutation ON audit_events;
            CREATE TRIGGER trigger_prevent_audit_events_mutation
            BEFORE UPDATE OR DELETE ON audit_events
            FOR EACH ROW
            EXECUTE FUNCTION prevent_audit_events_mutation();
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trigger_prevent_audit_events_mutation ON audit_events;
            DROP FUNCTION IF EXISTS prevent_audit_events_mutation();
        ");
    }
};
