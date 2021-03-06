<?php namespace Orchestra\Foundation\Http\Presenters\TestCase;

use Mockery as m;
use Illuminate\Support\Fluent;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Orchestra\Foundation\Http\Presenters\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->app = new Container();

        $this->app['app'] = $this->app;
        $this->app['orchestra.app'] = m::mock('\Orchestra\Contracts\Foundation\Foundation');
        $this->app['translator'] = m::mock('\Illuminate\Translation\Translator')->makePartial();

        $this->app['orchestra.app']->shouldReceive('handles');
        $this->app['translator']->shouldReceive('trans');

        Facade::clearResolvedInstances();
        Container::setInstance($this->app);
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->app);

        m::close();
    }

    /**
     * Test Orchestra\Foundation\Http\Presenters\User::table() method.
     *
     * @test
     */
    public function testTableMethod()
    {
        $app = $this->app;
        $model = m::mock('\Orchestra\Model\User');

        $app['html'] = m::mock('\Orchestra\Html\HtmlBuilder[create,raw]');

        $auth = m::mock('\Illuminate\Contracts\Auth\Guard');
        $user = m::mock('\Illuminate\Contracts\Auth\Authenticatable');
        $form = m::mock('\Orchestra\Contracts\Html\Form\Factory');
        $table = m::mock('\Orchestra\Contracts\Html\Table\Factory');

        $grid = m::mock('\Orchestra\Contracts\Html\Table\Grid');
        $column = m::mock('\Orchestra\Contracts\Html\Table\Column');

        $value = (object) [
            'fullname' => 'Foo',
            'roles' => [
                (object) ['id' => 1, 'name' => 'Administrator'],
                (object) ['id' => 2, 'name' => 'Member'],
            ],
        ];

        $auth->shouldReceive('user')->once()->andReturn($user);

        $stub = new User($auth, $form, $table);

        $column->shouldReceive('label')->twice()->andReturnSelf()
            ->shouldReceive('escape')->once()->with(false)->andReturnSelf()
            ->shouldReceive('value')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($column, $value) {
                    $c($value);

                    return $column;
                });
        $grid->shouldReceive('with')->once()->with($model)->andReturnNull()
            ->shouldReceive('sortable')->once()->andReturnNull()
            ->shouldReceive('layout')->once()->with('orchestra/foundation::components.table')->andReturnNull()
            ->shouldReceive('column')->once()->with('fullname')->andReturn($column)
            ->shouldReceive('column')->once()->with('email')->andReturn($column);
        $table->shouldReceive('of')->once()
                ->with('orchestra.users', m::type('Closure'))
                ->andReturnUsing(function ($t, $c) use ($grid) {
                    $c($grid);

                    return 'foo';
                });

        $app['html']->shouldReceive('create')->once()
                ->with('span', 'Administrator', m::any())->andReturn('administrator')
            ->shouldReceive('create')->once()
                ->with('span', 'Member', m::any())->andReturn('member')
            ->shouldReceive('create')->once()
                ->with('strong', 'Foo')->andReturn('Foo')
            ->shouldReceive('create')->once()->with('br')->andReturn('')
            ->shouldReceive('create')->once()->with('span', 'raw-foo', m::any())->andReturnNull()
            ->shouldReceive('raw')->once()->with('administrator member')->andReturn('raw-foo');

        $this->assertEquals('foo', $stub->table($model));
    }

    /**
     * Test Orchestra\Foundation\Http\Presenters\User::actions()
     * method.
     *
     * @test
     */
    public function testActionsMethod()
    {
        $app = $this->app;

        $auth = m::mock('\Illuminate\Contracts\Auth\Guard');
        $user = m::mock('\Illuminate\Contracts\Auth\Authenticatable');
        $form = m::mock('\Orchestra\Contracts\Html\Form\Factory');
        $table = m::mock('\Orchestra\Contracts\Html\Table\Factory');

        $builder = m::mock('\Orchestra\Contracts\Html\Table\Builder');
        $grid = m::mock('\Orchestra\Contracts\Html\Table\Grid');
        $column = m::mock('\Orchestra\Contracts\Html\Table\Column');

        $user->id = 2;

        $value = (object) [
            'id' => 1,
            'name' => 'Foo',
        ];

        $auth->shouldReceive('user')->once()->andReturn($user);

        $stub = new User($auth, $form, $table);

        $column->shouldReceive('label')->once()->with('')->andReturnSelf()
            ->shouldReceive('escape')->once()->with(false)->andReturnSelf()
            ->shouldReceive('headers')->once()->with(m::type('Array'))->andReturnSelf()
            ->shouldReceive('attributes')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($column, $value) {
                    $c($value);

                    return $column;
                })
            ->shouldReceive('value')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($column, $value) {
                    $c($value);

                    return $column;
                });
        $grid->shouldReceive('column')->once()->with('actions')->andReturn($column);

        $builder->shouldReceive('extend')->once()->with(m::type('Closure'))
            ->andReturnUsing(function ($c) use ($grid) {
                $c($grid);

                return 'foo';
            });

        $app['auth'] = m::mock('\Illuminate\Contracts\Auth\Guard');
        $app['html'] = m::mock('\Orchestra\Html\HtmlBuilder')->makePartial();
        $app['html']->shouldReceive('link')->once()
                ->with(handles("orchestra/foundation::users/1/edit"), m::any(), m::type('Array'))
                ->andReturn('edit')
            ->shouldReceive('link')->once()
                ->with(handles("orchestra/foundation::users/1/delete"), m::any(), m::type('Array'))
                ->andReturn('delete')
            ->shouldReceive('raw')->once()->with('editdelete')->andReturn('raw-edit')
            ->shouldReceive('create')->once()
                ->with('div', 'raw-edit', m::type('Array'))->andReturn('create-div');

        $this->assertEquals('foo', $stub->actions($builder));
    }

    /**
     * Test Orchestra\Foundation\Http\Presenters\User::form() method.
     *
     * @test
     */
    public function testFormMethod()
    {
        $app = $this->app;
        $model = m::mock('\Orchestra\Model\User');

        $auth = m::mock('\Illuminate\Contracts\Auth\Guard');
        $user = m::mock('\Illuminate\Contracts\Auth\Authenticatable');
        $form = m::mock('\Orchestra\Contracts\Html\Form\Factory');
        $table = m::mock('\Orchestra\Contracts\Html\Table\Factory');

        $grid = m::mock('\Orchestra\Contracts\Html\Form\Grid');
        $fieldset = m::mock('\Orchestra\Contracts\Html\Form\Fieldset');
        $control = m::mock('\Orchestra\Contracts\Html\Form\Control');

        $app['Orchestra\Contracts\Html\Form\Control'] = $control;
        $app['orchestra.role'] = m::mock('\Orchestra\Model\Role');

        $value = m::mock('stdClass');

        $roles = new Collection([
            new Fluent(['id' => 1, 'name' => 'Administrator']),
            new Fluent(['id' => 2, 'name' => 'Member']),
        ]);

        $value->shouldReceive('roles->get')->once()->andReturn($roles);

        $model->shouldReceive('hasGetMutator')->andReturn(false);

        $auth->shouldReceive('user')->once()->andReturn($user);

        $stub = new User($auth, $form, $table);

        $control->shouldReceive('label')->times(4)->andReturnSelf()
            ->shouldReceive('options')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($control) {
                    $c();

                    return $control;
                })
            ->shouldReceive('attributes')->once()->with(m::type('Array'))->andReturnSelf()
            ->shouldReceive('value')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($value) {
                    $c($value);
                });
        $fieldset->shouldReceive('control')->twice()->with('input:text', m::any())->andReturn($control)
            ->shouldReceive('control')->once()->with('input:password', 'password')->andReturn($control)
            ->shouldReceive('control')->once()->with('select', 'roles[]')->andReturn($control);
        $grid->shouldReceive('resource')->once()
                ->with($stub, 'orchestra/foundation::users', $model)->andReturnNull()
            ->shouldReceive('hidden')->once()->with('id')->andReturnNull()
            ->shouldReceive('fieldset')->once()->with(m::type('Closure'))
                ->andReturnUsing(function ($c) use ($fieldset) {
                    $c($fieldset);
                });
        $form->shouldReceive('of')->once()
                ->with('orchestra.users', m::any())
                ->andReturnUsing(function ($f, $c) use ($grid) {
                    $c($grid);

                    return 'foo';
                });

        $app['orchestra.role']->shouldReceive('lists')->once()
                ->with('name', 'id')->andReturn('roles');

        $stub->form($model);
    }
}
