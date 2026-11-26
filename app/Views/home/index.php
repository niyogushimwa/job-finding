<?php
/** Homepage — premium, content-driven and fully backed by database records. */
use App\Core\Auth;
use App\Core\Upload;

$jobCard = static function (array $job): string {
    ob_start();
    include BASE_PATH . '/app/Views/partials/job_card.php';
    return (string) ob_get_clean();
};
?>
<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy">
      <span class="eyebrow">Your Skills Deserve an Opportunity</span>
      <h1><?= e(t('hero_title')) ?></h1>
      <p class="hero-sub"><?= e(t('hero_text')) ?></p>
      <form class="search-panel" action="<?= e(url('jobs')) ?>" method="get" role="search">
        <div class="search-grid">
          <label class="search-field"><?= icon('search', 19) ?><input name="q" data-suggestions placeholder="<?= e(t('search_placeholder')) ?>" aria-label="Job title or keyword"><div id="suggestion-box" class="notif-panel hide" style="top:52px;left:0;right:auto;width:100%"></div></label>
          <label class="search-field"><?= icon('map', 19) ?><input name="location" placeholder="<?= e(t('location_placeholder')) ?>" aria-label="Location"></label>
          <label class="search-field"><?= icon('list', 19) ?>
            <select name="category" aria-label="Category"><option value="">All categories</option>
              <?php foreach ($categories as $cat): ?><option value="<?= (int) $cat['id'] ?>"><?= e($cat['name']) ?></option><?php endforeach; ?>
            </select>
          </label>
          <button class="btn btn-gold btn-lg" type="submit"><?= icon('search', 18) ?><?= e(t('search')) ?></button>
        </div>
      </form>
      <div class="hero-points">
        <span><?= icon('check', 16) ?> Verified opportunities</span>
        <span><?= icon('check', 16) ?> Smart job matching</span>
        <span><?= icon('check', 16) ?> Free career resources</span>
      </div>
    </div>
    <div class="hero-visual" aria-hidden="true">
      <div class="hero-art">
        <svg class="hero-illustration" viewBox="0 0 420 360" fill="none" xmlns="http://www.w3.org/2000/svg">
          <ellipse cx="210" cy="318" rx="160" ry="20" fill="rgba(230,179,74,.12)"/>
          <rect x="60" y="90" width="300" height="190" rx="16" fill="#ffffff" opacity=".95"/>
          <rect x="60" y="90" width="300" height="38" rx="16" fill="#eaf1ff"/>
          <circle cx="82" cy="109" r="5" fill="#e6b34a"/><circle cx="100" cy="109" r="5" fill="#93b4e8"/><circle cx="118" cy="109" r="5" fill="#c8d8ed"/>
          <rect x="82" y="148" width="120" height="12" rx="6" fill="#0b1f3a" opacity=".8"/>
          <rect x="82" y="170" width="190" height="8" rx="4" fill="#cbd5e1"/>
          <rect x="82" y="188" width="150" height="8" rx="4" fill="#cbd5e1"/>
          <rect x="82" y="216" width="96" height="34" rx="9" fill="#2563eb"/>
          <rect x="188" y="216" width="76" height="34" rx="9" fill="#f1f5f9"/>
          <circle cx="326" cy="232" r="46" fill="#fff5de"/>
          <path d="M326 208a24 24 0 0 1 24 24v12h-48v-12a24 24 0 0 1 24-24Z" fill="#e6b34a"/>
          <circle cx="326" cy="204" r="12" fill="#0b1f3a"/>
          <path d="M300 258h52l8 30h-68l8-30Z" fill="#0b1f3a"/>
          <path d="M40 150l24-14v28l-24-14Z" fill="#e6b34a" opacity=".8"/>
          <path d="M380 120l20 12-20 12v-24Z" fill="#93b4e8"/>
        </svg>
        <div class="float-card one"><?= icon('briefcase', 17) ?> 10 new opportunities today</div>
        <div class="float-card two"><?= icon('chart', 17) ?> Your profile: 85% match</div>
      </div>
    </div>
  </div>
</section>

<?php if ($ad): ?>
  <div class="container mt-3"><a class="card card-hover d-flex align-center justify-between gap-2" href="<?= e(url(ltrim((string) $ad['link'], '/'))) ?>"><div><strong><?= e($ad['title']) ?></strong><p class="small muted mb-0"><?= e($ad['description']) ?></p></div><?= icon('arrow', 20) ?></a></div>
<?php endif; ?>

