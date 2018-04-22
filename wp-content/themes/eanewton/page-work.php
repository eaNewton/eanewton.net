<?php /* Template Name: Work */ ?>

<?php 

get_header();

query_posts(array( 
    'post_type' => 'site',
    'showposts' => -1 
) );  

if ( have_posts() ) : ?>
  <div class="grid-container sites-grid"> 
  <?php while (have_posts()) : the_post(); 

    $featuredImage = get_the_post_thumbnail_url();
    $url = get_post_meta( get_the_ID(), 'site_url', TRUE );
    $excerpt = get_the_excerpt(); ?>

    <div class="single-site-container grid-item">
      <div class="image-container">
        <a href="<?php echo $url ?>" class="overlay" target="_blank">
          <img src="<?php echo $featuredImage ?>"/>
        </a>
      </div>
      <div class="title-container">
        <a class="site-title" href="<?php echo $url ?>" target="_blank">
          <?php echo get_the_title(); ?>
        </a>
      </div>
      <?php if ( $excerpt ) { ?>
        <div class="info-container">
          <p><?php echo $excerpt ?></p>
        </div>
      <?php } ?>
    </div>

<?php endwhile; ?>
  </div>
<?php endif; ?>

<?php get_footer(); ?>
