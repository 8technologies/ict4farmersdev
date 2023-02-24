<?php

namespace App\Admin\Controllers;

use App\Models\Pest;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PestController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Pest';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {   
        $grid = new Grid(new Pest());

        // $grid->column('id', __('Id'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('image', __('Image'))->image('', 100, 100);
        $grid->column('description', __('Description'));
        $grid->column('cause', __('Cause'));
        $grid->column('cure', __('Cure'));
        // $grid->column('video', __('Video'));

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
        $show = new Show(Pest::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('cause', __('Cause'));
        $show->field('cure', __('Cure'));
        $show->field('image', __('Image'));
        $show->field('video', __('Video'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Pest());

        $form->text('name', __('Name'));
        $form->textarea('description', __('Description'));
        $form->textarea('cause', __('Cause'));
        $form->textarea('cure', __('Cure'));
        $form->image ('image', __('Image'))->uniqueName()->help('The maximum file size is 5MB.');
        $form->file('video', __('Video'))->rules('mimes:mp4,mkv,avi,flv,wmv,3gp,webm,ts|max:5000',[
            'mimes' => 'Video format is not supported',
            'max' => 'Video size is too large'
        ])->uniqueName()->help('The maximum file size is 5MB.');

        return $form;
    }
}
