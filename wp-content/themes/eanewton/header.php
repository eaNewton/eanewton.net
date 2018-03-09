<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentyseventeen' ); ?></a>

	<header id="masthead" class="site-header" role="banner">

		<div class="header-container">
      <div class="header-left">
        <a href="/">
          <img src="/wp-content/themes/eanewton/images/logo-sky.png"/>
        </a>
      </div>
      <div class="header-right desktop">

        <?php if ( has_nav_menu( 'top' ) ) :
                wp_nav_menu();
              endif; ?>
        
      </div>
      <div class="header-right mobile">
        <a href="#" id="mobile-menu-trigger" class="mobile-menu-trigger">
          <img onclick="andThenThereWasLight()" id="mobile-menu-icon" class="mobile-menu-icon" src="/wp-content/themes/eanewton/images/light-bulb.svg"/>
        </a>
      </div>
    </div>

	</header><!-- #masthead -->

	<div class="site-content-contain">
		<div id="content" class="site-content">
