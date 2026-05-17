<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('store_settings')->insertOrIgnore([
            [
                'key'         => 'mail_host',
                'value'       => '',
                'label'       => 'SMTP Host',
                'type'        => 'text',
                'description' => 'Outgoing mail server (e.g., smtp.gmail.com)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'mail_port',
                'value'       => '587',
                'label'       => 'SMTP Port',
                'type'        => 'number',
                'description' => 'Port for SMTP (usually 587 for TLS)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'mail_username',
                'value'       => '',
                'label'       => 'SMTP Username',
                'type'        => 'text',
                'description' => 'Email account username',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'mail_password',
                'value'       => '',
                'label'       => 'SMTP Password',
                'type'        => 'text',
                'description' => 'Email account password or app password',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'mail_from_address',
                'value'       => 'noreply@queenbuilders.com',
                'label'       => 'From Email Address',
                'type'        => 'text',
                'description' => 'Sender email for system notifications',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'mail_from_name',
                'value'       => 'QueenBuilders IMS',
                'label'       => 'From Name',
                'type'        => 'text',
                'description' => 'Sender name for system notifications',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('store_settings')->whereIn('key', [
            'mail_host', 'mail_port', 'mail_username', 'mail_password',
            'mail_from_address', 'mail_from_name',
        ])->delete();
    }
};
