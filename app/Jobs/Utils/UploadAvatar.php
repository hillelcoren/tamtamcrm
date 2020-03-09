<?php

namespace App\Jobs\Utils;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadAvatar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $file;
    protected $directory;

    public function __construct($file, $directory)
    {
        $this->file = $file;
        $this->directory = $directory;
    }

    public function handle(): ?string
    {
        //make dir
        Storage::makeDirectory('public/' . $this->directory, 0755);
        $tmp_file = sha1(time()) . ".png";
        $file_png =
            imagepng(imagecreatefromstring(file_get_contents($this->file)), sys_get_temp_dir() . '/' . $tmp_file);

        $path = Storage::putFile('public/' . $this->directory, new File(sys_get_temp_dir() . '/' . $tmp_file));
        $url = Storage::url($path);

        //return file path
        if ($url) {
            return $url;
        } else {
            return null;
        }

    }
}
