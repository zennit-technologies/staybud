<?php
if($article_alias == '') pms_err404();

$result = $pms_db->query('SELECT * FROM pm_hotel WHERE checked = 1 AND lang = '.PMS_LANG_ID.' AND alias = '.$pms_db->quote($article_alias));
if($result !== false && $pms_db->last_row_count() == 1){
    
    $hotel = $result->fetch(PDO::FETCH_ASSOC);
    
    $hotel_id = $hotel['id'];
    $pms_article_id = $hotel_id;
    $title_tag = $hotel['title'].' - '.$title_tag;
    $page_title = $hotel['title'];
    $page_subtitle = '';
    $page_alias = $pms_pages[$pms_page_id]['alias'].'/'.pms_text_format($hotel['alias']);
    
    $result_hotel_file = $pms_db->query('SELECT * FROM pm_hotel_file WHERE id_item = '.$hotel_id.' AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
    if($result_hotel_file !== false && $pms_db->last_row_count() > 0){
        
        $row = $result_hotel_file->fetch();
        
        $file_id = $row['id'];
        $filename = $row['file'];
        
        if(is_file(SYSBASE.'medias/hotel/medium/'.$file_id.'/'.$filename))
            $page_img = pms_getUrl(true).DOCBASE.'medias/hotel/medium/'.$file_id.'/'.$filename;
    }
    
}else pms_err404();

pms_check_URI(DOCBASE.$page_alias);

/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */

$pms_javascripts[] = DOCBASE.'js/plugins/sharrre/jquery.sharrre.min.js';

$pms_javascripts[] = DOCBASE.'js/plugins/jquery.event.calendar/js/jquery.event.calendar.js';
$pms_javascripts[] = DOCBASE.'js/plugins/jquery.event.calendar/js/languages/jquery.event.calendar.'.PMS_LANG_TAG.'.js';
$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/jquery.event.calendar/css/jquery.event.calendar.css', 'media' => 'all');

$pms_stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', 'media' => 'all');
$pms_stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', 'media' => 'all');
$pms_javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js';

$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/isotope/css/style.css', 'media' => 'all');
$pms_javascripts[] = '//unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js';

$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/lazyloader/lazyloader.css', 'media' => 'all');
$pms_javascripts[] = DOCBASE.'js/plugins/lazyloader/lazyloader.js';

$pms_stylesheets[] = array('file' => '//cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.0.7/css/star-rating.css', 'media' => 'all');
$pms_javascripts[] = '//cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.0.7/js/star-rating.js';

$pms_javascripts[] = DOCBASE.'js/plugins/live-search/jquery.liveSearch.js';

require(pms_getFromTemplate('common/send_comment.php', false));

require(pms_getFromTemplate('common/header.php', false)); ?>

<section id="page">
    
    <?php include(pms_getFromTemplate('common/page_header.php', false)); ?>
    
    <div id="content" class="pb30">
    
        <div id="search-page" class="mb30">
            <div class="container">
                <?php include(pms_getFromTemplate('common/search.php', false)); ?>
            </div>
        </div>
        
        <div class="container">
            <div class="alert alert-success" style="display:none;"></div>
            <div class="alert alert-danger" style="display:none;"></div>
        </div>
    
        <article class="container">
            <div class="row">
                <div class="col-md-8 mb20">
                    <div class="row mb10">
                        <div class="col-sm-8">
                            <h1 class="mb0">
                                <?php echo $hotel['title']; ?>
                                <small>
                                    <?php
                                    if(!empty($hotel['class'])){
                                        for($j = 0; $j < $hotel['class']; $j++) echo '<i class=\"fas fa-fw fa-star\"></i>';
                                    } ?>
                                </small>
                                <br><small><?php echo $hotel['subtitle']; ?></small>
                            </h1>
                            <?php
                            $result_rating = $pms_db->query('SELECT count(*) as count_rating, AVG(rating) as avg_rating FROM pm_comment WHERE item_type = \'hotel\' AND id_item = '.$hotel_id.' AND checked = 1 AND rating > 0 AND rating <= 5');
                            if($result_rating !== false && $pms_db->last_row_count() == 1){
                                $row = $result_rating->fetch();
                                $hotel_rating = $row['avg_rating'];
                                $count_rating = $row['count_rating'];
                                
                                if($hotel_rating > 0 && $hotel_rating <= 5){ ?>
                                
                                    <input type="hidden" class="rating pull-left" value="<?php echo $hotel_rating; ?>" data-rtl="<?php echo (PMS_RTL_DIR) ? true : false; ?>" data-size="xs" readonly="true" data-default-caption="<?php echo $count_rating.' '.$pms_texts['RATINGS']; ?>" data-show-caption="false" data-show-clear="false">
                                    <?php
                                }
                            } ?>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <div class="price text-primary">
                                <?php
                                $min_price = 0;
                                $result_rate = $pms_db->query('
                                    SELECT MIN(ra.price) as min_price
                                    FROM pm_rate as ra, pm_room as ro
                                    WHERE ro.id = id_room AND ro.id_hotel = '.$hotel_id);
                                if($result_rate !== false && $pms_db->last_row_count() > 0){
                                    $row = $result_rate->fetch();
                                    $price = $row['min_price'];
                                    if($price > 0) $min_price = $price;
                                }
                                if($min_price > 0){
                                    echo $pms_texts['FROM_PRICE']; ?>
                                    <span itemprop="priceRange">
                                        <?php echo pms_formatPrice($min_price*PMS_CURRENCY_RATE); ?>
                                    </span>
                                    / <?php echo $pms_texts['NIGHT'];
                                } ?>
                            </div>
                            <form action="<?php echo DOCBASE.$pms_sys_pages['booking']['alias']; ?>" method="post">
								<input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
								<button type="submit" name="check_availabilities" class="btn btn-success"><?php echo $pms_texts['BOOK_NOW']; ?></button>
							</form>
                        </div>
                    </div>
                    <div class="row mb10">
                        <div class="col-sm-12">
                            <?php
                            $result_facility = $pms_db->query('SELECT * FROM pm_facility WHERE lang = '.PMS_LANG_ID.' AND id IN('.$hotel['facilities'].') ORDER BY id',PDO::FETCH_ASSOC);
                            if($result_facility !== false && $pms_db->last_row_count() > 0){
                                foreach($result_facility as $i => $row){
                                    $facility_id 	= $row['id'];
                                    $facility_name  = $row['name'];
                                    
                                    $result_facility_file = $pms_db->query('SELECT * FROM pm_facility_file WHERE id_item = '.$facility_id.' AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1',PDO::FETCH_ASSOC);
                                    if($result_facility_file !== false && $pms_db->last_row_count() == 1){
                                        $row = $result_facility_file->fetch();
                                        
                                        $file_id 	= $row['id'];
                                        $filename 	= $row['file'];
                                        $label	 	= $row['label'];
                                        
                                        $realpath	= SYSBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                        $thumbpath	= DOCBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                            
                                        if(is_file($realpath)){ ?>
                                            <span class="facility-icon">
                                                <img alt="<?php echo $facility_name; ?>" title="<?php echo $facility_name; ?>" src="<?php echo $thumbpath; ?>" class="tips">
                                            </span>
                                            <?php
                                        }
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                    <div class="row mb10">
                        <div class="col-md-12">
                            <div class="owl-carousel owlWrapper" data-items="1" data-autoplay="true" data-dots="true" data-nav="false" data-rtl="<?php echo (PMS_RTL_DIR) ? 'true' : 'false'; ?>">
                                <?php
                                $result_hotel_file = $pms_db->query('SELECT * FROM pm_hotel_file WHERE id_item = '.$hotel_id.' AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank`');
                                if($result_hotel_file !== false){
                                    
                                    foreach($result_hotel_file as $i => $row){
                                    
                                        $file_id = $row['id'];
                                        $filename = $row['file'];
                                        $label = $row['label'];
                                        
                                        $realpath = SYSBASE.'medias/hotel/big/'.$file_id.'/'.$filename;
                                        $thumbpath = DOCBASE.'medias/hotel/big/'.$file_id.'/'.$filename;
                                        
                                        if(is_file($realpath)){ ?>
                                            <img alt="<?php echo $label; ?>" src="<?php echo $thumbpath; ?>" class="img-responsive" style="max-height:600px;"/>
                                            <?php
                                        }
                                    }
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mb10">
                        <div class="col-md-12" itemprop="description">
                            <?php
                            echo $hotel['descr'];
                            
                            $short_text = pms_strtrunc(pms_rip_tags($hotel['descr']), 100);
                            $site_url = pms_getUrl(); ?>
                           
                            <div id="twitter" data-url="<?php echo $site_url; ?>" data-text="<?php echo $short_text; ?>" data-title="Tweet"></div>
                            <div id="facebook" data-url="<?php echo $site_url; ?>" data-text="<?php echo $short_text; ?>" data-title="Like"></div>
                        </div>
                    </div>
                    <div class="row mt30">
                        <?php
                        $lz_offset = 1;
                        $lz_limit = 3;
                        $lz_pages = 0;
                        $num_records = 0;
                        $result = $pms_db->query('SELECT count(*) FROM pm_activity WHERE hotels REGEXP \'(^|,)'.$hotel_id.'(,|$)\' AND checked = 1 AND lang = '.PMS_LANG_ID);
                        if($result !== false){
                            $num_records = $result->fetchColumn(0);
                            $lz_pages = ceil($num_records/$lz_limit);
                        }
                        if($num_records > 0){ ?>
                            <h3><?php echo $pms_texts['FIND_ACTIVITIES_AND_TOURS']; ?></h3>
                            <div class="isotopeWrapper clearfix isotope lazy-wrapper" data-loader="<?php echo pms_getFromTemplate('common/get_activities.php'); ?>" data-mode="click" data-limit="<?php echo $lz_limit; ?>" data-pages="<?php echo $lz_pages; ?>" data-is_isotope="true" data-variables="page_id=<?php echo $pms_sys_pages['activities']['id']; ?>&page_alias=<?php echo $pms_sys_pages['activities']['alias']; ?>&hotel=<?php echo $hotel_id; ?>">
                                <?php include(pms_getFromTemplate('common/get_activities.php', false)); ?>
                            </div>
                            <?php
                        } ?>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            $nb_comments = 0;
                            $item_type = 'hotel';
                            $item_id = $hotel_id;
                            $allow_comment = PMS_ALLOW_COMMENTS;
                            $allow_rating = PMS_ALLOW_RATINGS;
                            if($allow_comment == 1){
                                $result_comment = $pms_db->query('SELECT * FROM pm_comment WHERE id_item = '.$item_id.' AND item_type = \''.$item_type.'\' AND checked = 1 ORDER BY add_date DESC');
                                if($result_comment !== false)
                                    $nb_comments = $pms_db->last_row_count();
                            }
                            include(pms_getFromTemplate('common/comments.php', false)); ?>
                        </div>
                    </div>
                </div>
                <aside class="col-md-4 mb20">
                    <div class="boxed">
                        <div itemscope itemtype="http://schema.org/Corporation">
                            <h3 itemprop="name"><?php echo $hotel['title']; ?></h3>
                            <address>
                                <p>
                                    <i class="fas fa-fw fa-map-marker"></i> <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><?php echo $hotel['address']; ?></span><br>
                                    <?php if($hotel['phone'] != '') : ?><i class="fas fa-fw fa-phone"></i> <span itemprop="telephone" dir="ltr"><?php echo $hotel['phone']; ?></span><br><?php endif; ?>
                                    <?php if($hotel['email'] != '') : ?><i class="fas fa-fw fa-envelope"></i> <a itemprop="email" dir="ltr" href="mailto:<?php echo $hotel['email']; ?>"><?php echo $hotel['email']; ?></a><br><?php endif; ?>
                                    <?php if($hotel['web'] != '') : ?><i class="fas fa-fw fa-globe"></i> <a dir="ltr" href="<?php echo strpos($hotel['web'], 'http') === false ? 'http://'.$hotel['web'] : $hotel['web']; ?>" target="_blank"><?php echo $hotel['web']; ?></a><?php endif; ?>
                                </p>
                            </address>
                        </div>
                        <script type="text/javascript">
                            var pms_locations = [
                                ['<?php echo addslashes($hotel['title']); ?>', '<?php echo addslashes($hotel['address']); ?>', '<?php echo $hotel['lat']; ?>', '<?php echo $hotel['lng']; ?>']
                            ];
                        </script>
                        <div id="mapWrapper" class="mb10" data-marker="<?php echo pms_getFromTemplate('images/marker.png'); ?>" data-api_key="<?php echo PMS_GMAPS_API_KEY; ?>"></div>
                        <?php
                        $id_facility = 0;
                        $result_facility_file = $pms_db->prepare('SELECT * FROM pm_facility_file WHERE id_item = :id_facility AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
                        $result_facility_file->bindParam(':id_facility', $id_facility);

                        $room_facilities = '0';
                        $result_facility = $pms_db->prepare('SELECT * FROM pm_facility WHERE lang = '.PMS_LANG_ID.' AND FIND_IN_SET(id, :room_facilities) ORDER BY `rank` LIMIT 8');
                        $result_facility->bindParam(':room_facilities', $room_facilities);
            
                        $id_room = 0;
                        $result_rate = $pms_db->prepare('SELECT MIN(price) as price FROM pm_rate WHERE id_room = :id_room');
                        $result_rate->bindParam(':id_room', $id_room);
                        
                        $result_room_file = $pms_db->prepare('SELECT * FROM pm_room_file WHERE id_item = :id_room AND checked = 1 AND lang = '.PMS_LANG_ID.' AND type = \'image\' AND file != \'\' ORDER BY `rank`');
                        $result_room_file->bindParam(':id_room', $id_room, PDO::PARAM_STR);
                
                        $result_room = $pms_db->query('SELECT * FROM pm_room WHERE id_hotel = '.$hotel_id.' AND checked = 1 AND lang = '.PMS_LANG_ID.' ORDER BY `rank`', PDO::FETCH_ASSOC);
                        if($result_room !== false && $pms_db->last_row_count() > 0){ ?>
                            <p class="widget-title"><?php echo $pms_texts['ROOMS']; ?></p>
                            
                            <?php
                            foreach($result_room as $i => $row){
                                $id_room = $row['id'];
                                $room_title = $row['title'];
                                $room_subtitle = $row['subtitle'];
                                $room_descr = $row['descr'];
                                $room_alias = $row['alias'];
                                $room_facilities = $row['facilities'];
                                $max_people = $row['max_people'];
                                $room_price = $row['price']; ?>
                                
                                <a class="popup-modal" href="#room-<?php echo $id_room; ?>">
                                    <div class="row">
                                        <div class="col-xs-4 mb20">
                                            <?php
                                            $result_room_file->execute();
                                            if($result_room_file !== false && $pms_db->last_row_count() > 0){
                                                $row = $result_room_file->fetch(PDO::FETCH_ASSOC);
                                                
                                                $file_id = $row['id'];
                                                $filename = $row['file'];
                                                $label = $row['label'];
                                                
                                                $realpath = SYSBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                                $thumbpath = DOCBASE.'medias/room/small/'.$file_id.'/'.$filename;
                                                    
                                                if(is_file($realpath)){ ?>
                                                    <div class="img-container sm">
                                                        <img alt="" src="<?php echo $thumbpath; ?>">
                                                    </div>
                                                    <?php
                                                }
                                            } ?>
                                        </div>
                                        <div class="col-xs-8">
                                            <h3 class="mb0"><?php echo $room_title; ?></h3>
                                            <h4 class="mb0"><?php echo $room_subtitle; ?></h4>
                                            <?php
                                            $min_price = $room_price;
                                            if($result_rate->execute() !== false && $pms_db->last_row_count() > 0){
                                                $row = $result_rate->fetch();
                                                $price = $row['price'];
                                                if($price > 0) $min_price = $price;
                                            } ?>
                                            <div class="price text-primary">
                                                <?php echo $pms_texts['FROM_PRICE']; ?>
                                                <span itemprop="priceRange">
                                                    <?php echo pms_formatPrice($min_price*PMS_CURRENCY_RATE); ?>
                                                </span>
                                                / <?php echo $pms_texts['NIGHT']; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <div id="room-<?php echo $id_room; ?>" class="white-popup-block mfp-hide">
                                    <div class="fluid-container">
                                        <div class="row">
                                            <div class="col-xs-12 mb20">
                                                <div class="owl-carousel" data-items="1" data-autoplay="true" data-dots="true" data-nav="false" data-rtl="<?php echo (PMS_RTL_DIR) ? 'true' : 'false'; ?>">
                                                    <?php
                                                    $result_room_file->execute();
                                                    if($result_room_file !== false){
                                                        foreach($result_room_file as $i => $row){
                                    
                                                            $file_id = $row['id'];
                                                            $filename = $row['file'];
                                                            $label = $row['label'];
                                                            
                                                            $realpath = SYSBASE.'medias/room/medium/'.$file_id.'/'.$filename;
                                                            $thumbpath = DOCBASE.'medias/room/medium/'.$file_id.'/'.$filename;
                                                            
                                                            if(is_file($realpath)){ ?>
                                                                <div><img alt="<?php echo $label; ?>" src="<?php echo $thumbpath; ?>" class="img-responsive" style="max-height:600px;"></div>
                                                                <?php
                                                            }
                                                        }
                                                    } ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <h3 class="mb0"><?php echo $room_title; ?></h3>
                                                <h4 class="mb0"><?php echo $room_subtitle; ?></h4>
                                            </div>
                                            <div class="col-sm-4 text-right">
                                                <?php
                                                $min_price = $room_price;
                                                if($result_rate->execute() !== false && $pms_db->last_row_count() > 0){
                                                    $row = $result_rate->fetch();
                                                    $price = $row['price'];
                                                    if($price > 0) $min_price = $price;
                                                }
                                                $type = $pms_texts['NIGHT']; ?>
                                                <div class="price text-primary">
                                                    <?php echo $pms_texts['FROM_PRICE']; ?>
                                                    <span itemprop="priceRange">
                                                        <?php echo pms_formatPrice($min_price*PMS_CURRENCY_RATE); ?>
                                                    </span>
                                                    / <?php echo $type; ?>
                                                </div>
                                                <p>
                                                    <?php echo $pms_texts['CAPACITY']; ?> : <i class="fas fa-fw fa-male"></i>x<?php echo $max_people; ?>
                                                </p>
                                            </div>
                                            <div class="col-xs-12">
                                                <div class="clearfix mb5">
                                                    <?php
                                                    $result_facility->execute();
                                                    if($result_facility !== false && $pms_db->last_row_count() > 0){
                                                        foreach($result_facility as $row){
                                                            $id_facility = $row['id'];
                                                            $facility_name = $row['name'];
                                                            
                                                            $result_facility_file->execute();
                                                            if($result_facility_file !== false && $pms_db->last_row_count() == 1){
                                                                $row = $result_facility_file->fetch();
                                                                
                                                                $file_id = $row['id'];
                                                                $filename = $row['file'];
                                                                $label = $row['label'];
                                                                
                                                                $realpath = SYSBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                                                $thumbpath = DOCBASE.'medias/facility/big/'.$file_id.'/'.$filename;
                                                                    
                                                                if(is_file($realpath)){ ?>
                                                                    <span class="facility-icon">
                                                                        <img alt="<?php echo $facility_name; ?>" title="<?php echo $facility_name; ?>" src="<?php echo $thumbpath; ?>" class="tips">
                                                                    </span>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    } ?>
                                                </div>
                                                <?php echo $room_descr; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } ?>
                    </div>
                </aside>
            </div>
        </article>
    </div>
</section>
