<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if IE 7]>
<html id="ie7" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if IE 8]>
<html id="ie8" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if IE 9]>
<html id="ie9" lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]>
<html lang="en-US" prefix="og: http://ogp.me/ns#">
<![endif]-->
<html>
<head>
	<meta charset="utf-8" />
	<title><?php wp_title(' - ','true','right'); ?><?php bloginfo('name'); ?></title>
	<!--[if IE]>
		<link href="<?php echo get_template_directory_uri(); ?>/ie.css" rel="stylesheet" type="text/css" media="all" />
	<![endif]-->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!-- Bootstrap -->
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.min.css" />
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/font-awesome.min.css" />

	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/owl.carousel.css" />
	<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/css/animate.css" />
	<link rel="stylesheet" media="all" href="<?php echo get_template_directory_uri(); ?>/css/lightbox.css" />
	<link rel="stylesheet" media="all" href="<?php echo get_template_directory_uri(); ?>/css/modal.css" />

	<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />

	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/layout.css" />
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/font.css" />
	
	<link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/images/logo.ico" />
	<link rel="apple-touch-icon" sizes="57x57" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon" sizes="60x60" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-60x60.png" />
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon" sizes="76x76" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon" sizes="120x120" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon" sizes="144x144" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon" sizes="152x152" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-152x152.png" />
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/images/apple-touch-icon-180x180.png" />
	<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon-194x194.png" sizes="194x194" />
	<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/android-chrome-192x192.png" sizes="192x192" />
	<link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/images/favicon-16x16.png" sizes="16x16" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="robots" content="noimageindex, noodp, noydir" />
	<meta name="author" content="" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
	<?php wp_head(); ?>
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
</head>
<body <?php id_the_body(); ?> class="<?php class_the_body(); ?>">
	<a href="#" class="smooth-scroll"><button type="button" class="back_to_top-button"><i class="fa fa-chevron-up"></i></button></a>
	<header id="main-header">
		<div id="main-header-content">
			<div id="top-header">
				<div class="container">
					<div class="row">
						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>			
			<div id="main-navigation">
				<div class="container">
					<div class="row">
						<?php
							$header_logo = get_field('header_logo', 'option');
						?>
						<div class="col-md-12 main-logo">
							<div class="header_top">
								<ul class="header_list">
									<li class="login">
									<?php if ( is_user_logged_in() ) { ?>
										<span class="clickable_area collapsed" data-toggle="collapse" data-target="#signin_menu" aria-expanded="true"><a class="signin loggedin">Control Panel</a></span>
										<div style="position: relative;">
											<form>
												<fieldset id="signin_menu" class="collapse">
													<p><?php global $user_ID, $user_identity; get_currentuserinfo(); if ($user_ID) { echo get_avatar($user_ID, 40); ?>Welcome back, <?php echo $user_identity; } ?>! <a href="<?php echo wp_logout_url( get_option('home') ); ?>" title="Log Out">Log Out</a><?php if ( current_user_can('manage_options') ) { ?>  | <a href="<?php echo get_option('home'); ?>/wp-admin/" title="Admin">Admin</a> <?php } ?> | <a href="<?php echo get_option('home'); ?>/account/" title="My Account" class="wc_account_link">My Account</a> | <a href="<?php echo get_option('home'); ?>/cart/" title="Cart" class="wc_cart_link">Cart</a></p>
												</fieldset>
											</form>
										</div>
									<?php } else { ?>
										<span class="clickable_area collapsed" data-toggle="collapse" data-target="#signin_menu" aria-expanded="true"><span class="user_area">Have an account?</span> <a class="signin loggedout"><span>Sign in</span></a></span>
										<div style="position: relative;">
											<form action="<?php echo get_option('home'); ?>/wp-login.php" method="post">
												<fieldset id="signin_menu" class="collapse">
														<p>
															<label for="log">User Name</label>
															<input type="text" name="log" id="log" value="<?php echo wp_specialchars(stripslashes($user_login), 1) ?>" title="User Name" />
														</p>
														<p>
															<label for="pwd">Password</label>
															<input type="password" name="pwd" id="pwd" title="Password" />
														</p>
														<p class="remember">
															<input id="remember" name="remember_me" type="checkbox" checked="checked" value="forever" title="Remember Me" />
															<label for="remember">Remember me</label>
															<input id="signin_submit" type="submit" name="submit" value="Log In" title="Log In" />
															<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
														</p>
														<p class="forgot"><a href="<?php echo get_option('home'); ?>/wp-login.php?action=lostpassword" title="Forgot your password?">Forgot your password?</a></p>
												</fieldset>
											</form>
										</div>
									<?php } ?>
									</li>
								</ul>
							</div>						
							<div class="header-logo lazy-image" style="width:216px;" <?php echo ($header_logo ? 'data-src="' . $header_logo['url'] . '"' : 'data-src="' . get_template_directory_uri() . '/images/upvi_logo@3x.png"'); ?>><a href="<?php echo get_settings('home'); ?>" title="<?php bloginfo('name'); ?>" class=""><img src="<?php echo get_template_directory_uri() . '/images/upvi_logo.png'; ?>" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>" style="width:216px;max-width:100%;height:auto;" /></a></div>
							<div class="nav">
								<div class="inner-nav">
									<?php wp_nav_menu( array( 'theme_location' => 'primary_navigation', 'items_wrap' => '<ul class="primary-menu">%3$s</ul>', 'link_before' => '<span class="menu-item">', 'link_after'  => '</span>' ) ); ?>
									<?php if(get_field('show_search_form_after_menu', 'option')){ ?>
										<form role="search" method="get" id="menu_searchform" class="search-form collapse" action="<?php echo home_url( '/' ); ?>">
			`								<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search the UP Vanguard Inc. Shibboleth Wall website', 'placeholder' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label' ) ?>" />
										</form>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
			<div id="bottom-header">
				<div class="container">
					<div class="row">
						<div class="clearfix"></div>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="clearfix"></div>
			</div>			
		</div>
	</header>