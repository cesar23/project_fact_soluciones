<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Exception;

class BackupRemote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bk:remote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database from remote server';

    protected $process;

    protected $host;
    protected $username;
    protected $password;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // Verificar si estamos dentro de un contenedor
            $isInContainer = file_exists('/.dockerenv');
            
            if ($isInContainer) {
                // Si estamos en un contenedor, usar el socket de Docker
                $commandToGetContainer = "docker --host unix:///var/run/docker.sock ps --format '{{.Names}}' | grep mariadb";
            } else {
                // Si estamos fuera del contenedor
                $commandToGetContainer = "docker ps --format '{{.Names}}' | grep mariadb";
            }
            
            $nameContainer = shell_exec($commandToGetContainer);
            $nameContainer = trim($nameContainer);
            
            if(empty($nameContainer)) {
                $this->error("No se encontró el contenedor de MariaDB.");
                return 1;
            }

            $dbVolume = $nameContainer . '_mysqldata1';
            $timestamp = now()->format('Ymd_His');
            $backupName = "mysqldata_$timestamp.tar.gz";
            $localPath = storage_path("app/backups/{$backupName}");
            
            // Asegurarse que el directorio de backups existe
            if (!file_exists(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            if ($isInContainer) {
                // Si estamos en un contenedor, usar el socket de Docker para el comando tar
                $dockerVolumePath = "/var/lib/docker/volumes/{$dbVolume}/";
                $command = "docker --host unix:///var/run/docker.sock run --rm -v {$dockerVolumePath}:/source -v {$localPath}:/backup.tar.gz alpine tar -czf /backup.tar.gz -C /source .";
            } else {
                // Si estamos fuera del contenedor
                $dockerVolumePath = "/var/lib/docker/volumes/{$dbVolume}/";
                $command = "tar -czf {$localPath} {$dockerVolumePath}";
            }

            exec($command, $output, $resultCode);
        
            if ($resultCode !== 0) {
                $this->error("Error al comprimir el volumen: " . implode("\n", $output));
                return 1;
            }
        
            $this->info("Respaldo realizado exitosamente en: {$localPath}");
            return 0;

        } catch (Exception $e) {
            Log::error("Backup failed -- Line: {$e->getLine()} - Message: {$e->getMessage()} - File: {$e->getFile()}");
            return [
                'success' => false,
                'message' => 'Error inesperado: ' . $e->getMessage()
            ];
        }
    }




    

}
