<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use LimitIterator;
use SplFileObject;

class SupportMessageSent extends Mailable
{
    use Queueable, SerializesModels;

    public $message;

    public $send_logs;

    public function __construct($message, $send_logs)
    {
        $this->message = $message;
        $this->send_logs = $send_logs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $system_info = null;
        $log_lines = [];

        /**
         * With self-hosted version of Ninja,
         * we are going to bundle system-level info
         * and last 10 lines of laravel.log file.
         */
        if ($this->send_logs !== false) {
            //$system_info = Ninja::getDebugInfo();

            $log_file = new SplFileObject(sprintf('%s/laravel.log', base_path('storage/logs')));

            $log_file->seek(PHP_INT_MAX);
            $last_line = $log_file->key();
            $lines = new LimitIterator($log_file, $last_line - 10, $last_line);

            $log_lines = iterator_to_array($lines);
        }

        return $this->from(config('mail.from.address'))//todo this needs to be fixed to handle the hosted version
                    ->subject('NEW SUPPORT MESSAGES')->markdown('email.support.message', [
            'message' => $this->message,
            'system_info' => $system_info,
            'laravel_log' => $log_lines
        ]);
    }
}
