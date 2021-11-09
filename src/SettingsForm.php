<?php

namespace Jarnail\Wpt;

class SettingsForm extends Helper\Message implements Interface\Form
{
  private $cache;

  function __construct(Cache &$cache)
  {
    $this->message_buckets = [];

    $this->cache = $cache;

    $this->process_submission();
  }

  public function process_submission()
  {
    if( ! $this->is_submitted()) {
      return;
    }

    $this->validate();

    if($this->is_valid()) {
      $this->update_settings();
    }
  }

  public function is_submitted()
  {
    if(isset($_POST['form']) && 'settings' == $_POST['form']) {
      return true;
    }

    return false;;
  }

  public function validate()
  {
    if( ! isset($_POST['wp-install-path-prefix']) || empty($_POST['wp-install-path-prefix']) )
    {
      $this->add_message_to_bucket(
        [
          'head' => 'WordPress Installation Path Prefix',
          'body' => "Invalid WordPress Installation Path Prefix"
        ], 
        'error'
      );
    }
  }

  public function is_valid()
  {
    return 1 > count($this->get_message_bucket('error'));
  }

  public function update_settings()
  {
    $value = $_POST['wp-install-path-prefix'];
    $value = strpos($value, '/', -1) ? substr($value, 0, (strlen($value)-1)) : $value;

    $this->cache->set_setting('wp-install-path-prefix', $value);

    $this->add_message_to_bucket(
      [
        'head' => 'WordPress Installation Path Prefix',
        'body' => "Saved successfully"
      ], 
      'success'
    );
  }
}