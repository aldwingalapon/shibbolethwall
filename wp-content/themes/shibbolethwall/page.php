<?php  
/**
 * Template Name: Default
 *
 */

get_header(); ?>

<?php if (have_posts()) : ?>
	<div id="main-content" class="page-template">
		<?php while (have_posts()) : the_post(); $the_ID = $post->ID; ?>
			<div class="page-breadcrumb">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<?php if(function_exists('the_breadcrumbs')) the_breadcrumbs(); ?>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
			<!-- Hero Banner Section -->
			<?php if(get_field('use_header_image_or_hero_banner', $the_ID) == 'Header Image') { ?>
				<div class="page-banner">
					<?php 
						$headerimg = '';
						$headerbg = get_stylesheet_directory_uri().'/images/default-page-header-blank.png';
						if(get_field('header_image', $the_ID ) ) {
							$img = get_field('header_image', $the_ID );
							$headerimg = $img['url'];
						} else {
							$headerimg = get_stylesheet_directory_uri().'/images/default-page-header.jpg';	
						}
					 ?>
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<div class="overlay"></div>
								<div class="header-image lazy-image" data-src="<?php echo $headerimg; ?>"><img src="<?php echo $headerbg; ?>" width="1920" height="256" alt="<?php echo ( get_field('header_title', $the_ID ) ? the_field('header_title', $the_ID ) : get_the_title() ); ?>" title="<?php echo ( get_field('header_title', $the_ID ) ? the_field('header_title', $the_ID ) : get_the_title() ); ?>" />
									<div class="header-caption">
								</div>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			<?php } else {
				$hero_banner_category = get_field('hero_banner_category', $the_ID);
			?>
				<div class="hero-banner">
					<div class="overlay"></div>
					<?php 
						$args = array(
							'posts_per_page' => -1,
							'post_type' =>	'slider',
							'status'	=>	'publish',
							'orderby'	=>	'menu_order',
							'order'	=>	'ASC',
							'tax_query' =>
								array(
									array(
										'taxonomy' => 'slider_position',
										'field'    => 'name',
										'terms' => $hero_banner_category->name,                                    
									),
								), 
						);
									
						$slider = New WP_Query($args);
					?>
					<?php if ($slider) : $i = 1; ?>
						<div class="owl-carousel" id="hero-owl-carousel">
							<?php  while ( $slider->have_posts() ) : $slider->the_post(); $slider_id = get_the_ID();  ?>
								<div class="hero-item">
									<div class="hero-caption">
										<h2><?php echo((get_field('slider_title_pretext', $slider_id)) ? '<small class="pre">' . get_field('slider_title_pretext', $slider_id) . '</small>' : ''); ?><?php echo get_the_title($slider_id); ?></h2>
										<p><?php echo get_post_field('post_content', $slider_id); ?></p>
										<?php if(get_field('show_link_button_1', $slider_id) || get_field('show_link_button_2', $slider_id)) { ?>
											<div class="hero-buttons">
												<?php if(get_field('show_link_button_1', $slider_id)) { ?>
													<?php if(get_field('button_on_click_event_1', $slider_id)) { ?>
														<div class="btn_container">
															<a class="hero_button_<?php echo $slider_id; ?>_first<?php echo ((get_field('button_type_1', $slider_id) == 'Primary') ? ' btn btn-primary button' : ' btn btn-secondary button'); ?>"<?php echo (get_field('button_on_click_event_1', $slider_id) ? ' onclick="' . get_field('button_on_click_event_1', $slider_id) . '"' : ''); ?>><?php echo (get_field('button_text_1', $slider_id) ? get_field('button_text_1', $slider_id) : ''); ?></a>
															<?php echo ((get_field('button_text_1_instruction', $slider_id)) ? '<div class="instructions">' . get_field('button_text_1_instruction', $slider_id) . '</div>' : ''); ?>
														</div>
													<?php } else { ?>
														<div class="btn_container">
															<a href="<?php echo (get_field('button_link_1', $slider_id) ? get_field('button_link_1', $slider_id) : ''); ?>" class="hero_button_<?php echo $slider_id; ?>_first<?php echo (get_field('button_id_1', $slider_id) ? ' modal-dialog-'.get_field('button_id_1', $slider_id) : ''); ?><?php echo ((get_field('button_type_1', $slider_id) == 'Primary') ? ' btn btn-primary button' : ' btn btn-secondary button'); ?>"><?php echo (get_field('button_text_1', $slider_id) ? get_field('button_text_1', $slider_id) : ''); ?></a>
															<?php echo ((get_field('button_text_1_instruction', $slider_id)) ? '<div class="instructions">' . get_field('button_text_1_instruction', $slider_id) . '</div>' : ''); ?>
														</div>
													<?php } ?>
													<?php if(get_field('button_icon_1', $slider_id) && get_field('button_icon_position_1', $slider_id)) {
														$img_1 = get_field('button_icon_1', $slider_id );
														$button_icon_1 = $img_1['url'];
														$button_icon_1_width = $img_1['width'];
														$button_icon_1_height = $img_1['height'];
														$button_icon_1_width_actual = ($img_1['width']/$img_1['height'])*12;
														$button_icon_1_height_actual = 12;
														
														if (get_field('button_icon_position_1', $slider_id) == 'Before'){
													?>	
															<style>
																.hero_button_<?php echo $slider_id; ?>_first:before{content:'';margin-right:12px;display: inline-block;vertical-align:middle;background:transparent url(<?php echo $button_icon_1; ?>) no-repeat center;width:<?php echo $button_icon_1_width_actual; ?>px; height:<?php echo $button_icon_1_height_actual; ?>px;background-size:contain;margin-bottom: 3px;}
															</style>
														<?php } else { ?>
															<style>
																.hero_button_<?php echo $slider_id; ?>_first:after{content:'';margin-left:12px;display: inline-block;vertical-align: middle;background:transparent url(<?php echo $button_icon_1; ?>) no-repeat center;width:<?php echo $button_icon_1_width_actual; ?>px; height:<?php echo $button_icon_1_height_actual; ?>px;background-size:contain;margin-bottom: 3px;}
															</style>
													<?php
															}
														}
													?>
												<?php } ?>
												
												<?php if(get_field('show_link_button_2', $slider_id)) { ?>
													<?php if(get_field('button_on_click_event_2', $slider_id)) { ?>
														<div class="btn_container">
															<a class="hero_button_<?php echo $slider_id; ?>_second<?php echo ((get_field('button_type_2', $slider_id) == 'Primary') ? ' btn btn-primary button' : ' btn btn-secondary button'); ?>"<?php echo (get_field('button_on_click_event_2', $slider_id) ? ' onclick="' . get_field('button_on_click_event_2', $slider_id) . '"' : ''); ?>><?php echo (get_field('button_text_2', $slider_id) ? get_field('button_text_2', $slider_id) : ''); ?></a>
															<?php echo ((get_field('button_text_2_instruction', $slider_id)) ? '<div class="instructions">' . get_field('button_text_2_instruction', $slider_id) . '</div>' : ''); ?>
														</div>
													<?php } else { ?>
														<div class="btn_container">
															<a href="<?php echo (get_field('button_link_2', $slider_id) ? get_field('button_link_2', $slider_id) : ''); ?>" class="hero_button_<?php echo $slider_id; ?>_second<?php echo (get_field('button_id_2', $slider_id) ? ' modal-dialog-'.get_field('button_id_2', $slider_id) : ''); ?><?php echo ((get_field('button_type_2', $slider_id) == 'Primary') ? ' btn btn-primary button' : ' btn btn-secondary button'); ?>"><?php echo (get_field('button_text_2', $slider_id) ? get_field('button_text_2', $slider_id) : ''); ?></a>
															<?php echo ((get_field('button_text_2_instruction', $slider_id)) ? '<div class="instructions">' . get_field('button_text_2_instruction', $slider_id) . '</div>' : ''); ?>
														</div>
													<?php } ?>
													<?php if(get_field('button_icon_2', $slider_id) && get_field('button_icon_position_2', $slider_id)) {
														$img_2 = get_field('button_icon_2', $slider_id );
														$button_icon_2 = $img_2['url'];
														$button_icon_2_width = $img_2['width'];
														$button_icon_2_height = $img_2['height'];
														$button_icon_2_width_actual = ($img_2['width']/$img_2['height'])*12;
														$button_icon_2_height_actual = 12;
														
														if (get_field('button_icon_position_2', $slider_id) == 'Before'){
													?>	
															<style>
																.hero_button_<?php echo $slider_id; ?>_second:before{content:'';margin-right:12px;display: inline-block;vertical-align:middle;background:transparent url(<?php echo $button_icon_2; ?>) no-repeat center;width:<?php echo $button_icon_2_width_actual; ?>px; height:<?php echo $button_icon_2_height_actual; ?>px;background-size:contain;margin-bottom: 3px;}
															</style>
														<?php } else { ?>
															<style>
																.hero_button_<?php echo $slider_id; ?>_second:after{content:'';margin-left:12px;display: inline-block;vertical-align: middle;background:transparent url(<?php echo $button_icon_2; ?>) no-repeat center;width:<?php echo $button_icon_2_width_actual; ?>px; height:<?php echo $button_icon_2_height_actual; ?>px;background-size:contain;margin-bottom: 3px;}
															</style>
													<?php
															}
														}
													?>												
												<?php } ?>
											</div>
										<?php } ?>
									</div>
									<?php if ( has_post_thumbnail() ) {
										$image_url = wp_get_attachment_image_src( get_post_thumbnail_id($slider_id), 'full' );
										$feature_icon = $image_url[0];
										echo '<div class="hero-image lazy-image" data-src="' . $feature_icon . '"><img src="' . $feature_icon . '" /></div>';
									} ?>					
								</div>
							<?php endwhile; ?>
						</div>
					<?php else : endif; ?>
				</div>
			<?php } ?>			
			<?php if( have_rows('section_item', $the_ID ) ): $z_index = 50; ?>
				<?php while ( have_rows('section_item', $the_ID ) ) : the_row();
					$z_index -= 1;
					$show_section = get_sub_field('show_section');
					$section_id = get_sub_field('section_id');
					$section_class = get_sub_field('section_class');
					$section_background_color = get_sub_field('section_background_color');
					$section_background_image = get_sub_field('section_background_image');
					$section_background_image_position = get_sub_field('section_background_image_position');
					$section_background_image_size = get_sub_field('section_background_image_size');
					$section_background_image_repeat = get_sub_field('section_background_image_repeat');
					$section_custom_style = get_sub_field('section_custom_style');
					$section_custom_script = get_sub_field('section_custom_script');
					$section_padding_top = get_sub_field('section_padding_top');
					$section_padding_bottom = get_sub_field('section_padding_bottom');
					$section_bottom_angled = get_sub_field('section_bottom_angled');
					$section_bottom_angle_direction = get_sub_field('section_bottom_angle_direction');
					$section_bottom_angle_degree = get_sub_field('section_bottom_angle_degree');

					$section_title = get_sub_field('section_title');
					$section_title_color = get_sub_field('section_title_color');
					$section_title_style = get_sub_field('section_title_style');
					$section_description = get_sub_field('section_description');
					$section_description_color = get_sub_field('section_description_color');
					$section_description_style = get_sub_field('section_description_style');

					$section_content_type = get_sub_field('section_content_type');
					$section_content_post_type = get_sub_field('section_content_post_type');
					$section_content_post_slider_category = get_sub_field('section_content_post_slider_category');
					$section_content_post_slider_class = get_sub_field('section_content_post_slider_class');
					$section_content_post_slider_id = get_sub_field('section_content_post_slider_id');
					$section_content_post_slider_style = get_sub_field('section_content_post_slider_style');
					$section_content_post_slider_script = get_sub_field('section_content_post_slider_script');
					$section_content_post_slider_image_position = get_sub_field('section_content_post_slider_image_position');
					$show_slider_navigation = get_sub_field('show_slider_navigation');
					$show_slider_dots_navigation = get_sub_field('show_slider_dots_navigation');
					$slider_dots_navigation_position = get_sub_field('slider_dots_navigation_position');
					$slider_dots_navigation_normal_color = get_sub_field('slider_dots_navigation_normal_color');
					$slider_dots_navigation_hover_color = get_sub_field('slider_dots_navigation_hover_color');
					
					$section_content_post_query_count_id = get_sub_field('section_content_post_query_count_id');
					$section_content_post_count = get_sub_field('section_content_post_count');
					$section_content_post_ids = get_sub_field('section_content_post_ids');
					$show_section_content_post_sticky = get_sub_field('show_section_content_post_sticky');
					
					$section_content_post_order_by = get_sub_field('section_content_post_order_by');
					$section_content_post_order = get_sub_field('section_content_post_order');
					$show_section_content_post_thumbnail = get_sub_field('show_section_content_post_thumbnail');
					$section_content_post_thumbnail_size = get_sub_field('section_content_post_thumbnail_size');
					$section_content_post_thumbnail_style = get_sub_field('section_content_post_thumbnail_style');
					$section_content_post_thumbnail_container_image = get_sub_field('section_content_post_thumbnail_container_image');
					$show_section_content_post_title = get_sub_field('show_section_content_post_title');
					$section_content_post_title_position = get_sub_field('section_content_post_title_position');
					$show_section_content_post_excerpt = get_sub_field('show_section_content_post_excerpt');

					$show_section_pre_content = get_sub_field('show_section_pre_content');
					$section_pre_content = get_sub_field('section_pre_content');
					$section_pre_content_container_class = get_sub_field('section_pre_content_container_class');
					$section_pre_content_style = get_sub_field('section_pre_content_style');
					$section_pre_content_script = get_sub_field('section_pre_content_script');
					$show_section_post_content = get_sub_field('show_section_post_content');
					$section_post_content = get_sub_field('section_post_content');
					$section_post_content_container_class = get_sub_field('section_post_content_container_class');
					$section_post_content_style = get_sub_field('section_post_content_style');
					$section_post_content_script = get_sub_field('section_post_content_script');

					$section_content_margin_top = get_sub_field('section_content_margin_top');
					$section_content_margin_bottom = get_sub_field('section_content_margin_bottom');
					
					$section_content_post_template = get_sub_field('section_content_post_template');

					$section_content_column_count = get_sub_field('section_content_column_count');
					$section_content_column_padding = get_sub_field('section_content_column_padding');
					$column_one = get_sub_field('column_one');
					$column_one_class = get_sub_field('column_one_class');
					$column_two = get_sub_field('column_two');
					$column_two_class = get_sub_field('column_two_class');
					$column_three = get_sub_field('column_three');
					$column_three_class = get_sub_field('column_three_class');
					$column_four = get_sub_field('column_four');
					$column_four_class = get_sub_field('column_four_class');
				?>

					<?php if($show_section) { ?>
						<div<?php echo($section_id ? ' id="' . $section_id . '"' : ''); ?><?php echo($section_class ? ' class="' . $section_class . ' section"' : ''); ?><?php echo(($section_background_color || $section_background_image || $section_padding_top || $section_padding_bottom) ? ' style="' . (($section_background_color) ? 'background-color:' . $section_background_color . ';' : '') . (($section_background_image) ? 'background-image:url(' . $section_background_image['url'] . ');' : '') . (($section_background_image_position) ? 'background-position:' . $section_background_image_position . ';' : '') . (($section_background_image_size) ? 'background-size:' . $section_background_image_size . ';' : '') . (($section_background_image_repeat) ? 'background-repeat:' . $section_background_image_repeat . ';' : '') . (($section_padding_top) ? 'padding-top:' . $section_padding_top . 'rem;' : '') . (($section_padding_bottom) ? 'padding-bottom:' . $section_padding_bottom . 'rem;' : '') . '"' : ''); ?>>
							<div class="container">
								<div class="row-fluid">
									<?php if($section_title) { ?>
										<div class="col-md-12">
											<?php echo(($section_title) ? '<h2 ' . (($section_title_color || $section_title_style) ? ' style=" ' . (($section_title_color) ? 'color:' . $section_title_color . ' !important;' : '') . $section_title_style . ' "' : '') . '>' . $section_title . ($section_description ? '<div class="clearfix"' . (($section_description_color || $section_description_style) ? ' style=" ' . ($section_description_color ? 'color:' . $section_description_color . ' !important;' : '') . $section_description_style . ' "' : '') . '>' . $section_description . '</div>' : '') . '</h2>' : ''); ?>
										</div>
										<div class="clearfix"></div>
									<?php } ?>
									<?php if($show_section_pre_content) { ?>
										<div class="pre-content-container <?php echo $section_pre_content_container_class; ?>">
											<?php echo $section_pre_content; ?>
										</div>
										<?php echo (($section_pre_content_style) ? '<style>' . $section_pre_content_style . '</style>' : ''); ?>
										<?php echo (($section_pre_content_script) ? '<script>' . $section_pre_content_script . '</script>' : ''); ?>
									<?php } ?>
									<div class="content-container row-fluid"<?php echo(($section_content_margin_top || $section_content_margin_bottom) ? ' style="' . ($section_content_margin_top ? 'margin-top:' . $section_content_margin_top . 'rem;' : '') . ($section_content_margin_bottom ? 'margin-bottom:' . $section_content_margin_bottom . 'rem;' : '') . '"' : ''); ?>>
										<?php if(($section_content_type) == 'Custom Content') { ?>
											<div class="custom">
												<?php if(($section_content_column_count) == 'One Column'){ ?>
													<div class="<?php echo $column_one_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_one; ?>
														</div>
													</div>
												<?php } elseif(($section_content_column_count) == 'Two Columns'){ ?>
													<div class="<?php echo $column_one_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_one; ?>
														</div>
													</div>
													<div class="<?php echo $column_two_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_two; ?>
														</div>
													</div>
												<?php } elseif(($section_content_column_count) == 'Three Columns'){ ?>
													<div class="<?php echo $column_one_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_one; ?>
														</div>
													</div>
													<div class="<?php echo $column_two_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_two; ?>
														</div>
													</div>
													<div class="<?php echo $column_three_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_three; ?>
														</div>
													</div>
												<?php } elseif(($section_content_column_count) == 'Four Columns'){ ?>
													<div class="<?php echo $column_one_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_one; ?>
														</div>
													</div>
													<div class="<?php echo $column_two_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_two; ?>
														</div>
													</div>
													<div class="<?php echo $column_three_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_three; ?>
														</div>
													</div>
													<div class="<?php echo $column_four_class; ?>" style="padding-left:<?php echo $section_content_column_padding; ?>px;padding-right:<?php echo $section_content_column_padding; ?>px;">
														<div class="inner-content">
															<?php echo $column_four; ?>
														</div>
													</div>
												<?php }	?>
											</div>
											<div class="clearfix"></div>
										<?php } elseif(($section_content_type) == 'Query Content') { ?>
											<?php
												
												if(($section_content_column_count) == 'One Column'){
													$col_count = 1;
													$col_class = 'col-md-12';
												} elseif(($section_content_column_count) == 'Two Columns'){
													$col_count = 2;
													$col_class = 'col-md-6 col-sm-6';
												} elseif(($section_content_column_count) == 'Three Columns'){
													$col_count = 3;
													$col_class = 'col-md-4 col-sm-6';
												} elseif(($section_content_column_count) == 'Four Columns'){
													$col_count = 4;
													$col_class = 'col-md-3 col-sm-6';
												}

												
												if(($section_content_post_query_count_id) == "ID"){
													if ($section_content_post_slider_category){
														if ($show_section_content_post_sticky){
															$myarray = explode(",", $section_content_post_ids);
															$args = array(
																'post__in' =>	$myarray,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order,
																'tax_query' =>
																	array(
																		array(
																			'taxonomy' => 'slider_position',
																			'field'    => 'name',
																			'terms' => $section_content_post_slider_category->name,                                    
																		),
																	), 
															);
														} else {
															$myarray = explode(",", $section_content_post_ids);
															$args = array(
																'post__in' =>	$myarray,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order,
																'ignore_sticky_posts' => 1,
																'tax_query' =>
																	array(
																		array(
																			'taxonomy' => 'slider_position',
																			'field'    => 'name',
																			'terms' => $section_content_post_slider_category->name,                                    
																		),
																	), 
															);
														}
													}else{
														if ($show_section_content_post_sticky){
															$myarray = explode(",", $section_content_post_ids);
															$args = array(
																'post__in' =>	$myarray,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order
															);
														} else {
															$myarray = explode(",", $section_content_post_ids);
															$args = array(
																'post__in' =>	$myarray,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order,
																'ignore_sticky_posts' => 1
															);
														}
													}
												} else {
													if ($section_content_post_slider_category){
														if ($show_section_content_post_sticky){
															$args = array(
																'posts_per_page' => $section_content_post_count,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order,
																'tax_query' =>
																	array(
																		array(
																			'taxonomy' => 'slider_position',
																			'field'    => 'name',
																			'terms' => $section_content_post_slider_category->name,                                    
																		),
																	), 
															);
														} else {
															$args = array(
																'posts_per_page' => $section_content_post_count,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order,
																'ignore_sticky_posts' => 1,
																'tax_query' =>
																	array(
																		array(
																			'taxonomy' => 'slider_position',
																			'field'    => 'name',
																			'terms' => $section_content_post_slider_category->name,                                    
																		),
																	), 
															);
														}
													}else{
														if ($show_section_content_post_sticky){
															$args = array(
																'posts_per_page' => $section_content_post_count,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order
															);
														} else {
															$args = array(
																'posts_per_page' => $section_content_post_count,
																'post_type' =>	$section_content_post_type,
																'status'	=>	'publish',
																'orderby'	=>	$section_content_post_order_by,
																'order'	=>	$section_content_post_order,
																'ignore_sticky_posts' => 1
															);
														}
													}
												}
											
												$query_item = New WP_Query($args);
											?>

											<?php if($section_content_post_template){ ?>
												<?php require(locate_template('/includes/' . $section_content_post_template . '.php', TRUE, TRUE)); ?>
											<?php } ?>
												
										<?php } ?>
									</div>
									<?php if($show_section_post_content) { ?>
										<div class="pre-content-container <?php echo $section_post_content_container_class; ?>">
											<?php echo $section_post_content; ?>
										</div>
										<?php echo (($section_post_content_style) ? '<style>' . $section_post_content_style . '</style>' : ''); ?>
										<?php echo (($section_post_content_script) ? '<script>' . $section_post_content_script . '</script>' : ''); ?>
									<?php } ?>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
						<div class="clearfix"></div>
						<?php echo (($section_custom_style) ? '<style>' . $section_custom_style . '</style>' : ''); ?>
						<?php echo (($section_custom_script) ? '<script>' . $section_custom_script . '</script>' : ''); ?>
					<?php } ?>
					<?php if($section_bottom_angled) { ?>
						<style>
							#main-content .section#<?php echo $section_id; ?>:after{content: ''; width: 100%; height: 100%; position: absolute; background-color: inherit; z-index:<?php echo $z_index ;?>; top: 0; transform-origin: <?php echo $section_bottom_angle_direction; ?> bottom;transform: skewY(<?php echo $section_bottom_angle_degree; ?>deg);}
						</style>
					<?php } ?>
				<?php endwhile; ?>
			<?php endif; ?>
			
		<?php endwhile; ?>
	</div>
<?php else : ?>
<?php endif; ?>
	
<?php wp_reset_query(); ?>

<?php get_footer(); ?>