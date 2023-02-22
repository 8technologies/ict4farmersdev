<?php

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
