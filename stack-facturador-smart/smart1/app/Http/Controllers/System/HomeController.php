<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Models\System\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Version\Package\Version;
use Symfony\Component\Process\Process;
use Tremby\LaravelGitVersion\GitVersionHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;

class HomeController extends Controller
{
    public function index()
    {
        $clients = Client::get();
        $delete_permission = config('tenant.admin_delete_client');
        // $avail = new Process(['df', '-m', '-h', '--output=avail', '/']);
        // $avail->setEnv([]);
        // $avail->run();
        //  $discused =  $avail->getOutput();
        // $disc_used = $discused != "" ? substr($discused, 0, -1) : 0;
         $i_used = '';
        $process = new Process(['df', '-m', '-h', '--output=pcent', '/']);
        $process->run();

        // if (!$process->isSuccessful()) {
        //     throw new \RuntimeException($process->getErrorOutput());
        // }
        $disc_used ="0"; 
        $output = $process->getOutput();
        $lines = explode("\n", trim($output));

        // Obtén el último porcentaje de espacio disponible
        $lastLine = end($lines);
        $disc_used = trim($lastLine);
        // if($disc_used == ""){
        //     $disc_used = "75%";
        // }
 

        // if ($disc_used != 0) {
        //     $inodes = new Process(['df', '-i', '/', '|', 'awk', "'{print $5}'", '|', 'tail', '-n', '1']);
        //     $inodes->run();
        //     $i_used = $inodes->getOutput();
        // }
        // $i_used = $i_used != "" ? substr($i_used, 0, -1) : 0;
        $i_used ="0";
        $command = "df -i / | awk '{print $5}' | tail -n 1";
        $i_used = exec($command);
        
        // $df = new Process(['du', '-sh', storage_path(), '|', 'cut', '-f1']);
        // $df->run();
        // $storage_size = $df->getOutput();
        // $storage_size = $storage_size != "" ? substr($storage_size, 0, -1) : 0;
        
        $command = "du -sh " . storage_path() . " | cut -f1";
        $storage_size ="0";    
        $process = Process::fromShellCommandline($command);
        $process->run();
        if ($process->isSuccessful()) {
            $storage_size = trim($process->getOutput());
           
        }     
        $id = new Process(['git', 'describe', '--tags']);;
        $id->run();
        $version = $id->getOutput();
        $user = Auth::user();
        $permissions = $user->permissions->pluck('permission')->toArray();
        return view('system.dashboard')->with('clients', count($clients))
                ->with('delete_permission', $delete_permission)
                ->with('disc_used', $disc_used)
                ->with('i_used', $i_used)
                ->with('permissions', $permissions)
                ->with('storage_size', $storage_size)
                ->with('version', $version);
    }
}
