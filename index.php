<?php
  // Constants
  define('ROOT_PATH', __DIR__ );

  // Error logging
  error_reporting( E_ALL );
  ini_set("error_log", ROOT_PATH."/anything.log");

  // Load vendor
  require ROOT_PATH . '/vendor/autoload.php';

  // Helper functions
  use Jarnail\Wpt\Helper\Functions;

  use Jarnail\Wpt\Cache;
  $cache = new Cache('cache', ['dir' => ROOT_PATH]);

  // Process Settings Form
  use Jarnail\Wpt\SettingsForm;
  $SForm = new SettingsForm($cache);

  // Process Installation Form
  use Jarnail\Wpt\InstallationForm;
  $IForm = new InstallationForm($cache);

  // Repopulate form
  $should_repop = $IForm->is_submitted() & !$IForm->is_compiled();
  $frpop = Functions::form_re_pop($should_repop);

  // Collect and Display Messages
  use Jarnail\Wpt\MessageHandler;
  $messages = new MessageHandler([
    $SForm,
    $IForm
  ]);

  // Get saved data to populate suggestions
  $settings = $cache->get_settings();
  $essentials = $cache->get_essentials();

  \devel($settings);
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>WP Installation Wrapper</title>

  <!-- CSS only -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

  <!-- Icons only -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.6.1/font/bootstrap-icons.css">

  <!-- JavaScript Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>

  <!-- jQuery 3.6.0 -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</head>

