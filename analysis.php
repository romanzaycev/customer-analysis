<?php

/**
 * Includes
 */
require_once __DIR__ . '/CustomerAnalysis/Db.php';
require_once __DIR__ . '/CustomerAnalysis/Segmentation.php';

/**
 * Defines
 */
define('ORDER_STATUS_DELIVERED', 5);
define('SEGMENTS_COUNT', 3);

/**
 * Namespaces
 */
use CustomerAnalysis\Segmentation;
use CustomerAnalysis\Db;

$db = Db::getInstance();

$records = $db->getOrders(ORDER_STATUS_DELIVERED);

/**
 * New segmentation builder with 'totalAmount' field
 */
$requencySegmentation = new Segmentation($records);
$requencySegmentation->setField('totalAmount');
$requencySegmentation->setSegmentsCount(SEGMENTS_COUNT);
$records = $requencySegmentation->calculate('R'); // R - Data code, passed to result array
$requencySegmentation = null; // GC

/**
 * New segmentation builder with 'totalOrders' field
 */
$monetarySegmentation = new Segmentation($records);
$monetarySegmentation->setField('totalOrders');
$monetarySegmentation->setSegmentsCount(SEGMENTS_COUNT);
$records = $monetarySegmentation->calculate('M');

/**
 * Save calculated analytic data
 */
foreach ($records as $record) {
    $db->saveAnalysisData(
        $record['customerID'],
        [
            'R' => $record['R'],
            'M' => $record['M']
        ]
    );
}

/**
 * Done.
 */

// EOF analysis.php