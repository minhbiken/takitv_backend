<?php 
namespace App\Telegram;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Commands\Command;

class PingCommand extends Command {
    /**
    * @var string Command Name
    */
    protected string $name = "ping";

    /**
    *  @var string Command Description
    */
    protected string $description = "Ping Domain";

    /** @var array Holds parsed command arguments */
    protected array $arguments = ['domain'];


    /**
    * @inheritdoc
    */
    public function handle()
    {
        $commands = $this->getTelegram()->getCommands();

        // Build the list
        $response = '';
        foreach ($commands as $name => $command) {
            if ( $name == 'ping' ) {
                $domain = $this->getArguments();
                $domain = $domain[0];
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
                    'chat_id' => '-4012828427',
                    'parse_mode' => 'HTML',
                    'text' => $text
                ]);
                break;
            }
        }

        // Reply with the commands list
        $this->replyWithMessage(['text' => $response]);
    }
}