<?php /* Template Name: Homepage */ ?>

<?php get_header(); ?>

<?php 
  if (have_posts()):
    while (have_posts()) : the_post();
      $content = get_the_content();
      $featuredImage = get_the_post_thumbnail_url();
    endwhile;
  endif;
?>

<div class="grid-container homepage-grid" style="background-image:url('<?php echo $featuredImage ?>')">
  <div class="grid-item item1"></div>
  <div class="grid-item item2"></div>
  <div class="grid-item item3"></div>
  <div class="grid-item item4"></div>
  <div class="grid-item item5">
    <?php echo $content; ?>
  </div>
  <div class="grid-item item6"></div>
  <div class="grid-item item7"></div>
  <div class="grid-item item8"></div>
  <div class="grid-item item9"></div>
  <div class="grid-item item10"></div>
  <div class="grid-item item11"></div>
  <div class="grid-item item12"></div>
  <div class="grid-item item13"></div>
  <div class="grid-item item14">
    <div class="button-container">
      <a class="button" href="/work">see some projects</a>
    </div>
  </div>
  <div class="grid-item item15"></div>
</div>

<?php get_footer(); ?>
