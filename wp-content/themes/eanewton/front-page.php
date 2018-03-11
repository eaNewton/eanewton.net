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
  <div class="grid-item item1">1</div>
  <div class="grid-item item2">2</div>
  <div class="grid-item item3">3</div>
  <div class="grid-item item4">4</div>
  <div class="grid-item item5">
    <?php echo $content; ?>
  </div>
  <div class="grid-item item6">6</div>
  <div class="grid-item item7">7</div>
  <div class="grid-item item8">8</div>
  <div class="grid-item item9">9</div>
  <div class="grid-item item10">10</div>
  <div class="grid-item item11">
    <div class="button-conainter">
      <a class="button" href="/work">check out some of my work</a>
    </div>
  </div>
  <div class="grid-item item12">12</div>
  <div class="grid-item item13">13</div>
  <div class="grid-item item14">14</div>
</div>

<?php get_footer(); ?>
