<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Matomo\Plugins\QueuedTracking\tests\Framework\Mock;

use Matomo\Tracker\Request;

class Tracker extends \Matomo\Tests\Framework\Mock\Tracker
{
    public function trackRequest(Request $request)
    {
        $allParams = $request->getRawParams();
        if (!empty($allParams['forceThrow'])) {
            throw new ForcedException("forced exception");
        }

        return parent::trackRequest($request);
    }
}
