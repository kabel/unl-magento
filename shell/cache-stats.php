<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Magento Cache Shell Script
 *
 */
class Unl_Shell_CacheStats extends Mage_Shell_Abstract
{
    protected $stats;

    /**
     * Convert size to human view
     *
     * @param int $number
     * @return string
     */
    protected function _humanSize($number)
    {
        if ($number < 1000) {
            return sprintf('%d b', $number);
        } else if ($number >= 1000 && $number < 1000000) {
            return sprintf('%.2fKb', $number / 1000);
        } else if ($number >= 1000000 && $number < 1000000000) {
            return sprintf('%.2fMb', $number / 1000000);
        } else {
            return sprintf('%.2fGb', $number / 1000000000);
        }
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('clean')) {
            Mage::app()->getCache()->getBackend()->clean(Zend_Cache::CLEANING_MODE_OLD);
            echo "Cache cleaned\n";
        } else if ($this->getArg('status')) {
            $backend = Mage::app()->getCache()->getBackend();

            if (!($backend instanceof Zend_Cache_Backend_ExtendedInterface)) {
                echo "Cache stats are not available\n";
                exit;
            }

            $limit = 20;
            if ($this->getArg('limit')) {
                $limit = intval($this->getArg('limit'));
            }

            foreach (array('total size', 'avg size', 'count') as $key) {
                $this->printStats($key, $limit);
            }

        } else {
            echo $this->usageHelp();
        }
    }

    protected function getStats()
    {
        $backend = Mage::app()->getCache()->getBackend();
        $tagStats = array();

        foreach ($backend->getTags() as $tag) {
            if (preg_match('/^\w{3}_MAGE$/', $tag)) {
                continue;
            }

            $ids = $backend->getIdsMatchingTags(array($tag));

            $tagSizes = array();
            $missing = 0;

            foreach ($ids as $id) {
                $data = $backend->load($id);
                $size = strlen($data);
                if ($size) {
                    $tagSizes[] = $size;
                } else {
                    $missing++;
                }
            }

            if ($tagSizes) {
                $tagCount = count($tagSizes);
                $tagSum = array_sum($tagSizes);
                $tagStats[$tag] = array(
                    'count' => $tagCount,
                    'min' => min($tagSizes),
                    'max' => max($tagSizes),
                    'avg size' => $tagSum / $tagCount,
                    'total size' => $tagSum,
                    'missing' => $missing,
                );
            }
        }

        $this->stats = $tagStats;
    }

    protected function printStats($key, $limit)
    {
        if (!$this->stats) {
            $this->getStats();
        }
        $data = $this->stats;

        echo "Top $limit tags by ".ucwords($key)."\n";
        echo "------------------------------------------------------------------------------------\n";

        $sort = array();
        foreach ($data as $tag => $stats) {
            $sort[$tag] = $stats[$key];
        }
        array_multisort($sort, SORT_DESC, $data);

        $i = 0;
        $fmt = "%-40s| %-8s| %-15s| %-15s\n";
        printf($fmt, 'Tag', 'Count', 'Avg Size', 'Total Size');

        foreach ($data as $tag => $stats) {
            $tag = substr($tag, 4);
            if (++$i > $limit) break;
            $avg = $this->_humanSize($stats['avg size']);
            $total = $this->_humanSize($stats['total size']);
            printf($fmt, $tag, $stats['count'], $avg, $total);
        }

        echo "\n";
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f cache-stats.php -- [options]
        php -f cache-stats.php -- clean

  clean             Clean old cached objects
  status            Display statistics per log tables
  help              This help

USAGE;
    }
}

$shell = new Unl_Shell_CacheStats();
$shell->run();