<section class="section-tight">
  <div class="container">
    <div class="section-head">
      <div><div class="section-kicker">Handpicked for you</div><h2>Featured opportunities</h2><p>Roles selected by our team and employers, updated regularly.</p></div>
      <a class="btn btn-outline" href="<?= e(url('jobs')) ?>"><?= e(t('view_all')) ?> <?= icon('arrow', 16) ?></a>
    </div>
    <?php if ($featured): ?><div class="grid grid-4"><?php foreach ($featured as $job): ?><?= $jobCard($job) ?><?php endforeach; ?></div>
    <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>

<section class="section" style="background:var(--surface);border-block:1px solid var(--border)">
  <div class="container">
    <div class="section-head">
      <div><div class="section-kicker">Fresh off the press</div><h2>Latest opportunities</h2><p>The newest jobs and internships published on the platform.</p></div>
      <a class="btn btn-outline" href="<?= e(url('jobs?sort=latest')) ?>">Browse all jobs <?= icon('arrow', 16) ?></a>
    </div>
    <?php if ($latest): ?><div class="grid grid-4"><?php foreach ($latest as $job): ?><?= $jobCard($job) ?><?php endforeach; ?></div>
    <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><div><div class="section-kicker">Explore by interest</div><h2>Popular job categories</h2><p>Find opportunities in the field that fits you best.</p></div></div>
    <?php if ($categories): ?>
      <div class="grid grid-4">
        <?php foreach ($categories as $cat): ?>
          <a class="card card-hover category-card" href="<?= e(url('jobs?category=' . $cat['id'])) ?>">
            <span class="category-icon"><?= icon($cat['icon'] ?: 'briefcase', 22) ?></span>
            <strong><?= e($cat['name']) ?></strong>
            <span class="small muted"><?= (int) $cat['jobs_count'] ?> open opportunities</span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);border-block:1px solid var(--border)">
  <div class="container">
    <div class="section-head"><div><div class="section-kicker">Meet the employers</div><h2>Top companies</h2><p>Organisations actively hiring and growing their teams.</p></div><a class="btn btn-outline" href="<?= e(url('companies')) ?>">All companies</a></div>
    <?php if ($companies): ?>
      <div class="grid grid-4">
        <?php foreach ($companies as $company): ?>
          <a class="card card-hover company-card" href="<?= e(url('companies/' . $company['id'] . '/' . $company['slug'])) ?>">
            <span class="company-logo"><?= !empty($company['logo']) ? '<img src="' . e(Upload::url($company['logo'])) . '" alt="">' : e(mb_substr($company['name'], 0, 1)) ?></span>
            <strong><?= e($company['name']) ?></strong>
            <span class="small muted"><?= (int) $company['jobs'] ?> open roles · <?= e($company['location'] ?? 'Rwanda') ?></span>
            <?php if ($company['verification_status'] === 'verified'): ?><span class="tag tag-green"><?= icon('shield', 13) ?> Verified company</span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><div><div class="section-kicker">Work from anywhere</div><h2>Remote opportunities</h2><p>Build your career without being tied to a location.</p></div><a class="btn btn-outline" href="<?= e(url('remote-jobs')) ?>">See remote jobs</a></div>
    <?php if ($remote): ?><div class="grid grid-3"><?php foreach ($remote as $job): ?><?= $jobCard($job) ?><?php endforeach; ?></div>
    <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>

