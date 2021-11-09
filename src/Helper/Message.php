<?php

namespace Jarnail\Wpt\Helper;

use Jarnail\Wpt\Interface;

class Message implements Interface\Message
{
  public $message_buckets;

  public function get_message_bucket($bucket): array
  {
    if(isset($this->message_buckets[$bucket])) {
      return $this->message_buckets[$bucket];
    }

    return [];
  }

  public function add_message_to_bucket($msg, $bucket): Message
  {
    $this->message_buckets[$bucket][] = $msg;

    return $this;
  }

  public function get_message_buckets(): array
  {
    return $this->message_buckets;
  }
}