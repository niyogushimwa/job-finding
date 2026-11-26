<section class="section">
  <div class="container grid grid-2" style="align-items:start">
    <div>
      <div class="section-kicker">We are here to help</div>
      <h1>Contact us</h1>
      <p class="muted">Questions about an opportunity, your account or a partnership? Send us a message and our team will respond.</p>
      <div class="panel"><div class="panel-body">
        <p><?= icon('mail', 18) ?> <a href="mailto:<?= e(config('mail.from')) ?>"><?= e(config('mail.from')) ?></a></p>
        <p class="mb-0"><?= icon('map', 18) ?> Kigali, Rwanda</p>
      </div></div>
      <p class="small muted">Never pay a recruiter for a job offer. Report suspicious requests using the report button on any job.</p>
    </div>
    <div class="card">
      <form method="post" action="<?= e(url('contact')) ?>">
        <?= csrf_field() ?>
        <div class="form-group"><label class="form-label" for="name">Your name</label><input class="form-control" id="name" name="name" required value="<?= e(old('name')) ?>"></div>
        <div class="form-group"><label class="form-label" for="email">Email address</label><input class="form-control" id="email" name="email" type="email" required value="<?= e(old('email')) ?>"></div>
        <div class="form-group"><label class="form-label" for="subject">Subject</label><input class="form-control" id="subject" name="subject" value="<?= e(old('subject')) ?>"></div>
        <div class="form-group"><label class="form-label" for="message">Message</label><textarea class="form-control" id="message" name="message" rows="6" required><?= e(old('message')) ?></textarea></div>
        <button class="btn btn-primary btn-block" type="submit">Send message</button>
      </form>
    </div>
  </div>
</section>
