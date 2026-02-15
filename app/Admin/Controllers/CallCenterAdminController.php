<?php

namespace App\Admin\Controllers;

use App\Models\CallModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class CallCenterAdminController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Call Center Logs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new CallModel());

        $grid->model()->orderBy('id', 'desc');   // order by latest to be recorded
        $grid->model()->whereNotNull('status')->where('status', '!=', '');

        $grid->column("caller_phone_number", __("Caller Phone"))->sortable();
        $grid->column("language", __("Language"))->sortable();
        $grid->column("inquiry_category", __("Category"))->sortable();
        $grid->column("agent_phone_number", __("Agent Phone"))->sortable();
        $grid->column("agentProfile.name", __("Agent Name"));
        $grid->column("status", __("Status"))->display(function ($value) {
            if (!$value) {
                return '';
            }

            $normalized = strtolower(trim((string) $value));
            if ($normalized === 'success') {
                $class = 'label label-success';
            } elseif (in_array($normalized, ['failed', 'failure', 'error'], true)) {
                $class = 'label label-danger';
            } else {
                $class = 'label label-warning';
            }

            return new HtmlString('<span class="' . e($class) . '">' . e($value) . '</span>');
        })->sortable();
        $grid->column("duration_in_seconds", __("Duration (seconds)"))->display(function ($value) {
            return $value ?? $this->call_duration;
        });
        $grid->column("recording_path", __("Recording"))->display(function ($value) {
            if (!$value) {
                return '';
            }

            $url = route('admin.call_center_voice.recording', $this->id);
            return new HtmlString('<a href="' . e($url) . '" target="_blank" rel="noopener">Listen</a>');
        });
        $grid->column('created_at', __('Date Recorded'))
            ->display(function ($item) {
            return Carbon::parse($item)->diffForHumans();
        })->sortable();
        

        $grid->filter(function($search_param){
            $search_param->disableIdfilter();
            $search_param->like('caller_phone_number', __("Caller Phone"));
            $search_param->like('agent_phone_number', __("Agent Phone"));
            $search_param->like('dial_destination_number', __("Dialed Number"));
            $search_param->equal('language', __("Language"))->select([
                'English' => 'English',
                'Luganda' => 'Luganda',
                'Runyakitara' => 'Runyakitara',
                'Swahili' => 'Swahili',
                'Lugisu' => 'Lugisu',
            ]);
            $search_param->equal('inquiry_category', __("Category"))->select([
                'Coffee' => 'Coffee',
                'Farming' => 'Farming',
            ]);
            $search_param->like('status', __("Status"));
            $search_param->like('call_session_state', __("Call State"));
            $search_param->between('created_at', __("Logged At"))->datetime();
        });

        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        return $grid;
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CallModel::findOrFail($id));

        $show->field('session_id', __('Session ID'));
        $show->field('caller_phone_number', __('Caller Phone'));
        $show->field('caller_country', __('Caller Country'));
        $show->field('caller_carrier', __('Caller Carrier'));
        $show->field('direction', __('Direction'));
        $show->field('destination_number', __('Destination Number'));
        $show->field('call_start_time', __('Call Start Time'));
        $show->field('call_session_state', __('Call State'));
        $show->field('status', __('Status'));
        $show->field('language', __('Language'));
        $show->field('inquiry_category', __('Category'));
        $show->field('agent_phone_number', __('Agent Phone'));
        $show->field('agent_profile_id', __('Agent Profile ID'));
        $show->field('dial_destination_number', __('Dialed Number'));
        $show->field('dial_start_time', __('Dial Start Time'));
        $show->field('duration_in_seconds', __('Duration (seconds)'));
        $show->field('dial_duration_in_seconds', __('Dial Duration (seconds)'));
        $show->field('amount', __('Amount'));
        $show->field('currency_code', __('Currency'));
        $show->field('recording_path', __('Recording (Local)'))->as(function ($value) {
            if (!$value) {
                return '';
            }

            $url = route('admin.call_center_voice.recording', $this->id);
            return new HtmlString('<audio controls src="' . e($url) . '"></audio>');
        });
        $show->field('recording_url', __('Recording URL (External)'));
        $show->field('last_payload', __('Last Payload'))->as(function ($value) {
            if (!$value) {
                return '';
            }

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = json_encode($decoded, JSON_PRETTY_PRINT);
            }

            return new HtmlString('<pre>' . e($value) . '</pre>');
        });
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    public function recording($id)
    {
        $call = CallModel::findOrFail($id);
        $path = $call->recording_path;

        if (!$path || strncmp($path, 'recordings/', 11) !== 0) {
            abort(404, 'Recording not found.');
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($path)) {
            abort(404, 'Recording not found.');
        }

        $fullPath = $disk->path($path);
        $mime = $disk->mimeType($path) ?: 'audio/mpeg';

        return response()->file($fullPath, ['Content-Type' => $mime]);
    }


}


/*
    id
    session_id
    phone
    call_date
    call_type
    active
    recording_url
    agent_phone
    call_duration
    call_menu_selected
    language
    created_at
    updated_at
 */
