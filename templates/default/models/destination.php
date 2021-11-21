<?php
if($article_alias == '') pms_err404();

$result = $pms_db->query('SELECT * FROM pm_destination WHERE checked = 1 AND lang = '.PMS_LANG_ID.' AND alias = '.$pms_db->quote($article_alias));
if($result !== false && $pms_db->last_row_count() > 0){
    
    $destination = $result->fetch(PDO::FETCH_ASSOC);
    
    $destination_id = $destination['id'];
    $pms_article_id = $destination_id;
    $title_tag = $destination['name'].' - '.$title_tag;
    $page_title = $destination['name'];
    $page_subtitle = '';
    $page_alias = $pms_pages[$pms_page_id]['alias'].'/'.pms_text_format($destination['alias']);
    
    $result_destination_file = $pms_db->query('SELECT * FROM pm_destination_file WHERE id_item = '.$destination_id.' AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank` LIMIT 1');
    if($result_destination_file !== false && $pms_db->last_row_count() > 0){
        
        $row = $result_destination_file->fetch();
        
        $file_id = $row['id'];
        $filename = $row['file'];
        
        if(is_file(SYSBASE.'medias/destination/medium/'.$file_id.'/'.$filename))
            $page_img = pms_getUrl(true).'/medias/destination/medium/'.$file_id.'/'.$filename;
    }
    
}else pms_err404();

pms_check_URI(DOCBASE.$page_alias);

/* ==============================================
 * CSS AND JAVASCRIPT USED IN THIS MODEL
 * ==============================================
 */

$pms_javascripts[] = DOCBASE.'js/plugins/sharrre/jquery.sharrre.min.js';

$pms_stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css', 'media' => 'all');
$pms_stylesheets[] = array('file' => '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css', 'media' => 'all');
$pms_javascripts[] = '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js';

$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/isotope/css/style.css', 'media' => 'all');
$pms_javascripts[] = '//unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js';

$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/lazyloader/lazyloader.css', 'media' => 'all');
$pms_javascripts[] = DOCBASE.'js/plugins/lazyloader/lazyloader.js';

$pms_stylesheets[] = array('file' => '//cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.0.7/css/star-rating.css', 'media' => 'all');
$pms_javascripts[] = '//cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.0.7/js/star-rating.js';

$pms_stylesheets[] = array('file' => DOCBASE.'js/plugins/simpleweather/css/simpleweather.css', 'media' => 'all');
$pms_javascripts[] = '//cdn.rawgit.com/monkeecreate/jquery.simpleWeather/master/jquery.simpleWeather.min.js';

require(pms_getFromTemplate('common/send_comment.php', false));

require(pms_getFromTemplate('common/header.php', false)); ?>

