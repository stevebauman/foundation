<?php namespace Orchestra\Foundation\Http\Presenters\TestCase;

use Mockery as m;
use Illuminate\Support\Fluent;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Orchestra\Foundation\Http\Presenters\Setting;

class SettingTest extends \PHPUnit_Framework_TestCase
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
     * Test Orchestra\Foundation\Http\Presenters\Setting::form()
     * method.
     *
     * @test
     */
    public function testFormMethod()
    {
        $app = $this->app;
        $model = new Fluent([
            'email_password' => 123456,
        ]);

        $app['Illuminate\Contracts\View\Factory'] = m::mock('\Illuminate\Contracts\View\Factory');

        $form = m::mock('\Orchestra\Contracts\Html\Form\Factory');
        $grid = m::mock('\Orchestra\Contracts\Html\Form\Grid');

        $siteFieldset = m::mock('\Orchestra\Contracts\Html\Form\Fieldset');
        $siteControl = m::mock('\Orchestra\Contracts\Html\Form\Control');

        $emailFieldset = m::mock('\Orchestra\Contracts\Html\Form\Fieldset');
        $emailControl = m::mock('\Orchestra\Contracts\Html\Form\Control');

        $stub = new Setting($form);

        $siteFieldset->shouldReceive('control')->times(3)->andReturn($siteControl);
        $siteControl->shouldReceive('label')->times(3)->andReturnSelf()
            ->shouldReceive('attributes')->twice()->andReturnSelf()
            ->shouldReceive('options')->once()->andReturnSelf();

        $emailFieldset->shouldReceive('control')->times(13)
                ->with(m::any(), m::any())->andReturn($emailControl);
        $emailControl->shouldReceive('label')->times(13)->andReturnSelf()
            ->shouldReceive('attributes')->once()->andReturnSelf()
            ->shouldReceive('options')->times(3)->andReturnSelf()
            ->shouldReceive('help')->twice()->with('email.password.help');

        $grid->shouldReceive('setup')->once()
                ->with($stub, 'orchestra::settings', $model)->andReturnNull()
            ->shouldReceive('fieldset')->once()
                ->with(trans('orchestra/foundation::label.settings.application'), m::type('Closure'))
                ->andReturnUsing(function ($t, $c) use ($siteFieldset) {
                    $c($siteFieldset);
                })
            ->shouldReceive('fieldset')->once()
                ->with(trans('orchestra/foundation::label.settings.mail'), m::type('Closure'))
                ->andReturnUsing(function ($t, $c) use ($emailFieldset) {
                    $c($emailFieldset);
                });

        $form->shouldReceive('of')->once()
                ->with('orchestra.settings', m::type('Closure'))
                ->andReturnUsing(function ($n, $c) use ($grid) {
                    $c($grid);

                    return 'foo';
                });

        $app['Illuminate\Contracts\View\Factory']->shouldReceive('make')->twice()
            ->with('orchestra/foundation::settings._hidden', m::type('Array'), [])
            ->andReturn('email.password.help');

        $this->assertEquals('foo', $stub->form($model));
    }
}
