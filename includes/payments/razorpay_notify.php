<?php
require_once('../../common/lib.php');
require_once('../../common/define.php');

if(isset($_POST['order_id']) && isset($_POST['razorpay_payment_id'])){

    $id_booking = $_POST['order_id'];
    $txn_id = $_POST['razorpay_payment_id'];
    $payment_amount = $_POST['amount'];
    
    $result_booking = $pms_db->query('SELECT * FROM pm_booking WHERE id = '.$id_booking.' AND status = 1 AND (trans IS NULL OR trans = \'\')');
    if($result_booking !== false && $pms_db->last_row_count() > 0){
        
        $row = $result_booking->fetch();

		$expected_amount = (PMS_ENABLE_DOWN_PAYMENT == 1) ? $row['down_payment'] : $row['total'];
						
        if($payment_amount == $expected_amount){
            
			$data = array();
            $data['id'] = $id_booking;
            $data['status'] = 4;
			$data['paid'] = $payment_amount;
			$data['balance'] = $row['total']-$payment_amount;
            
            $result_booking = pms_db_prepareUpdate($pms_db, 'pm_booking', $data);
            if($result_booking->execute() !== false){
                            
				$data = array();
				$data['id'] = null;
				$data['id_booking'] = $id_booking;
				$data['date'] = time();
				$data['trans'] = $txn_id;
				$data['method'] = 'razorpay';
				$data['amount'] = $payment_amount;
				
				$result_payment = pms_db_prepareInsert($pms_db, 'pm_booking_payment', $data);
				$result_payment->execute();
                
                $service_content = '';
                $result_service = $pms_db->query('SELECT * FROM pm_booking_service WHERE id_booking = '.$id_booking);
                if($result_service !== false && $pms_db->last_row_count() > 0){
                    foreach($result_service as $service)
                        $service_content .= $service['title'].' x '.$service['qty'].' : '.pms_formatPrice($service['amount']*PMS_CURRENCY_RATE).' '.$pms_texts['INCL_VAT'].'<br>';
                }
                
                $room_content = '';
                $result_room = $pms_db->query('SELECT * FROM pm_booking_room WHERE id_booking = '.$id_booking);
                if($result_room !== false && $pms_db->last_row_count() > 0){
                    foreach($result_room as $room){
                        $room_content .= '<p><b>'.$room['title'].'</b><br>
                        '.($room['adults']+$room['children']).' '.pms_getAltText($pms_texts['PERSON'], $pms_texts['PERSONS'], ($room['adults']+$room['children'])).': ';
                        if($room['adults'] > 0) $room_content .= $room['adults'].' '.pms_getAltText($pms_texts['ADULT'], $pms_texts['ADULTS'], $room['adults']).' ';
                        if($room['children'] > 0){
                            $room_content .= $room['children'].' '.pms_getAltText($pms_texts['CHILD'], $pms_texts['CHILDREN'], $room['children']).' ';
                            if(isset($room['child_age'])){
                                $room_content .= '('.implode(' '.$pms_texts['YO'].', ', $room['child_age']).' '.$pms_texts['YO'].')';
                            }
                        }
                        $room_content .= '<br>'.$pms_texts['PRICE'].' : '.pms_formatPrice($room['amount']*PMS_CURRENCY_RATE).'</p>';
                    }
                }
                
                $activity_content = '';
                $result_activity = $pms_db->query('SELECT * FROM pm_booking_activity WHERE id_booking = '.$id_booking);
                if($result_activity !== false && $pms_db->last_row_count() > 0){
                    foreach($result_activity as $activity){
                        $activity_content .= '<p><b>'.$activity['title'].'</b> - '.$activity['duration'].' - '.strftime(PMS_DATE_FORMAT.' '.PMS_TIME_FORMAT, $activity['date']).'<br>
                        '.($activity['adults']+$activity['children']).' '.pms_getAltText($pms_texts['PERSON'], $pms_texts['PERSONS'], ($activity['adults']+$activity['children'])).': ';
                        if($activity['adults'] > 0) $activity_content .= $activity['adults'].' '.pms_getAltText($pms_texts['ADULT'], $pms_texts['ADULTS'], $activity['adults']).' ';
                        if($activity['children'] > 0) $activity_content .= $activity['children'].' '.pms_getAltText($pms_texts['CHILD'], $pms_texts['CHILDREN'], $activity['children']).' ';
                        $activity_content .= $pms_texts['PRICE'].' : '.pms_formatPrice($activity['amount']*PMS_CURRENCY_RATE).'</p>';
                    }
                }
                
                $tax_content = '';
                $result_tax = $pms_db->query('SELECT * FROM pm_booking_tax WHERE id_booking = '.$id_booking);
                if($result_tax !== false && $pms_db->last_row_count() > 0){
                    foreach($result_tax as $tax){
                        $tax_content .= $tax['name'].': '.pms_formatPrice($tax['amount']*PMS_CURRENCY_RATE).'<br>';
                    }
                }
                
                $mail = pms_getMail($pms_db, 'BOOKING_CONFIRMATION', array(
                    '{firstname}' => $row['firstname'],
                    '{lastname}' => $row['lastname'],
                    '{company}' => $row['company'],
                    '{address}' => $row['address'],
                    '{postcode}' => $row['postcode'],
                    '{city}' => $row['city'],
                    '{country}' => $row['country'],
                    '{phone}' => $row['phone'],
                    '{mobile}' => $row['mobile'],
                    '{email}' => $row['email'],
                    '{Check_in}' => gmstrftime(PMS_DATE_FORMAT, $row['from_date']),
                    '{Check_out}' => gmstrftime(PMS_DATE_FORMAT, $row['to_date']),
                    '{num_nights}' => $row['nights'],
                    '{num_guests}' => ($row['adults']+$row['children']),
                    '{num_adults}' => $row['adults'],
                    '{num_children}' => $row['children'],
                    '{rooms}' => $room_content,
                    '{extra_services}' => $service_content,
                    '{activities}' => $activity_content,
                    '{comments}' => nl2br($row['comments']),
                    '{tourist_tax}' => pms_formatPrice($row['tourist_tax']*PMS_CURRENCY_RATE),
                    '{discount}' => '- '.pms_formatPrice($row['discount']*PMS_CURRENCY_RATE),
                    '{taxes}' => $tax_content,
                    '{down_payment}' => pms_formatPrice($row['down_payment']*PMS_CURRENCY_RATE),
                    '{total}' => pms_formatPrice($row['total']*PMS_CURRENCY_RATE),
                    '{payment_notice}' => ''
                ));
                
                if($mail !== false){
                    $hotel_owners = array();
                    $result_owner = $pms_db->query('SELECT * FROM pm_user WHERE id IN ('.$row['users'].')');
                    if($result_owner !== false){
                        foreach($result_owner as $owner){
                            if($owner['email'] != PMS_EMAIL)
                                pms_sendMail($owner['email'], $owner['firstname'], $mail['subject'], $mail['content'], $_SESSION['book']['email'], $_SESSION['book']['firstname'].' '.$_SESSION['book']['lastname']);
                        }
                    }
                    pms_sendMail(PMS_EMAIL, PMS_OWNER, $mail['subject'], $mail['content'], $row['email'], $row['firstname'].' '.$row['lastname']);
                    pms_sendMail($row['email'], $row['firstname'].' '.$row['lastname'], $mail['subject'], $mail['content']);
                }
				unset($_SESSION['book']);
				header('Location: '.DOCBASE.$pms_sys_pages['booking']['alias'].'?action=confirm');// Payment has been authorised
				exit();
            }
        }
    }
}
