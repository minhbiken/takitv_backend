<?php

namespace App\Console\Commands;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Commands\Command;
class NotifyTelegram extends Command
{
    /**
    * @var string Command Name
    */
    protected $name = "help";

    /**
    * @var string Command Description
    */
    protected $description = "Start Command to get you started";


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $activity = Telegram::getUpdates();
        $lastestActivity = end($activity);
        if( isset($lastestActivity) ) {
            $text = $lastestActivity->getMessage()->text;
            preg_match('/\/ping /', $text, $matches, PREG_OFFSET_CAPTURE);
            if( isset($matches[0][0]) && $matches[0][0] == '/ping ' ) {
                $domainRoot = explode(' ', $text);
                $domain = $domainRoot[1];
                $wait = 10; // wait Timeout In Seconds
                $fp = @fsockopen($domain, 80, $errCode, $errStr, $wait);
                if (!$fp) {
                    if ( $errCode == '10060' ) {
                        $text = "Ping $domain ==> Timeout over 10s";
                    } else {
                        $text = "Ping $domain ==> ERROR: $errCode - $errStr";
                    }
                } else {
                    $text = "Ping $domain ==> Success";
                }
                Telegram::sendMessage([
                    'chat_id' => env('TELEGRAM_CHANNEL_ID', '-4061154988'),
                    'parse_mode' => 'HTML',
                    'text' => $text
                ]);
            }
        }
    }
}
