<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/resources/config.php");

    require_once($_SERVER["DOCUMENT_ROOT"] . "/resources/templates/header.php");
?>
<div class="container">
    <div class="content">
      <h1 id="under_construction">My work</h1>
      <div class="section">
        <div class="portfolio-image-container">
          <a href="https://inquiringminds-austin.org/" target="_blank">
            <img class="portfolio-item" id="inquiringminds-desktop" src="/img/layout/inquiringminds-desktop.png">
          </a>
        </div>
        <div class="portfolio-info-container">
          <a href="https://inquiringminds-austin.org/" target="_blank">
            <h4 class="portfolio-item-title">inquiringminds-austin.org</h4>
          </a>
          <p class="portfolio-item-info">Inquiring Minds is a camp for gifted children,
            providing attendees the opportunity to participate in constructive and creative
            activities that encourage critical thinking and independent learning.
          </p>
        </div>
      </div>
      <div class="section">
        <div class="portfolio-image-container">
          <a href="http://shamrockenvironmental.com/" target="_blank">
            <img class="portfolio-item" id="shamrock-desktop" src="/img/layout/shamrock-desktop.png">
          </a>
        </div>
        <div class="portfolio-info-container">
          <a href="http://shamrockenvironmental.com/" target="_blank">
            <h4 class="portfolio-item-title">shamrockenvironmental.com</h4>
          </a>
          <p class="portfolio-item-info"></p>
        </div>
      </div>
    </div>
</div>
<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/resources/templates/footer.php");
?>
