<?php

namespace Jarnail\Wpt;

class MessageHandler
{
  public $message_types = [
    'error'   => 'danger',
    'warning' => 'warning',
    'info'    => 'info',
    'success' => 'success'
  ];

  public $messages = [];

  public function __construct(array $message_emitters)
  {
    $filtered = [];

    foreach($message_emitters as $instance)
    {
      if($instance instanceof Interface\Message)
      {
        $filtered[] = $instance;
      }
    }

    foreach ($filtered as $messanger)
    {
      $message_buckets = $messanger->get_message_buckets();

      foreach ($message_buckets as $message_type => $bucket)
      {
        if( ! $this->valid_message_type($message_type))
        {
          $message_type = 'info';
        }

        foreach ($bucket as $message)
        {
          if(is_array($message))
          {
            $value = "<h4>{$message['head']}</h4>{$message['body']}";
          }
          else {
            $value = $message;
          }

          $this->messages[$message_type][] = $value;
        }
      }
    }
  }

  public function render()
  {
    if(count($this->messages)) {
      foreach ($this->messages as $message_type => $list) {
        $this->print_messages($message_type, $list);
      }
    }
  }

  public function valid_message_type($message_type)
  {
    return in_array($message_type, array_keys($this->message_types));
  }

  public function print_messages($message_type, $list)
  {
    $separater = '<hr>';

    $html = <<<HTML
      <div class="alert alert-{$this->message_types[$message_type]}" role="alert">
    HTML;

    $tmp = [];
    foreach ($list as $message_html) {
      $tmp[] = $message_html;
    }
    $html .= implode($separater, $tmp);

    $html .= <<<HTML
      </div>
    HTML;

    echo $html;
  }
}