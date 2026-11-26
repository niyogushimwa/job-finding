<section class="hero" style="padding:3.5rem 0">
  <div class="container">
    <span class="eyebrow">About the platform</span>
    <h1>Opportunity should be easy to find.</h1>
    <p class="hero-sub">JobLink Rwanda connects talent with organisations and helps people turn skills into sustainable careers.</p>
  </div>
</section>
<section class="section">
  <div class="container-narrow rich-text">
    <h2>Why we built JobLink</h2>
    <p>Unemployment is not only a lack of jobs — it is also a gap between people and the opportunities that already exist. Students, graduates, freelancers and career changers often do not know where to look, how to present their skills or whether an opportunity is trustworthy.</p>
    <p>JobLink Rwanda brings discovery, profile building, smart matching, applications, learning and community together in one free, mobile-friendly platform. We start with Rwanda and design for the wider African market.</p>
    <h2>What makes us different</h2>
    <div class="grid grid-2">
      <div class="card"><h3>Smart matching</h3><p class="small muted mb-0">Compatibility scores are calculated from your actual skills, education, experience, location and preferences — never invented.</p></div>
      <div class="card"><h3>Opportunity Radar</h3><p class="small muted mb-0">Jobs, internships, scholarships, fellowships, remote roles, volunteering and training in one searchable view.</p></div>
      <div class="card"><h3>Trust first</h3><p class="small muted mb-0">Companies are never automatically verified. External opportunities are labelled clearly and only marked verified after a human check.</p></div>
      <div class="card"><h3>Career growth</h3><p class="small muted mb-0">Practical articles, interview practice and community support help you grow beyond the application.</p></div>
    </div>
    <h2>Platform snapshot</h2>
    <p class="muted">Live counts: <?= (int) $pageStats['jobs'] ?> published opportunities, <?= (int) $pageStats['categories'] ?> job categories and <?= (int) $pageStats['countries'] ?> supported countries.</p>
    <a class="btn btn-primary" href="<?= e(url('contact')) ?>">Get in touch</a>
  </div>
</section>
