<?php

namespace App\Admin\Controllers;

use App\Models\CallModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Http\Controllers\CallCenter\CallCenterController;
use App\Models\Configuration;
use App\Http\Controllers\Controller;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Http\Request;
use Carbon\Carbon;


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

        $grid->column("caller_phone_number", __("Caller Phone"))->sortable();
        $grid->column("language", __("Language Selected"))->sortable();
        $grid->column("agent_phone_number", __("Agent Phone"))->sortable();

        $grid->column('created_at', __('Date Recorded'))
            ->display(function ($item) {
            return Carbon::parse($item)->diffForHumans();
        })->sortable();

        // $grid->column('created_at', __('Date Recorded'))->sortable();

        $grid->column("call_duration", __("Duration (seconds)"));
        $grid->column("recording_url", __("Recording"))->downloadable(); 
        

        $grid->filter(function($search_param){
            $search_param->disableIdfilter();
            $search_param->like('phone', __("Search by Caller Number"));
        });

        $grid->disableCreateButton();

        return $grid;
    }


    // /**
    //  * Make a show builder.
    //  *
    //  * @param mixed $id
    //  * @return Show
    //  */
    // protected function detail($id)
    // {
    //     $show = new Show(Call::findOrFail($id));

    //     return $show;
    // }


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
