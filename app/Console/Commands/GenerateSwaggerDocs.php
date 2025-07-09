<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateSwaggerDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:auto-generate {--watch : Watch for file changes and regenerate automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger documentation automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Generating Swagger documentation...');

        // Generate initial documentation
        Artisan::call('l5-swagger:generate');
        $this->info('✅ Swagger documentation generated successfully!');
        $this->info('📖 Documentation available at: ' . url('/api/documentation'));

        if ($this->option('watch')) {
            $this->info('👀 Watching for file changes...');
            $this->watchForChanges();
        }
    }

    /**
     * Watch for file changes and regenerate documentation
     */
    private function watchForChanges()
    {
        $lastModified = $this->getLastModifiedTime();

        while (true) {
            sleep(5); // Check every 5 seconds

            $currentModified = $this->getLastModifiedTime();

            if ($currentModified > $lastModified) {
                $this->info('🔄 File changes detected, regenerating documentation...');
                Artisan::call('l5-swagger:generate');
                $this->info('✅ Documentation updated!');
                $lastModified = $currentModified;
            }
        }
    }

    /**
     * Get the last modified time of relevant files
     */
    private function getLastModifiedTime()
    {
        $paths = [
            app_path('Http/Controllers'),
            app_path('Models'),
            base_path('routes/api.php'),
        ];

        $lastModified = 0;

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path)
                );

                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        $lastModified = max($lastModified, $file->getMTime());
                    }
                }
            } elseif (file_exists($path)) {
                $lastModified = max($lastModified, filemtime($path));
            }
        }

        return $lastModified;
    }
}
