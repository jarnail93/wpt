<?php

namespace Jarnail\Wpt;

use Flintstone\Flintstone as Storage;

class Cache {

  private $db;

  function __construct($database, $config) {
    
    $this->db = new Storage('cache', ['dir' => ROOT_PATH]);

    $this->set_default();
  }

  private function set_default()
  {
    if( ! $this->get_settings() )
    {
      $this->set_setting('wp-install-path-prefix', '/var/www/');
    }

    if( ! $this->get_essentials() )
    {
      $fields = InstallationForm::get_fields();
      foreach ($fields as $field => $data)
      {
        $this->set_essential($field, $data['value']);
      }
    }
  }

  public function get($key)
  {
    return $this->db->get($key);
  }

  public function set($key, $data)
  {
    $this->db->set($key, $data);
  }

  public function delete($key)
  {
    return $this->db->delete($key);
  }

  public function update_suggestion($group, $option)
  {
    // Init value
    $group[$option] = isset($group[$option]) 
      ? $group[$option] 
      : ['value' => '', 'suggestion' => []];

    // Populate suggestion
    empty($group[$option]['value']) 
      ? null 
      : array_push($group[$option]['suggestion'], $group[$option]['value']);

    // Clear suggestion
    $group[$option]['suggestion'] = array_unique($group[$option]['suggestion']);
    $group[$option]['suggestion'] = array_filter($group[$option]['suggestion']);

    return $group;
  }

  public function set_setting($name, $value)
  {
    $settings = $this->get_settings();

    $settings = $this->update_suggestion($settings, $name);

    // Update value
    $settings[$name]['value'] = $value;

    $this->set('settings', $settings);
  }

  public function get_setting($name)
  {
    $settings = $this->get_settings();

    return array_key_exists($name, $settings) ? $settings[$name] : null;
  }

  public function get_settings()
  {
    $settings = $this->db->get('settings');

    return is_array($settings) ? $settings : [];
  }

  public function set_essential($name, $value)
  {
    $essentials = $this->get_essentials();

    $essentials = $this->update_suggestion($essentials, $name);

    // Update value
    $essentials[$name]['value'] = $value;

    $this->set('essentials', $essentials);
  }

  public function get_essential($name)
  {
    $essentials = $this->get_essentials();

    return array_key_exists($name, $essentials) 
      ? $essentials[$name] 
      : ['value' => '', 'suggestion' => []];
  }

  public function get_essentials()
  {
    $essentials = $this->db->get('essentials');

    return is_array($essentials) ? $essentials : [];
  }
}