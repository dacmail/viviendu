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

use Exception;
use SplStack;

class Stack extends SplStack
{
    /** @var callable */
    protected $store;

    /** @var string */
    protected $storeKey;

    public function __construct(callable $store, string $storeKey)
    {
        $this->store = $store;
        $this->storeKey = $storeKey;

        if ($serialized = $store($storeKey)) {
            try {
                $this->unserialize($serialized);
            } catch (Exception $e) {
                // Silently ignore.
            }
        }
    }

    public function persist(): void
    {
        ($this->store)([$this->storeKey => $this->serialize()]);
    }
}
