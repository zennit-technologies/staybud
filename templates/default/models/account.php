<?php
$msg_error = "";
$msg_success = "";
$field_notice = array();

if(isset($_GET['view'])) $view = $_GET['view'];
else $view = "account";

$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0;

$result_user = $pms_db->query("SELECT * FROM pm_user WHERE id = ".$pms_db->quote($user_id)." AND checked = 1");
if($result_user !== false && $pms_db->last_row_count() > 0){
    $row = $result_user->fetch();
    
    $firstname = $row['firstname'];
    $lastname = $row['lastname'];
    $login = $row['login'];
    $email = $row['email'];
    $address = $row['address'];
    $postcode = $row['postcode'];
    $city = $row['city'];
    $company = $row['company'];
    $country = $row['country'];
    $mobile = $row['mobile'];
    $phone = $row['phone'];
}else{
    $firstname = "";
    $lastname = "";
    $login = "";
    $email = "";
    $address = "";
    $postcode = "";
    $city = "";
    $company = "";
    $country = "";
    $mobile = "";
    $phone = ""; 
}

require(pms_getFromTemplate("common/header.php", false)); ?>

<section id="page">
    
    <?php include(pms_getFromTemplate("common/page_header.php", false)); ?>
    
    <div id="content" class="pt30 pb30">
        <div class="container">
            
            <?php
            if($user_id > 0){ ?>
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="pagination pull-right">
                            <li<?php if($view == "account") echo " class=\"active\""; ?>><a href="?view=account"><?php echo $pms_texts['MY_ACCOUNT']; ?></a></li>
                            <li<?php if($view == "booking-history") echo " class=\"active\""; ?>><a href="?view=booking-history"><?php echo $pms_texts['BOOKING_HISTORY']; ?></a></li>
                        </ul>
                    </div>
                </div>
                <?php
            }
            
            $hotel_id = 0;
            $result_hotel = $pms_db->prepare("SELECT title FROM pm_hotel WHERE id = :hotel_id AND lang = ".PMS_LANG_ID);
            $result_hotel->bindParam(':hotel_id', $hotel_id);
            
            if($view == "booking-history" && $user_id > 0){ ?>
                <fieldset>
                    <legend><?php echo $pms_texts['BOOKING_HISTORY']; ?></legend>
                    <?php
                    $result_booking = $pms_db->query("SELECT * FROM pm_booking WHERE id_user = ".$pms_db->quote($user_id)." ORDER BY add_date DESC");
                    if($result_booking !== false && $pms_db->last_row_count() > 0){ ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?php echo $pms_texts['BOOKING_DATE']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['HOTEL']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['FROM_DATE']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['TO_DATE']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['NIGHTS']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['ADULTS']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['CHILDREN']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['TOTAL']; ?></th>
                                        <th class="text-center"><?php echo $pms_texts['PAYMENT']; ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach($result_booking as $i => $row){
                                        
                                        $hotel_id = $row['id_hotel'];
                                        $hotel = '';
                                        if($result_hotel->execute() !== false && $pms_db->last_row_count() > 0)
                                            $hotel = $result_hotel->fetchColumn(0); ?>
                                            
                                        <tr>
                                            <td><?php echo gmstrftime(PMS_DATE_FORMAT." ".PMS_TIME_FORMAT, $row['add_date']); ?></td>
                                            <td><?php echo $hotel; ?></td>
                                            <td><?php if(!is_null($row['from_date'])) echo gmstrftime(PMS_DATE_FORMAT, $row['from_date']); ?></td>
                                            <td><?php if(!is_null($row['to_date'])) echo gmstrftime(PMS_DATE_FORMAT, $row['to_date']); ?></td>
                                            <td class="text-center"><?php echo $row['nights']; ?></td>
                                            <td class="text-center"><?php echo $row['adults']; ?></td>
                                            <td class="text-center"><?php echo $row['children']; ?></td>
                                            <td class="text-right"><?php echo pms_formatPrice($row['total']*PMS_CURRENCY_RATE); ?></td>
                                            <td class="text-left">
                                                <?php
                                                switch($row['status']){
                                                    case 1: echo $pms_texts['AWAITING']; break;
                                                    case 2: echo $pms_texts['CANCELLED']; break;
                                                    case 3: echo $pms_texts['REJECTED_PAYMENT']; break;
                                                    case 4: echo $pms_texts['PAYED']; break;
                                                    default: echo $pms_texts['AWAITING']; break;
                                                }
                                                if(!empty($row['down_payment']) && $row['down_payment'] < $row['total']) echo " (".pms_formatPrice($row['down_payment']*PMS_CURRENCY_RATE).")" ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?php echo pms_getFromTemplate("common/booking-popup.php"); ?>" data-params="id=<?php echo $row['id']; ?>" class="ajax-popup-link"><i class="fa fa-search"></i></a>
                                            </td>
                                        </tr>
                                        <?php
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    }else{ ?>
                        <p class="lead text-center text-muted"><?php echo $pms_texts['NO_BOOKING_YET']; ?></p>
                        <?php
                    } ?>
                </fieldset>
                <?php
            }else{
                if($user_id == 0){ ?>
                    <fieldset>
                        <legend><?php echo $pms_texts['ALREADY_HAVE_ACCOUNT']; ?></legend>
                        <div class="row">
                            <form method="post" action="<?php echo DOCBASE.$page['alias']; ?>" role="form" class="ajax-form">
                                <div class="alert alert-success" style="display:none;"></div>
                                <div class="alert alert-danger" style="display:none;"></div>
                                <div class="col-sm-6">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label"><?php echo $pms_texts['USERNAME']; ?></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" name="user" value="<?php echo $login; ?>"/>
                                            <div class="field-notice" rel="user"></div>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label"><?php echo $pms_texts['PASSWORD']; ?></label>
                                        <div class="col-lg-9">
                                            <input type="password" class="form-control" name="pass" value=""/>
                                            <div class="field-notice" rel="pass"></div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-7 col-lg-4 col-lg-offset-3 text-left">
                                            <a class="popup-modal open-pass-form" href="#user-popup"><?php echo $pms_texts['FORGOTTEN_PASSWORD']; ?></a>
                                        </div>
                                        <div class="col-sm-5 text-right">
                                            <a href="#" class="btn btn-primary sendAjaxForm" data-action="<?php echo pms_getFromTemplate("common/register/login.php"); ?>" data-refresh="true"><i class="fa fa-power-off"></i> <?php echo $pms_texts['LOG_IN']; ?></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </fieldset>
                    <?php
                } ?>
                <fieldset>
                    <legend><?php echo ($user_id == 0) ? $pms_texts['I_SIGN_UP'] : $pms_texts['MY_ACCOUNT']; ?></legend>
                    <div class="row">
                        <form method="post" action="<?php echo DOCBASE.$page['alias']; ?>" role="form" class="ajax-form">
                            <div class="alert alert-success" style="display:none;"></div>
                            <div class="alert alert-danger" style="display:none;"></div>
                            <input type="hidden" name="signup_type" value="complete">
                            <div class="col-sm-6">
								<div class="row form-group">
									<div class="col-lg-9 col-lg-offset-3">
										<input type="radio" name="hotel_owner" id="hotel_owner_1" value="1"> <label for="hotel_owner_1"><?php echo $pms_texts['I_AM_HOTEL_OWNER']; ?></label> &nbsp;
										<input type="radio" name="hotel_owner" id="hotel_owner_0" value="0"> <label for="hotel_owner_0"><?php echo $pms_texts['I_AM_TRAVELER']; ?></label>
									</div>
								</div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['FIRSTNAME']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="firstname" value="<?php echo $firstname; ?>"/>
                                        <div class="field-notice" rel="firstname"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['LASTNAME']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="lastname" value="<?php echo $lastname; ?>"/>
                                        <div class="field-notice" rel="lastname"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['USERNAME']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="username" value="<?php echo $login; ?>"/>
                                        <div class="field-notice" rel="username"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['EMAIL']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="email" value="<?php echo $email; ?>"/>
                                        <div class="field-notice" rel="email"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo ($user_id > 0) ? $pms_texts['NEW_PASSWORD'] : $pms_texts['PASSWORD']; ?></label>
                                    <div class="col-lg-9">
                                        <input type="password" class="form-control" name="password" value=""/>
                                        <div class="field-notice" rel="password"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['PASSWORD_CONFIRM']; ?></label>
                                    <div class="col-lg-9">
                                        <input type="password" class="form-control" name="password_confirm" value=""/>
                                        <div class="field-notice" rel="password_confirm"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['ADDRESS']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="address" value="<?php echo $address; ?>"/>
                                        <div class="field-notice" rel="address"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['POSTCODE']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="postcode" value="<?php echo $postcode; ?>"/>
                                        <div class="field-notice" rel="postcode"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['CITY']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="city" value="<?php echo $city; ?>"/>
                                        <div class="field-notice" rel="city"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['COUNTRY']; ?> *</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="country">
                                            <option value="0">-</option>
                                            <?php
                                            $result_country = $pms_db->query("SELECT * FROM pm_country");
                                            if($result_country !== false){
                                                foreach($result_country as $i => $row){
                                                    $id_country = $row['id'];
                                                    $country_name = $row['name'];
                                                    $selected = ($country == $country_name) ? " selected=\"selected\"" : "";
                                                    
                                                    echo "<option value=\"".$country_name."\"".$selected.">".$country_name."</option>";
                                                }
                                            } ?>
                                        </select>
                                        <div class="field-notice" rel="country"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['PHONE']; ?> *</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="phone" value="<?php echo $phone; ?>"/>
                                        <div class="field-notice" rel="phone"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['MOBILE']; ?></label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="mobile" value="<?php echo $mobile; ?>"/>
                                        <div class="field-notice" rel="mobile"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"><?php echo $pms_texts['COMPANY']; ?></label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" name="company" value="<?php echo $company; ?>"/>
                                        <div class="field-notice" rel="company"></div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <label class="col-lg-3 control-label"></label>
                                    <div class="col-lg-9">
                                        <input type="checkbox" name="privacy_agreement" value="1"> <?php echo $pms_texts['PRIVACY_POLICY_AGREEMENT']; ?>
                                        <div class="field-notice" rel="privacy_agreement"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-right">
                                        <i class="text-muted"> * <?php echo $pms_texts['REQUIRED_FIELD']; ?> </i><br>
                                        <a href="#" class="btn btn-primary sendAjaxForm" data-action="<?php echo pms_getFromTemplate("common/register/signup.php"); ?>"<?php if($user_id == 0) echo " data-clear=\"true\""; ?>><i class="fa fa-power-off"></i> <?php echo ($user_id > 0) ? $pms_texts['EDIT'] : $pms_texts['SIGN_UP']; ?></a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>
                </div>
                <?php
            } ?>
        </div>
    </div>
</section>
