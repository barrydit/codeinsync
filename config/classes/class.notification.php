<?php

class Notification
{
    private $callback;
    private $notified = false;
    private $repeatable = false;
    private $interval;
    private $lastNotifiedTime;
    private $output = '';

    public function __construct(callable $callback, bool $repeatable = false, int $interval = 300)
    {
        $this->callback = $callback;
        $this->repeatable = $repeatable;
        $this->interval = $interval;
        $this->lastNotifiedTime = time();
    }

    public function checkAndNotify()
    {
        if (!$this->notified || ($this->repeatable && (time() - $this->lastNotifiedTime) >= $this->interval)) {
            ob_start();
            call_user_func($this->callback);
            $this->output = ob_get_clean();
            $this->notified = true;
            $this->lastNotifiedTime = time();
        }
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function reset()
    {
        $this->notified = false;
    }

    public function isRepeatable()
    {
        return $this->repeatable;
    }

    public function setRepeatable($repeatable)
    {
        $this->repeatable = $repeatable;
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
    }
}

class NotificationManager
{
    private $notifications = [];

    public function addNotification(Notification $notification)
    {
        $this->notifications[] = $notification;
    }

    public function checkNotifications()
    {
        foreach ($this->notifications as $notification) {
            $notification->checkAndNotify();
        }
    }

    public function getNotificationsOutput()
    {
        $output = '';
        foreach ($this->notifications as $notification) {
            $output .= $notification->getOutput();
        }
        return $output;
    }

    public function resetNotification(Notification $notification)
    {
        $notification->reset();
    }
}