	<footer id="main-footer">
		<div class="top-footer">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Top Footer') ) : ?>

						<?php endif; ?>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class="four-menu middle-footer">
				<div class="container">
					<div class="row">
						<?php if((is_active_sidebar('Home Footer Menu One')) && (is_active_sidebar('Home Footer Menu Two')) && (is_active_sidebar('Home Footer Menu Three')) && (is_active_sidebar('Home Footer Menu Four'))){ ?>
							<div class="col-md-3 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu One') ) : ?>
								<?php endif; ?>
							</div>
							<div class="col-md-4 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu Two') ) : ?>
								<?php endif; ?>
							</div>
							<div class="clearfix-sm"></div>
							<div class="col-md-3 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu Three') ) : ?>
								<?php endif; ?>
							</div>
							<div class="col-md-2 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu Four') ) : ?>
								<?php endif; ?>
							</div>
						<?php } elseif((is_active_sidebar('Home Footer Menu One')) && (is_active_sidebar('Home Footer Menu Two')) && (is_active_sidebar('Home Footer Menu Three')) && !(is_active_sidebar('Home Footer Menu Four'))){ ?>
							<div class="col-md-3 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu One') ) : ?>
								<?php endif; ?>
							</div>
							<div class="col-md-4 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu Two') ) : ?>
								<?php endif; ?>
							</div>
							<div class="clearfix-sm"></div>
							<div class="col-md-5 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu Three') ) : ?>
								<?php endif; ?>
							</div>
						<?php } elseif((is_active_sidebar('Home Footer Menu One')) && (is_active_sidebar('Home Footer Menu Two')) && !(is_active_sidebar('Home Footer Menu Three')) && !(is_active_sidebar('Home Footer Menu Four'))){ ?>
							<div class="col-md-6 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu One') ) : ?>
								<?php endif; ?>
							</div>
							<div class="col-md-6 col-sm-6 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu Two') ) : ?>
								<?php endif; ?>
							</div>
						<?php } elseif((is_active_sidebar('Home Footer Menu One')) && !(is_active_sidebar('Home Footer Menu Two')) && !(is_active_sidebar('Home Footer Menu Three')) && !(is_active_sidebar('Home Footer Menu Four'))){ ?>
							<div class="col-md-12 col-sm-12 footer-menu-one-fourth">
								<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Home Footer Menu One') ) : ?>
								<?php endif; ?>
							</div>
						<?php } ?>
						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		<div class="middle-footer">
			<div class="container">
				<div class="row">
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="bottom-footer">
			<div class="container">
				<div class="row">
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</footer>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery-1.12.4.min.js"></script>
	<?php wp_footer(); ?>

	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/modernizr-2.0.6.min.js" defer></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/owl.carousel.min.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery.lazy.min.js"></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/lightbox.min.js" defer></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/modal.js" defer></script>
	<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/classie.js" defer></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/bootstrap.min.js" defer></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.simplemodal.js" defer></script>
	<script src="<?php echo get_template_directory_uri(); ?>/js/jquery.scrollTo.min.js" defer></script>
	<script src="<?php echo get_template_directory_uri(); ?>/scripts/scripts.js" defer></script>
</script>
</body>
</html>