<?php

namespace App\Admin\Controllers;

use App\Models\CarPark;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CarParkController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CarPark);

        $grid->id('Id');
        $grid->community_id('Community id');
        $grid->no('No');
        $grid->floor_number('Floor number');
        $grid->status('Status');
        $grid->price('Price');
        $grid->size('Size');
        $grid->created_at('Created at');
        $grid->updated_at('Updated at');

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
        $show = new Show(CarPark::findOrFail($id));

        $show->id('Id');
        $show->community_id('Community id');
        $show->no('No');
        $show->floor_number('Floor number');
        $show->status('Status');
        $show->price('Price');
        $show->size('Size');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CarPark);

        $form->number('community_id', 'Community id');
        $form->text('no', 'No');
        $form->switch('floor_number', 'Floor number')->default(1);
        $form->switch('status', 'Status')->default(1);
        $form->decimal('price', 'Price');
        $form->text('size', 'Size');

        return $form;
    }
}