<section class="section-tight" style="background:var(--surface);border-block:1px solid var(--border)">
  <div class="container grid grid-2" style="align-items:start">
    <div>
      <div class="section-kicker">Start early</div><h2>Internship opportunities</h2>
      <p class="muted">Real experience, mentorship and a pathway to your first professional role.</p>
      <?php if ($internships): ?><div class="grid" style="gap:1rem"><?php foreach ($internships as $job): ?><?= $jobCard($job) ?><?php endforeach; ?></div>
      <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
    </div>
    <div>
      <div class="section-kicker">New graduates welcome</div><h2>Opportunities for fresh graduates</h2>
      <p class="muted">Entry-level roles and graduate trainee programmes that value potential.</p>
      <?php if ($graduate): ?><div class="grid" style="gap:1rem"><?php foreach ($graduate as $job): ?><?= $jobCard($job) ?><?php endforeach; ?></div>
      <?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><div class="text-center" style="margin:auto"><div class="section-kicker">Simple by design</div><h2>How JobLink works</h2><p>Four steps from your skills to your next opportunity.</p></div></div>
    <div class="grid grid-4">
      <?php foreach ([
        ['user', 'Create your profile', 'Add your skills, education, experience and portfolio so employers can see what you can do.'],
        ['search', 'Discover opportunities', 'Search jobs, internships, remote roles and external opportunities in one place.'],
        ['chart', 'See your match', 'Our matching engine explains how well each role fits your real profile.'],
        ['rocket', 'Apply and grow', 'Track every application, prepare with career resources and move forward.'],
      ] as $i => $step): ?>
        <div class="card"><span class="category-icon"><?= icon($step[0], 22) ?></span><h3 class="mt-2"><?= ($i + 1) . '. ' . e($step[1]) ?></h3><p class="small muted mb-0"><?= e($step[2]) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-tight">
  <div class="container">
    <div class="stat-band">
      <div class="section-head"><div><div class="section-kicker">Real platform data</div><h2>JobLink in numbers</h2><p>Statistics below are calculated directly from platform records.</p></div></div>
      <?php if ($stats['users'] > 0): ?>
      <div class="stat-grid">
        <div class="stat-item"><strong><?= (int) $stats['users'] ?></strong><span>registered members</span></div>
        <div class="stat-item"><strong><?= (int) $stats['published_jobs'] ?></strong><span>published opportunities</span></div>
        <div class="stat-item"><strong><?= (int) $stats['applications'] ?></strong><span>applications submitted</span></div>
        <div class="stat-item"><strong><?= (int) $stats['verified_companies'] ?></strong><span>verified companies</span></div>
      </div>
      <?php else: ?><p class="muted mb-0">The platform is new. Statistics will appear as members join.</p><?php endif; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--surface);border-block:1px solid var(--border)">
  <div class="container">
    <div class="section-head"><div><div class="section-kicker">Keep growing</div><h2>Career Growth Centre</h2><p>Free, practical guidance written for the Rwandan and African job market.</p></div><a class="btn btn-outline" href="<?= e(url('career-centre')) ?>">Explore resources</a></div>
    <?php if ($articles): ?><div class="grid grid-3">
      <?php foreach ($articles as $article): ?>
        <a class="card card-hover article-card" href="<?= e(url('career-centre/' . $article['slug'])) ?>">
          <div class="article-image"><?= icon('graduation', 34) ?></div>
          <span class="tag tag-blue"><?= e($article['category']) ?></span>
          <strong><?= e($article['title']) ?></strong>
          <span class="small muted"><?= e(str_limit($article['excerpt'], 110)) ?></span>
        </a>
      <?php endforeach; ?>
    </div><?php else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
  </div>
</section>

<section class="section">
  <div class="container grid grid-2" style="align-items:start">
    <div class="panel" style="margin:0">
      <div class="panel-body">
        <div class="section-kicker">Build your professional profile</div>
        <h2>Let your work speak for itself.</h2>
        <p class="muted">Add projects, certificates and skills. Get endorsements from people who have seen your work — never automatically labelled as certified.</p>
        <ul class="small" style="list-style:none;padding:0;display:grid;gap:.6rem">
          <li><?= icon('check', 16) ?> Shareable public professional profile</li>
          <li><?= icon('check', 16) ?> Interactive CV builder with 3 templates</li>
          <li><?= icon('check', 16) ?> Skill endorsements from real connections</li>
        </ul>
        <a class="btn btn-primary mt-2" href="<?= e(url(Auth::check() ? 'profile/edit' : 'register')) ?>"><?= e(Auth::check() ? 'Edit my profile' : 'Create free profile') ?> <?= icon('arrow', 16) ?></a>
      </div>
    </div>
    <div>
      <?php if ($stories): foreach ($stories as $story): ?>
        <div class="card story-card">
          <span class="tag tag-gold">Success story</span>
          <h3><?= e($story['headline']) ?></h3>
          <p class="small muted mb-0"><?= e(str_limit($story['story'], 220)) ?></p>
          <span class="small">— <?= e($story['name']) ?></span>
        </div>
      <?php endforeach; else: include BASE_PATH . '/app/Views/partials/empty.php'; endif; ?>
      <?php foreach ($tips as $tip): ?><div class="card tip-card mt-2"><span class="tag tag-green">Career tip</span><strong><?= e($tip['title']) ?></strong><p class="small muted mb-0"><?= e($tip['body']) ?></p></div><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-tight" style="background:var(--navy);color:#fff">
  <div class="container d-flex justify-between align-center flex-wrap gap-2">
    <div><h2 style="color:#fff;margin:0">Get opportunities in your inbox</h2><p class="mb-0" style="color:#c6d5ea">Weekly career tips and fresh opportunities. No spam.</p></div>
    <form class="d-flex gap-1 flex-wrap" method="post" action="<?= e(url('newsletter')) ?>" style="min-width:min(100%,420px)">
      <?= csrf_field() ?><input class="form-control" style="max-width:250px" type="email" name="email" required placeholder="Your email address">
      <button class="btn btn-gold" type="submit">Subscribe</button>
    </form>
  </div>
</section>
