<?php


namespace App\Bot;

use App\Models\Chat;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;

class Update
{

    public  $update, $bot_id;
    public $message;

    public function __construct($update, $bot_id)
    {
        $this->bot_id = $bot_id;
        $this->update = json_decode(json_encode($update));
        $locale = Cache::get('lang_' . $this->id());
        if ($locale) app()->setLocale($locale);
    }



    public  function is_message()
    {
        return $this->update->message ?? false;
    }
    public  function is_callback()
    {
        return $this->update->callback_query ?? false;
    }
    public  function is_channel_post()
    {
        return $this->update->channel_post ?? false;
    }
    public  function is_inline_query()
    {
        return $this->update->inline_query ?? false;
    }

    public function pre_checkout_query()
    {
        return $this->update->pre_checkout_query ?? false;
    }

    public function is_contact()
    {
        return $this->update->message->contact->phone_number ?? false;
    }
    public  function has_video()
    {
        return $this->update->message->video ?? false;
    }

    public function video_id()
    {
        return $this->update->message->video->file_id ?? null;
    }

    public function contact()
    {
        return $this->update->message->contact->phone_number ?? null;
    }

    public  function from()
    {

        if ($this->is_message()) {
            return $this->update->message->from;
        } else if ($this->is_callback()) {
            return $this->update->callback_query->from;
        } else if ($this->is_channel_post()) {
            return $this->update->channel_post->sender_chat;
        } elseif ($this->is_inline_query()) {
            return $this->update->inline_query->from;
        }

        return null;
    }

    public function name()
    {
        return $this->from()->first_name ?? null;
    }

    public  function id()
    {
        if ($this->is_message()) {
            return $this->update->message->chat->id;
        } else if ($this->is_callback()) {
            return $this->update->callback_query->chat->id;
        } else if ($this->is_channel_post()) {
            return $this->update->channel_post->sender_chat->id;
        } elseif ($this->is_inline_query()) {
            return $this->update->inline_query->chat->id;
        } else if ($this->pre_checkout_query()) {
            return $this->pre_checkout_query()->chat->id;
        }

        return null;
    }

    public function is_private_chat()
    {
        try {
            return $this->update->message->chat->type == 'private' ? true : false;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public  function message()
    {
        if ($this->is_contact()) {
            return $this->update->message->contact->phone_number;
        } elseif ($this->is_message()) {
            return $this->update->message->text ?? null;
        } elseif ($this->is_callback()) {
            return $this->update->callback_query->data ?? null;
        } elseif ($this->is_inline_query()) {
            return $this->update->inline_query->query;
        }

        return null;
    }

    public function inline_query_id()
    {
        return $this->update->inline_query->id;
    }

    public function payment()
    {
        return $this->update->message->successful_payment ?? null;
    }
    public  function messageId()
    {
        if ($this->is_message()) {
            return $this->update->message->message_id;
        } else if ($this->is_callback()) {
            return $this->update->callback_query->message->message_id;
        } else if ($this->is_channel_post()) {
            return $this->update->channel_post->message_id;
        }

        return null;
    }

    public function caption()
    {
        return $this->update->message->caption ?? null;
    }

    public function successful_payment()
    {
        return $this->update->message->successful_payment ?? null;
    }

    public function chat()
    {
        $chat = TelegraphChat::where('chat_id', (string) $this->id())->where('telegraph_bot_id', $this->bot_id)->first();
        return $chat ?? $this->createChat();
    }

    public function createChat()
    {
        $chat_id = $this->id();
        $chat = new TelegraphChat();
        $chat->chat_id = (string) $chat_id;
        $chat->telegraph_bot_id = $this->bot_id;
        $chat->name = $this->name();
        $chat->save();
        return $chat;
    }
}
