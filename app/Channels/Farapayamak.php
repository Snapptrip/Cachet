<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

use GuzzleHttp\Client;
use Illuminate\Notifications\Notification;

class FarapayamakMessage
{
    /** @var mixed */
    protected $data;
    /** @var array|null */
    protected $headers;
    /** @var string|null */
    protected $userAgent;
    /**
     * @param mixed $data
     *
     * @return static
     */
    public static function create($data = '')
    {
        return new static($data);
    }
    /**
     * @param mixed $data
     */
    public function __construct($data = '')
    {
        $this->data = $data;
    }
    /**
     * Set the Webhook data to be JSON encoded.
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Add a Webhook request custom header.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function header($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }
    /**
     * Set the Webhook request UserAgent.
     *
     * @param string $userAgent
     *
     * @return $this
     */
    public function userAgent($userAgent)
    {
        $this->headers['User-Agent'] = $userAgent;
        return $this;
    }
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->data,
            'headers' => $this->headers,
        ];
    }
}

class FarapayamakChannel
{
    /** @var Client */
    protected $client;
    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\Webhook\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $phone_number = $notifiable->routeNotificationFor('Farapayamak')) {
            return;
        }
        $text = $notification->toFarapayamak($notifiable)->toArray();
        $response = $this->client->post(config('services.webhook.url'), [
            'body' => json_encode([
                'username' => config('services.webhook.username'),
                'password' => config('services.webhook.password'),
                'from' => config('services.webhook.from'),
                'to' => $phone_number,
                'text' => Arr::get($text, 'data'),
            ]),
            'verify' => false,
        ]);
        if ($response->getStatusCode() >= 300 || $response->getStatusCode() < 200) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($response);
        }
    }
}