<body>
  <div class="container-fluid">

    <!-- Settings panel-->
    <form id="settings-panel" method="post" style="display: none;">
      <input type="hidden" name="form" value="settings">
      <div class="border-start border-end border-bottom p-3" >
        <div class="row">
          <div class="col-3 mb-3">
            <label for="wp-install-path-prefix" class="form-label">WordPress Installation Path Prefix</label>
            <input id="wp-install-path-prefix" name="wp-install-path-prefix" 
              type="text" class="form-control" placeholder="/var/www/wp" 
              value="<?= $settings['wp-install-path-prefix']['value'] ?>" list="wp-install-path-prefix-suggestion">
              <?php if( count($settings['wp-install-path-prefix']['suggestion']) ) { ?>
              <datalist id="wp-install-path-prefix-suggestion">
                <?php foreach ($settings['wp-install-path-prefix']['suggestion'] as $value) { ?>
                  <option value="<?= $value ?>"><?= $value ?></option>
                <?php } ?>
              </datalist>
              <?php } ?>
          </div>

          <!-- Save -->
          <div class="col-12 mt-2 mr-2mb-2">
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </div>
      </div>
    </form>

    <!-- Accordions -->
    <form method="post">
      <input type="hidden" name="form" value="installation">
      <div class="row">
        <div class="col-11">
          <h1>WP Installation Wrapper <small>(Works on top of fresh-wp.sh)</small></h1>
        </div>
        <div class="col-1">
          <h1 class="d-flex justify-content-end">
            <span id="settings-panel-handle" type="button" class="bi bi-gear-fill" data-state="close"></span>
          </h1>
        </div>

        <!-- Messages -->
        <div class="col-12">
          <?php $messages->render(); ?>
        </div>

        <div class="col-12">
          <div class="accordion" id="accordionEssentials">
            <div class="accordion-item">
              <h2 class="accordion-header" id="essentials">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  Essential params
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="essentials" data-bs-parent="#accordionEssentials">
                <div class="accordion-body">
                  <div class="row">
                    <div class="col-4 mb-3">
                      <label for="db-host" class="form-label">Database Host</label>
                      <input id="db-host" type="text" name="db-host" class="form-control" placeholder="localhost" value="<?= $frpop('db-host', $essentials['db-host']['value']) ?>" list="db-host-suggestion">
                      <?php if( count($essentials['db-host']['suggestion']) ) { ?>
                      <datalist id="db-host-suggestion">
                        <?php foreach ($essentials['db-host']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-4 mb-3">
                      <label for="db-user" class="form-label">Database User</label>
                      <input id="db-user" type="text" name="db-user" class="form-control" placeholder="root" value="<?= $frpop('db-user', $essentials['db-user']['value']) ?>" list="db-user-suggestion">
                      <?php if( count($essentials['db-user']['suggestion']) ) { ?>
                      <datalist id="db-user-suggestion">
                        <?php foreach ($essentials['db-user']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-4 mb-3">
                      <label for="db-password" class="form-label">Database Password</label>
                      <input id="db-password" type="text" name="db-password" class="form-control" placeholder="********" value="<?= $frpop('db-password', $essentials['db-password']['value']) ?>" list="db-password-suggestion">
                      <?php if( count($essentials['db-password']['suggestion']) ) { ?>
                      <datalist id="db-password-suggestion">
                        <?php foreach ($essentials['db-password']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-4 mb-3">
                      <label for="wp-user" class="form-label">WordPress User</label>
                      <input id="wp-user" type="text" name="wp-user" class="form-control" placeholder="admin" value="<?= $frpop('wp-user', $essentials['wp-user']['value']) ?>" list="wp-user-suggestion">
                      <?php if( count($essentials['wp-user']['suggestion']) ) { ?>
                      <datalist id="wp-user-suggestion">
                        <?php foreach ($essentials['wp-user']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-4 mb-3">
                      <label for="wp-password" class="form-label">WordPress Password</label>
                      <input id="wp-password" type="text" name="wp-password" class="form-control" placeholder="***********" value="<?= $frpop('wp-password', $essentials['wp-password']['value']) ?>" list="wp-password-suggestion">
                      <?php if( count($essentials['wp-password']['suggestion']) ) { ?>
                      <datalist id="wp-password-suggestion">
                        <?php foreach ($essentials['wp-password']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-4 mb-3">
                      <label for="wp-email" class="form-label">WordPress Email</label>
                      <input id="wp-email" type="text" name="wp-email" class="form-control" placeholder="admin@localhost" value="<?= $frpop('wp-email', $essentials['wp-email']['value']) ?>" list="wp-email-suggestion">
                      <?php if( count($essentials['wp-email']['suggestion']) ) { ?>
                      <datalist id="wp-email-suggestion">
                        <?php foreach ($essentials['wp-email']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-3 mb-3">
                      <label for="wp-url" class="form-label">WordPress URL</label>
                      <input id="wp-url" type="text" name="wp-url" class="form-control" placeholder="http://localhost/wp" value="<?= $frpop('wp-url', $essentials['wp-url']['value']) ?>" list="wp-url-suggestion">
                      <?php if( count($essentials['wp-url']['suggestion']) ) { ?>
                      <datalist id="wp-url-suggestion">
                        <?php foreach ($essentials['wp-url']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-3 mb-3">
                      <label for="wp-title" class="form-label">WordPress Title</label>
                      <input id="wp-title" type="text" name="wp-title" class="form-control" placeholder="WP" value="<?= $frpop('wp-title', $essentials['wp-title']['value']) ?>" list="wp-title-suggestion">
                      <?php if( count($essentials['wp-title']['suggestion']) ) { ?>
                      <datalist id="wp-title-suggestion">
                        <?php foreach ($essentials['wp-title']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                    <div class="col-3 mb-3">
                      <label for="wp-install-path" class="form-label">WordPress Installation Path</label>
                      <input id="wp-install-path" type="text" name="wp-install-path" class="form-control" placeholder="/wp" value="<?= $frpop('wp-install-path', $essentials['wp-install-path']['value']) ?>" list="wp-install-path-suggestion">
                      <?php if( count($essentials['wp-install-path']['suggestion']) ) { ?>
                      <datalist id="wp-install-path-suggestion">
                        <?php foreach ($essentials['wp-install-path']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                      <div class="form-text"><?= $settings['wp-install-path-prefix']['value'] ?></div>
                    </div>
                    <div class="col-3 mb-3">
                      <label for="db-name" class="form-label">WordPress Database Name</label>
                      <input id="db-name" type="text" name="db-name" class="form-control" placeholder="wp" value="<?= $frpop('db-name', $essentials['db-name']['value']) ?>" list="db-name-suggestion">
                      <?php if( count($essentials['db-name']['suggestion']) ) { ?>
                      <datalist id="db-name-suggestion">
                        <?php foreach ($essentials['db-name']['suggestion'] as $value) { ?>
                          <option value="<?= $value ?>"><?= $value ?></option>
                        <?php } ?>
                      </datalist>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 my-3">
          <div class="d-grid gap-3 col-md-4 col-sm-6 mx-auto">
            <button type="submit" class="btn btn-primary">Compile</button>
            <button type="reset" class="btn btn-warning">Reset</button>
          </div>
        </div>
      </div>
    </form>

  </div>

  <script type="text/javascript">
    $(document).ready(function() {
      $(document).on('click', '#settings-panel-handle', function(ev) {
        let _this = $(this);

        if('close' == _this.data('state')) {
          _this.data('state', 'open');
          $('#settings-panel').slideDown();
        }
        else {
          _this.data('state', 'close');
          $('#settings-panel').slideUp();
        }
      });
    });
  </script>
</body>
</html>