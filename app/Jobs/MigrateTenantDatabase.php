<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class MigrateTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        $migrationPath = database_path('migrations/tenant');
        $tenantKey     = $this->tenant->getTenantKey();

        logger()->info("MigrateTenantDatabase::handle() starting for tenant {$tenantKey}, path: {$migrationPath}");

        $this->tenant->run(function () use ($migrationPath, $tenantKey) {
            $migrator = app('migrator');

            logger()->info("Inside tenant run for {$tenantKey}, connection: " . $migrator->resolveConnection(null)->getName());
            logger()->info("Migrator registered paths: " . implode(', ', $migrator->paths()));

            if (! $migrator->repositoryExists()) {
                $migrator->getRepository()->createRepository();
            }

            $files = $migrator->getMigrationFiles([$migrationPath]);
            logger()->info("Migration files count from path: " . count($files));

            $migrations = $migrator->run([$migrationPath], ['pretend' => false, 'step' => false]);

            logger()->info("Migrations ran for tenant {$tenantKey}: " . count($migrations));
        });
    }
}
