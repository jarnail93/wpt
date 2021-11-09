<?php

namespace Jarnail\Wpt\Interface;

interface Message
{
  public function get_message_bucket($bucket): array;

  public function add_message_to_bucket($msg, $bucket): Message;

  public function get_message_buckets(): array;
}