<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Question;
use DateTime;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class QuestionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Question';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Question());

        $grid->column('created_at', __('Asked on'))->display(function ($created_at) {
            return (new DateTime($created_at))->format('d-m-Y H:i:s');
        });
        $grid->column('category_id', __('Category'))->display(function ($category_id) {
            return $this->category->name;
        });
        $grid->column('administrator_id', __('Asked by'))->display(function ($administrator_id) {
            return $this->asked_by->name;
        });
        // $grid->column('answered_by', __('Answered by'));
        // $grid->column('is_answered', __('Is answered'));
        $grid->column('question', __('Question'));
        // $grid->column('answer', __('Answer'));
        // $grid->column('question_images', __('Question images'));
        // $grid->column('answer_images', __('Answer images'));

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
        $show = new Show(Question::findOrFail($id));

        // return view('widgets.questions', compact('show'));

        $show->field('created_at', __('Asked on'))->as(function ($created_at) {
            return $created_at->format('d-m-Y H:i:s');
        });
        $show->field('category_id', __('Category'))->as(function ($category_id) {
            return $this->category->name;
        });
        $show->field('administrator_id', __('Asked by'))->as(function ($administrator_id) {
            return $this->asked_by->name;
        });
        // $show->field('answered_by', __('Answered by'));
        // $show->field('is_answered', __('Is answered'));
        $show->field('question', __('Question'));
        // $show->field('answer', __('Answer'));
        $show->field('question_images', __('Question images'))->image();
        // $show->field('answer_images', __('Answer images'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Question());
        $form->hidden('administrator_id');
        // $form->number('answered_by', __('Answered by'));
        // $form->switch('is_answered', __('Is answered'));
        $form->select('category_id', __('Category'))->options(Category::pluck('name', 'id'));
        $form->textarea('question', __('Question'))->required();
        // $form->textarea('answer', __('Answer'));
        $form->multipleImage('question_images', __('Question images'));
        // $form->textarea('answer_images', __('Answer images'));

        return $form;
    }
}
