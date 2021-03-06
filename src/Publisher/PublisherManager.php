<?php namespace Orchestra\Foundation\Publisher;

use Exception;
use Illuminate\Support\Manager;
use Orchestra\Memory\ContainerTrait;

class PublisherManager extends Manager
{
    use ContainerTrait;

    /**
     * Create a new driver instance.
     *
     * @param  string  $driver
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        return $this->app->make("orchestra.publisher.{$driver}");
    }

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->memory->get('orchestra.publisher.driver', 'ftp');
    }

    /**
     * Execute the queue.
     *
     * @return bool
     */
    public function execute()
    {
        $messages = $this->app->make('orchestra.messages');
        $queues   = $this->queued();
        $fails    = [];

        foreach ($queues as $queue) {
            try {
                $this->driver()->upload($queue);

                $messages->add('success', trans('orchestra/foundation::response.extensions.activate', [
                    'name' => $queue,
                ]));
            } catch (Exception $e) {
                // this could be anything.
                $messages->add('error', $e->getMessage());
                $fails[] = $queue;
            }
        }

        $this->memory->put('orchestra.publisher.queue', $fails);

        return true;
    }

    /**
     * Add a process to be queue.
     *
     * @param  string  $queue
     *
     * @return bool
     */
    public function queue($queue)
    {
        $queue = array_unique(array_merge($this->queued(), (array) $queue));
        $this->memory->put('orchestra.publisher.queue', $queue);

        return true;
    }

    /**
     * Get a current queue.
     *
     * @return array
     */
    public function queued()
    {
        return $this->memory->get('orchestra.publisher.queue', []);
    }
}