<article id="page">
    <?php include(pms_getFromTemplate('common/page_header.php', false)); ?>
    
    <div id="content" class="pt30 pb30">
        <div class="container">
            
            <div class="alert alert-success" style="display:none;"></div>
            <div class="alert alert-danger" style="display:none;"></div>
            
            <div class="row">
                <div class="col-md-8 mb20">
                    <div class="row mb10">
                        <div class="col-sm-12">
                            <h1 class="mb0">
                                <?php echo $destination['name']; ?>
                                <br><small><?php echo $destination['subtitle']; ?></small>
                            </h1>
                        </div>
                    </div>
                    <div class="row mb10">
                        <div class="col-md-12">
                            <div class="owl-carousel owlWrapper" data-items="1" data-autoplay="false" data-dots="true" data-nav="false" data-rtl="<?php echo (PMS_RTL_DIR) ? 'true' : 'false'; ?>">
                                <?php
                                if(!empty($destination['video'])){ ?>
                                    <div class="video-container">
                                        <iframe src="<?php echo $destination['video']; ?>" frameborder="0"></iframe>
                                    </div>
                                    <?php
                                }
                                $result_destination_file = $pms_db->query('SELECT * FROM pm_destination_file WHERE id_item = '.$destination_id.' AND checked = 1 AND lang = '.PMS_DEFAULT_LANG.' AND type = \'image\' AND file != \'\' ORDER BY `rank`');
                                if($result_destination_file !== false){
                                    
                                    foreach($result_destination_file as $i => $row){
                                    
                                        $file_id = $row['id'];
                                        $filename = $row['file'];
                                        $label = $row['label'];
                                        
                                        $realpath = SYSBASE.'medias/destination/big/'.$file_id.'/'.$filename;
                                        $thumbpath = DOCBASE.'medias/destination/big/'.$file_id.'/'.$filename;
                                        
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
                            <?php echo $destination['text']; ?>
                        </div>
                    </div>
                </div>
                <aside class="col-md-4 mb20">
                    <div class="boxed">
                        <div itemscope itemtype="http://schema.org/Corporation">
                            <h3 itemprop="name"><?php echo $destination['name']; ?></h3>
                            <span class="simple-weather" data-location="<?php echo $destination['name']; ?>" data-unit="c"></span>
                        </div>
                        <script type="text/javascript">
                            var pms_locations = [
                                ['<?php echo $destination['name']; ?>', '', '<?php echo $destination['lat']; ?>', '<?php echo $destination['lng']; ?>']
                            ];
                        </script>
                        <div id="mapWrapper" class="mb30" data-marker="<?php echo pms_getFromTemplate('images/marker.png'); ?>" data-api_key="<?php echo PMS_GMAPS_API_KEY; ?>"></div>
                        
                        <?php
                        $id_destination = 0;
                        $result_destination_file = $pms_db->prepare('SELECT * FROM pm_destination_file WHERE id_item = :id_destination AND checked = 1 AND lang = '.PMS_LANG_ID.' AND type = \'image\' AND file != \'\' ORDER BY `rank`');
                        $result_destination_file->bindParam(':id_destination', $id_destination, PDO::PARAM_STR);
                
                        $result_destination = $pms_db->query('SELECT * FROM pm_destination WHERE id != '.$destination_id.' AND checked = 1 AND lang = '.PMS_LANG_ID.' ORDER BY rand() LIMIT 5', PDO::FETCH_ASSOC);
                        if($result_destination !== false && $pms_db->last_row_count() > 0){
                            foreach($result_destination as $i => $row){
                                $id_destination = $row['id'];
                                $destination_name = $row['name'];
                                $destination_subtitle = $row['subtitle'];
                                $destination_alias = $row['alias']; ?>
                                
                                <a href="<?php echo DOCBASE.$page['alias'].'/'.pms_text_format($destination_alias); ?>">
                                    <div class="row">
                                        <div class="col-xs-4 mb20">
                                            <?php
                                            $result_destination_file->execute();
                                            if($result_destination_file !== false && $pms_db->last_row_count() > 0){
                                                $row = $result_destination_file->fetch(PDO::FETCH_ASSOC);
                                                
                                                $file_id = $row['id'];
                                                $filename = $row['file'];
                                                $label = $row['label'];
                                                
                                                $realpath = SYSBASE.'medias/destination/small/'.$file_id.'/'.$filename;
                                                $thumbpath = DOCBASE.'medias/destination/small/'.$file_id.'/'.$filename;
                                                    
                                                if(is_file($realpath)){ ?>
                                                    <div class="img-container sm">
                                                        <img alt="" src="<?php echo $thumbpath; ?>">
                                                    </div>
                                                    <?php
                                                }
                                            } ?>
                                        </div>
                                        <div class="col-xs-8">
                                            <h3 class="mb0"><?php echo $destination_name; ?></h3>
                                            <?php
                                            if($destination_subtitle != ''){ ?>
                                                <h4 class="mb0"><?php echo $destination_subtitle; ?></h4>
                                                <?php
                                            } ?>
                                        </div>
                                    </div>
                                </a>
                                <?php
                            } ?>
                            <?php
                        } ?>
                    </div>
                </aside>
            </div>
            <div class="row">
                <?php
                $lz_offset = 1;
                $lz_limit = 9;
                $lz_pages = 0;
                $num_records = 0;
                $hotel_ids = array();
                $result = $pms_db->query('SELECT id FROM pm_hotel WHERE checked = 1 AND lang = '.PMS_LANG_ID.' AND id_destination = '.$destination_id);
                if($result !== false){
                    $hotel_ids = $result->fetchAll(PDO::FETCH_COLUMN);
                    $num_records = count($hotel_ids);
                    $lz_pages = ceil($num_records/$lz_limit);
                }
                if($num_records > 0){ ?>
                    <div class="col-md-12">
                        <h2><?php echo $destination['name'].' - '.$num_records.' '.pms_getAltText($pms_texts['HOTEL'], $pms_texts['HOTELS'], $num_records); ?></h2>
                    </div>
                    <div class="clearfix"></div>
                    <div class="isotopeWrapper clearfix isotope lazy-wrapper" data-loader="<?php echo pms_getFromTemplate('common/get_hotels.php'); ?>" data-mode="click" data-limit="<?php echo $lz_limit; ?>" data-pages="<?php echo $lz_pages; ?>" data-more_caption="<?php echo $pms_texts['LOAD_MORE']; ?>" data-is_isotope="true" data-variables="destination=<?php echo $destination_id; ?>">
                        <?php include(pms_getFromTemplate('common/get_hotels.php', false)); ?>
                    </div>
                    <?php
                } ?>
            </div>
            <div class="row">
                <?php
                $lz_offset = 1;
                $lz_limit = 9;
                $lz_pages = 0;
                $num_records = 0;
                $hotel_ids = implode('|', $hotel_ids);
                $result = $pms_db->query('SELECT count(*) FROM pm_activity WHERE checked = 1 AND lang = '.PMS_LANG_ID.' AND hotels REGEXP \'[[:<:]]'.$hotel_ids.'[[:>:]]\'');
                if($result !== false){
                    $num_records = $result->fetchColumn(0);
                    $lz_pages = ceil($num_records/$lz_limit);
                }
                if($num_records > 0){ ?>
                    <div class="col-md-12">
                        <h2><?php echo $destination['name'].' - '.$num_records.' '.pms_getAltText($pms_texts['ACTIVITY'], $pms_texts['ACTIVITIES'], $num_records); ?></h2>
                    </div>
                    <div class="clearfix"></div>
                    <div class="isotopeWrapper clearfix isotope lazy-wrapper" data-loader="<?php echo pms_getFromTemplate('common/get_activities.php'); ?>" data-mode="click" data-limit="<?php echo $lz_limit; ?>" data-pages="<?php echo $lz_pages; ?>" data-more_caption="<?php echo $pms_texts['LOAD_MORE']; ?>" data-is_isotope="true" data-variables="page_id=<?php echo $pms_page_id; ?>&page_alias=<?php echo $page['alias']; ?>&hotels=<?php echo $hotel_ids; ?>">
                        <?php include(pms_getFromTemplate('common/get_activities.php', false)); ?>
                    </div>
                    <?php
                } ?>
            </div>
        </div>
    </div>
</article>
