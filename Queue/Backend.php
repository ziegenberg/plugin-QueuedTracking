<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Matomo\Plugins\QueuedTracking\Queue;

use Matomo\Concurrency\LockBackend;

/**
 * Interface for queue backends.
 */
interface Backend extends LockBackend
{
    public function appendValuesToList($key, $values);

    public function getFirstXValuesFromList($key, $numValues);

    public function removeFirstXValuesFromList($key, $numValues);

    public function getNumValuesInList($key);

    public function setIfNotExists($key, $value, $ttlInSeconds);

    public function deleteIfKeyHasValue($key, $value);

    public function hasAtLeastXRequestsQueued($key, $numValuesRequired);

    public function expireIfKeyHasValue($key, $value, $ttlInSeconds);

    public function get($key);

    public function getKeysMatchingPattern($pattern);
}
