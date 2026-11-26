<?php $year = date('Y'); ?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a class="brand" href="<?= e(url()) ?>">
          <span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M2 13h20"/></svg></span>
          <span>JobLink<small>Rwanda</small></span>
        </a>
        <p class="small" style="color:#a9bbd5;max-width:34ch;margin-top:1rem"><?= e(t('hero_text')) ?></p>
        <p class="small" style="color:var(--gold);font-weight:700"><?= e(config('app.tagline')) ?></p>
      </div>
      <div>
        <h4>Opportunities</h4>
        <a href="<?= e(url('jobs')) ?>">Find jobs</a>
        <a href="<?= e(url('internships')) ?>">Internships</a>
        <a href="<?= e(url('remote-jobs')) ?>">Remote jobs</a>
        <a href="<?= e(url('freelance')) ?>">Freelance projects</a>
        <a href="<?= e(url('scholarships')) ?>">Scholarships & fellowships</a>
        <a href="<?= e(url('opportunity-radar')) ?>">Opportunity Radar</a>
      </div>
      <div>
        <h4>Grow</h4>
        <a href="<?= e(url('career-centre')) ?>">Career Growth Centre</a>
        <a href="<?= e(url('interview-prep')) ?>">Interview preparation</a>
        <a href="<?= e(url('community')) ?>">Career community</a>
        <a href="<?= e(url('companies')) ?>">Browse companies</a>
        <a href="<?= e(url('about')) ?>">About us</a>
      </div>
      <div>
        <h4>Support</h4>
        <a href="<?= e(url('help')) ?>">Help Centre</a>
        <a href="<?= e(url('contact')) ?>">Contact us</a>
        <a href="<?= e(url('privacy')) ?>">Privacy Policy</a>
        <a href="<?= e(url('terms')) ?>">Terms & Conditions</a>
        <a href="<?= e(url('sitemap.xml')) ?>">Sitemap</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= $year ?> JobLink Rwanda. Built for opportunity.</span>
      <span>Made in Rwanda · English · Kinyarwanda · Français</span>
    </div>
  </div>
</footer>
