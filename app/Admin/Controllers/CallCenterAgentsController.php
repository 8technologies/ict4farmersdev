<?php

namespace App\Admin\Controllers;

use App\Models\Call;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Http\Controllers\CallCenter\CallCenterController;
use App\Models\AgentProfile;
use App\Http\Controllers\Controller;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Http\Request;
use Carbon\Carbon;


class CallCenterAgentsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'UNFFE Call Center Agents';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new AgentProfile());
        $grid->column("name", __("Name"))->sortable();
        $grid->column("phone_number", __("Phone Number"))->sortable();
        $grid->column("region", __("Region"))->sortable();
        $grid->column("district", __("District"))->sortable();
        $grid->column("specific_role", __("Role"))->sortable();
        $grid->column("language", __("Languages"))->display(function ($value) {
            if ($value === null || $value === '') {
                return '';
            }

            $values = array_filter(array_map('trim', explode(',', $value)));
            return implode(', ', $values);
        })->sortable();
        $grid->column("inquiry_category", __("Inquiry Category"))->display(function ($value) {
            if ($value === null || $value === '') {
                return '';
            }

            $values = array_filter(array_map('trim', explode(',', $value)));
            return implode(', ', $values);
        })->sortable();
        $grid->column("priority", __("Priority"))->sortable();
        $grid->column("is_active", __("Active"))->display(function ($value) {
            return $value ? 'Yes' : 'No';
        })->sortable();

        $grid->column('created_at', __('Date Registered'))
            ->display(function ($item) {
            return Carbon::parse($item)->diffForHumans();
        })->sortable();

        $grid->filter(function($search_param){
            $search_param->disableIdfilter();
            $search_param->like('name', __("Search for Agent by Name"));
            $search_param->like('phone_number', __("Search for Agent by Phone Number"));
            $search_param->like('district', __("Search for Agent by District"));
            $search_param->like('specific_role', __("Search for Agent by Role"));
            $search_param->like('language', __("Search for Agent by Language"));
            $search_param->like('inquiry_category', __("Search for Agent by Category"));
        });


        return $grid;
    }


       /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AgentProfile());
        $form->text('name', __("Agent Name"))->required();
        $form->text('phone_number', __("Phone Number"))->required();
        $form->text('region', __("Region"))->required();
        $form->text('district', __("District"))->required();
        $form->text('specific_role', __("Role"))->required();
        $form->tags('language', __("Languages"))->options([
            'English' => 'English',
            'Luganda' => 'Luganda',
            'Runyakitara' => 'Runyakitara',
            'Swahili' => 'Swahili',
        ])->required();
        $form->tags('inquiry_category', __("Inquiry Category"))->options([
            'Coffee' => 'Coffee',
            'Farming' => 'Farming',
        ]);
        $form->number('priority', __("Priority"))->default(0)->min(0);
        $form->switch('is_active', __("Active"))->default(1);

        return $form;
    }

}
