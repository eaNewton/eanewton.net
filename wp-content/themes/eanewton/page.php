<?php
/* Template Name: General */

get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

  <div class="entry">
    <div class="container title-container">
      <h1 class="page-title"><?php echo get_the_title(); ?></h1>
    </div>
    <div class="container content-container">
      <div class="page-content">
        <?php echo get_the_content(); ?>
      </div>
    </div>
  </div><!-- entry -->

<?php endwhile; ?>
<?php endif; ?>

<?php get_footer();
