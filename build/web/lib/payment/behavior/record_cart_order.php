<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: record_cart_order.php 44444 2013-01-05 21:24:24Z changi67 $

function payment_behavior_record_cart_order( $items = array() )
{
	global $tikilib;

	if (!count($items)) {
		return false;
	}

	$mid = " WHERE `itemId` IN (" . implode(",", array_fill(0, count($items), '?')) . ")";
	$query = "UPDATE `tiki_tracker_items` SET `status` = 'o'" . $mid;
	$tikilib->query($query, $items);
	return true;	
}

