<?php

namespace App\Bot;


use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Http\Request;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;

class Webhook extends WebhookHandler
{
    protected $_bot;
    private $update;
    public function handle(Request $request, TelegraphBot $bot): void
    {
        $update = new Update($request->all(), $bot->id);
        $this->update = $update;
        $this->_bot = $bot;
        $chat = $update->chat();
        $message = $update->message();
        $msg_id = null; //if $msg_id is not null,the chat is private chat text.

        $sources = explode(",", env('SOURCES'));
        if (env("SOURCES") && !in_array($update->id(), $sources)) return;

        if ($update->is_message() && $update->is_private_chat()) {
            $res = $chat->message("Please wait...")->send();
            $res = json_decode($res, true);
            $msg_id = $res['result']['message_id'];
        }

        $is_about_class = gemini($message);
        if ($is_about_class == 'Yes') {
            if ($msg_id) {
                $chat->edit($msg_id)->markdown("Yooo! You better check this message\n ``` " . $message . "```")->send();
                return;
            }
            $ping = TelegraphChat::where('chat_id', env('PING_ADDRESS'))->first();
            if (!$ping) return;
            $ping->markdown("Yooo! You better check this message\n ``` " . $message . "```")->send();
        } else if ($msg_id) {
            $chat->edit($msg_id)->markdown("Does't seem relevant message 🗑🗑🗑 \n ``` " . $message . "```")->send();
        }
    }
}
