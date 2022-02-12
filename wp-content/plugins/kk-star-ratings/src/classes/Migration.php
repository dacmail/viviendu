<?php

/*
 * This file is part of bhittani/kk-star-ratings.
 *
 * (c) Kamal Khan <shout@bhittani.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bhittani\StarRating\classes;

use function Bhittani\StarRating\functions\autoload_class;
use InvalidArgumentException;

autoload_class(Stack::class);

class Migration extends Stack
{
    /**
     * @see https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
     * @see https://regex101.com/r/vkijKf/1/
     *
     * @var string
     */
    public const VERSION_REGEX = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    /** @var callable */
    protected $cron;

    public function cron(callable $cron): self
    {
        $this->cron = $cron;

        return $this;
    }

    public function isBusy(): bool
    {
        return ! $this->isEmpty()
            && $this->bottom()->status == 'working';
    }

    public function isPending(): bool
    {
        return ! ($this->isEmpty() || $this->isBusy());
    }

    public function flush(): self
    {
        while (! $this->isEmpty()) {
            $this->pop();
        }

        return $this;
    }

    public function create(string $tag, string $version, $payload): self
    {
        if (! preg_match(static::VERSION_REGEX, $version)) {
            throw new InvalidArgumentException("Can not create a migration. The string '{$version}' is not a valid semver.");
        }

        $this->push((object) [
            'payload' => $payload,
            'status' => 'pending',
            'tag' => $tag,
            'times' => 0,
            'timestamp' => time(),
            'version' => $version,
        ]);

        return $this;
    }

    /** @param bool|null $touch */
    public function patch(string $status, $payload = null, $touch = null): self
    {
        $migration = $this->bottom();

        $migration->status = $status;
        $migration->timestamp = time();

        if (! is_null($payload)) {
            $migration->payload = $payload;

            if (is_null($touch)) {
                $touch = true;
            }
        }

        if ($touch) {
            ++$migration->times;
        }

        return $this->replace($migration);
    }

    public function replace($migration): self
    {
        $this->shift();
        $this->unshift($migration);

        return $this;
    }

    public function remove(): self
    {
        $this->shift();

        return $this;
    }

    public function scheduleOnce(int $seconds = 5, bool $force = false): self
    {
        // $this->unschedule(true);

        if ($this->cron
            && (! $this->isEmpty() || $force)
            && ! wp_next_scheduled($this->cron)
        ) {
            wp_schedule_single_event(time() + $seconds, $this->cron, [], true);
        }

        return $this;
    }

    public function schedule(bool $force = false): self
    {
        if ($this->cron
            && (! $this->isEmpty() || $force)
            && ! wp_next_scheduled($this->cron)) {
            wp_schedule_event(time(), 'one_minute', $this->cron);
        }

        return $this;
    }

    public function unschedule(bool $force = false): self
    {
        if ($this->cron
            && ($this->isEmpty() || $force)
        ) {
            $timestamp = wp_next_scheduled($this->cron);
            wp_unschedule_event($timestamp, $this->cron);
        }

        return $this;
    }

    /**
     * @return int
     *             1  -> The migration was processed and completed.
     *             2  -> The migration was processed but still pending.
     *             4  -> There are no migrations available.
     *             8  -> The migration is already being processed.
     *             16 -> The migration is not current.
     */
    public function migrate(string $tag, string $version, callable $fn): int
    {
        if ($this->isEmpty()) {
            return 4;
        }

        $migration = $this->bottom();

        if ($migration->tag !== $tag
            || $migration->version !== $version
        ) {
            return 16;
        }

        if ($migration->status == 'working') {
            $seconds = time() - $migration->timestamp;

            if ($seconds <= (60 * 5)) {
                return 8;
            }
        }

        $this->patch('working')->persist();

        $value = $fn($migration->payload);

        if (is_null($value)) {
            $this->remove()->persist();

            return 1;
        }

        $this->patch('pending', $value)->persist();

        return 2;
    }
}
