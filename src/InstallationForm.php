<?php

namespace Jarnail\Wpt;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallationForm extends Helper\Message implements Interface\Form
{
  private $cache;

  private $db_server_conn;

  public $input;

  public $compiled;

  public static $fields = [
    'db-host'         => [
      'label'   => 'Database Host', 
      'value'   => 'localhost', 
      'cmd_arg' => '--db-host'
    ],
    'db-user'         => [
      'label'   => 'Database User', 
      'value'   => 'admin', 
      'cmd_arg' => '--db-user'
    ],
    'db-password'     => [
      'label'   => 'Database Password', 
      'value'   => 'Pass123#', 
      'cmd_arg' => '--db-pass'
    ],
    'db-name'         => [
      'label'   => 'Database Name', 
      'value'   => '', 
      'cmd_arg' => '--db-name'
    ],
    'wp-user'         => [
      'label'   => 'WordPress User', 
      'value'   => 'admin', 
      'cmd_arg' => '--user'
    ],
    'wp-password'     => [
      'label'   => 'WordPress Password', 
      'value'   => 'Pass123#', 
      'cmd_arg' => '--pass'
    ],
    'wp-email'        => [
      'label'   => 'WordPress Email', 
      'value'   => 'admin@localhost', 
      'cmd_arg' => '--email'
    ],
    'wp-url'          => [
      'label'   => 'WordPress URL', 
      'value'   => 'http://localhost', 
      'cmd_arg' => '--base-url'
    ],
    'wp-title'        => [
      'label'   => 'WordPress Title', 
      'value'   => 'WP Title', 
      'cmd_arg' => '--title'
    ],
    'wp-install-path' => [
      'label'   => 'WordPress Installation Path', 
      'value'   => './wp', 
      'cmd_arg' => '--path'
    ]
  ];

  function __construct(Cache &$cache)
  {
    $this->compiled = false;

    $this->message_buckets = [];

    $this->cache = $cache;

    $this->process_submission();
  }

  public static function get_fields()
  {
    return self::$fields;
  }

  public function process_submission()
  {
    if( ! $this->is_submitted())
    {
      return;
    }

    $this->validate();

    if($this->is_valid())
    {
      $this->update_settings();

      if($this->is_complieable())
      {
        if($this->compile())
        {
          $this->compiled = true;
        }
      }
    }
  }

  public function is_submitted()
  {
    if(isset($_POST['form']) && 'installation' == $_POST['form'])
    {
      return true;
    }

    return false;;
  }

  public function validate()
  {
    foreach (self::$fields as $name => $data)
    {
      if( ! isset($_POST[$name]) || empty($_POST[$name]) )
      {
        $this->add_message_to_bucket(
          "<b>{$data['label']}:</b> Invalid value", 
          'error'
        );
      }
    }
  }

  public function is_valid()
  {
    return 1 > count($this->get_message_bucket('error'));
  }

  public function update_settings()
  {
    $labels = [];
    foreach(self::$fields as $name => $data)
    {
      $this->cache->set_essential($name, $_POST[$name]);
      $this->input[$name] = $_POST[$name];
      $labels[] = $data['label'];
    }

    // Modify any input values as per the settings
    $this->update_input_path();

    $this->add_message_to_bucket(
      "<b>Cached successfully:</b> ".implode(', ', $labels), 
      'success'
    );
  }

  public function update_input_path()
  {
    $dir_prefix = $this->cache->get_setting('wp-install-path-prefix')['value'];

    $path = Helper\Functions::join_paths($dir_prefix, $this->input['wp-install-path']);

    $this->input['wp-install-path'] = $path;
  }

  public function is_complieable()
  {
    $flag = true;

    $flag &= $this->db_server_connectable($flag);
    $flag &= $this->db_name_available($flag);
    $flag &= $this->wp_dir_available($flag);

    return $flag;
  }

  public function db_server_connectable($flag)
  {
    if( ! $flag) return $flag;

    // New conncetion
    try
    {
      $conn = new \mysqli(
        $this->input['db-host'],
        $this->input['db-user'],
        $this->input['db-password']
      );
    }
    catch(\Exception $ex) {
      $conn = new \stdClass();
      $conn->connect_errno = $ex->getCode();
      $conn->connect_error = $ex->getMessage();
    }

    $who = "{$this->input['db-host']}@{$this->input['db-user']}";

    if ($conn->connect_errno)
    {
      $this->add_message_to_bucket([
          'head' => "Database connection issue ({$who})",
          'body' => "Description: {$conn->connect_error}"
        ],
        'error'
      );
      $connectable = false;
    }
    else
    {
      /* Set the desired charset after establishing a connection */
      $conn->set_charset('utf8mb4');
      $this->db_server_conn = $conn;
      $this->add_message_to_bucket("Database connected successfully: {$who}", 'info');
      $connectable = true;
    }

    return $connectable;
  }

  public function db_name_available($flag)
  {
    if( ! $flag) return $flag;

    try
    {
      $result = $this->db_server_conn->query(<<<SQL
        SHOW DATABASES LIKE "{$this->input['db-name']}"
      SQL);
    }
    catch(\Exception $ex) {
      $this->add_message_to_bucket([
          'head' => "Database connection issue ({$ex->getCode()})",
          'body' => "Description: {$ex->getMessage()}"
        ],
        'error'
      );
    }

    $available = 1 > $result->num_rows;

    if($available) {
      $this->add_message_to_bucket("Database name available: {$this->input['db-name']}", 'info');
    }
    else {
      $this->add_message_to_bucket(
        "Database name <b>not</b> available: {$this->input['db-name']}", 
        'error'
      );
    }

    return $available;
  }

  public function wp_dir_available($flag)
  {
    if( ! $flag) return $flag;

    $path = $this->input['wp-install-path'];

    if(is_dir(realpath($path)))
    {
      $this->add_message_to_bucket("WP Directory <b>not</b> available: {$path}", 'error');
      $available = false;
    }
    else
    {
      $this->add_message_to_bucket("WP Directory available: {$path}", 'info');
      $available = true;
    }

    return $available;
  }

  public function compile()
  {
    $cmd_args = [];
    foreach ($this->get_fields() as $name => $data) {
      $cmd_args[] = "{$data['cmd_arg']}=\"{$this->input[$name]}\"";
    }

    $command = implode(
      ' ',
      array_merge(
        ['bash', Helper\Functions::join_paths(getcwd(), './fresh-wp.sh')],
        $cmd_args
      )
    );

    // new Process() constructor try to escape the command and corrupt the whole 
    // command, which eventually doesn't run
    $process = Process::fromShellCommandline($command);

    $process->run();

    // executes after the command finishes
    if ( ! $process->isSuccessful())
    {
      try
      {
        throw new ProcessFailedException($process);
      }
      catch(\Exception $ex)
      {
        $msg = nl2br($ex->getMessage());
        $this->add_message_to_bucket([
            'head' => "Compilation issue ({$ex->getCode()})",
            'body' => $msg
          ],
          'error'
        );
      }
      return false;
    }

    $final_output = nl2br($process->getOutput());

    $this->add_message_to_bucket([
        'head' => 'Compiled successfully <small>with output:</small>',
        'body' => $final_output
      ], 
      'success'
    );

    return true;
  }

  public function is_compiled()
  {
    return $this->compiled;
  }
}