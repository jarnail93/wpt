<?php

namespace Jarnail\Wpt\Interface;

interface Form {

  public function process_submission();

  public function validate();

  public function is_valid();
}