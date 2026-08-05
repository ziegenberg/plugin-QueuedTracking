<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Matomo\Plugins\QueuedTracking\Queue;

use Matomo\Container\StaticContainer;
use Matomo\Plugins\QueuedTracking\Queue;
use Matomo\Plugins\QueuedTracking\SystemSettings;
use Exception;

/**
 * This class represents a page view, tracking URL, page title and generation time.
 */
class Factory
{
    public static function makeBackend()
    {
        $settings = self::getSettings();

        return self::makeBackendFromSettings($settings);
    }

    public static function makeQueueManager(Backend $backend)
    {
        $settings = self::getSettings();

        $lock    = self::makeLock($backend);
        $manager = new Manager($backend, $lock);
        $manager->setNumberOfAvailableQueues($settings->numQueueWorkers->getValue());
        $manager->setNumberOfRequestsToProcessAtSameTime($settings->numRequestsToProcess->getValue());

        return $manager;
    }

    public static function makeLock(Backend $backend)
    {
        return new Lock($backend);
    }

    /**
     * @return \Matomo\Plugins\QueuedTracking\SystemSettings
     */
    public static function getSettings()
    {
        return StaticContainer::get('Matomo\Plugins\QueuedTracking\SystemSettings');
    }

    public static function makeBackendFromSettings(SystemSettings $settings)
    {
        if ($settings->isMysqlBackend()) {
            return new Queue\Backend\MySQL();
        }

        $host     = $settings->redisHost->getValue();
        $port     = $settings->redisPort->getValue();
        $timeout  = $settings->redisTimeout->getValue();
        $password = $settings->redisPassword->getValue();
        $database = $settings->redisDatabase->getValue();

        if ($settings->isUsingSentinelBackend()) {
            $masterName = $settings->getSentinelMasterName();
            if (empty($masterName)) {
                throw new Exception('You must configure a sentinel master name via `sentinel_master_name="mymaster"` to use the sentinel backend');
            } else {
                $redis = new Queue\Backend\Sentinel();
                $redis->setSentinelMasterName($masterName);
                $redis->setDatabase($database);
                if (!empty($settings->getUsePasswordForSentinelInstances())) {
                    $redis->setUsePasswordForSentinelInstances(true);
                }
            }
        } elseif ($settings->isUsingClusterBackend()) {
            $redis = new Queue\Backend\RedisCluster();
        } else {
            $redis = new Queue\Backend\Redis();
            $redis->setDatabase($database);
        }

        $redis->setConfig($host, $port, $timeout, $password);
        return $redis;
    }
}
