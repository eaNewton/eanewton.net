<?php /* Template Name: Contact */ ?>

<?php get_header(); ?>

  <div class="page-header-container">
    <h1 class="page-title"><?php echo get_the_title(); ?></h1>
  </div>

  <?php while(have_posts()) : the_post(); ?>
  <?php the_content();?>
  <?php endwhile; ?>

<?php get_footer(); ?>
